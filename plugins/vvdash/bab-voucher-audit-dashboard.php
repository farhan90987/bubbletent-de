<?php
/**
 * Plugin Name: BAB – Voucher Audit Dashboard (Orders Based)
 * Description: Lists completed orders that contain a Flexible PDF Coupons product (or variation) AND have voucher code meta. Shows redemption status/date. Includes CSV export (Overview + Cards).
 * Version: 2.5.0
 * Author: BAB / Mathesconsulting
 */

if (!defined('ABSPATH')) exit;

class BAB_Voucher_Audit_Dashboard_V25 {
    const MENU_SLUG = 'bab-voucher-audit-dashboard';

    // WP Desk voucher product marker meta
    const PRODUCT_VOUCHER_FLAG_META = '_wpdesk_pdf_coupons';

    // Order meta key
    const VOUCHER_CODE_META = '_voucher_code';

    // Dynamic order meta key pattern
    const FCPDF_DYNAMIC_PREFIX = 'fcpdf_order_item_';
    const FCPDF_DYNAMIC_SUFFIX = '_coupon_code';

    // Strict “enabled” values for _wpdesk_pdf_coupons
    const VOUCHER_TRUE_VALUES = ['1','yes','on','true'];

    const EXPORT_ACTION = 'bab_vad_export_csv';
    const NONCE_ACTION  = 'bab_vad_export_csv_nonce';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);

        // Dedicated export endpoint (prevents HTML output being downloaded as CSV)
        add_action('admin_post_' . self::EXPORT_ACTION, [$this, 'handle_export_csv']);
    }

    public function add_menu() {
        add_submenu_page(
            'woocommerce',
            'Voucher Audit Dashboard',
            'Voucher Audit Dashboard',
            'manage_woocommerce',
            self::MENU_SLUG,
            [$this, 'render_page']
        );
    }

    private function get_tab(): string {
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';
        return in_array($tab, ['overview', 'cards'], true) ? $tab : 'overview';
    }

    private function get_date_param(string $key, string $default): string {
        $val = isset($_GET[$key]) ? sanitize_text_field(wp_unslash($_GET[$key])) : $default;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $default;
        return $val;
    }

    private function get_int_param(string $key, int $default): int {
        $val = isset($_GET[$key]) ? (int) $_GET[$key] : $default;
        return max(1, $val);
    }

    private function admin_url_self(array $extra = []): string {
        $base = admin_url('admin.php?page=' . self::MENU_SLUG);
        return $extra ? add_query_arg($extra, $base) : $base;
    }

    private function table_exists(string $table_name): bool {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $found = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        return !empty($found) && strtolower($found) === strtolower($table_name);
    }

    /**
     * Strict check: pm_prod.meta_value OR pm_var.meta_value must be in (1,yes,on,true), case-insensitive.
     */
    private function voucher_product_where_sql(): string {
        $vals = array_map('strtolower', self::VOUCHER_TRUE_VALUES);
        $in = "'" . implode("','", array_map('esc_sql', $vals)) . "'";

        return "(
            (pm_prod.meta_value IS NOT NULL AND LOWER(pm_prod.meta_value) IN ({$in}))
            OR
            (pm_var.meta_value  IS NOT NULL AND LOWER(pm_var.meta_value)  IN ({$in}))
        )";
    }

    /**
     * Query rows per your exact AND logic:
     * - Must be completed order in date range
     * - Must contain voucher product (strict)
     * - Overview tab: AND (dynamic code OR _voucher_code) in ORDER POSTMETA
     * - Cards tab:    AND (_voucher_code) in ORDER POSTMETA only
     *
     * Returns: [$rows, $total, $total_pages, $has_lookup]
     */
    private function query_rows(string $from, string $to, int $page, int $per_page, bool $only_voucher_code): array {
        global $wpdb;

        $posts          = $wpdb->posts;
        $postmeta       = $wpdb->postmeta;
        $order_items    = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';

        $lookup         = $wpdb->prefix . 'wc_order_coupon_lookup';
        $has_lookup     = $this->table_exists($lookup);

        $from_dt = $from . ' 00:00:00';
        $to_dt   = $to . ' 23:59:59';
        $offset  = ($page - 1) * $per_page;

        $voucher_flag_key = self::PRODUCT_VOUCHER_FLAG_META;

        $dynamic_like = esc_sql(self::FCPDF_DYNAMIC_PREFIX) . '%'
                      . esc_sql(self::FCPDF_DYNAMIC_SUFFIX);

        $join_voucher_code = "
            LEFT JOIN {$postmeta} pm_voucher_code
                ON pm_voucher_code.post_id = o.ID
                AND pm_voucher_code.meta_key = '" . esc_sql(self::VOUCHER_CODE_META) . "'
        ";

        $join_dynamic = $only_voucher_code ? "" : "
            LEFT JOIN {$postmeta} pm_dynamic_code
                ON pm_dynamic_code.post_id = o.ID
                AND pm_dynamic_code.meta_key LIKE '" . $dynamic_like . "'
        ";

        $code_where = $only_voucher_code
            ? " AND pm_voucher_code.meta_value IS NOT NULL AND pm_voucher_code.meta_value <> '' "
            : " AND (
                    (pm_dynamic_code.meta_value IS NOT NULL AND pm_dynamic_code.meta_value <> '')
                 OR (pm_voucher_code.meta_value IS NOT NULL AND pm_voucher_code.meta_value <> '')
              ) ";

        $code_select = $only_voucher_code
            ? "NULLIF(pm_voucher_code.meta_value,'')"
            : "COALESCE(NULLIF(MAX(pm_dynamic_code.meta_value),''), NULLIF(pm_voucher_code.meta_value,''))";

        $voucher_product_where = $this->voucher_product_where_sql();

        $sql_count = "
            SELECT COUNT(DISTINCT o.ID)
            FROM {$posts} o
            INNER JOIN {$order_items} oi
                ON oi.order_id = o.ID
                AND oi.order_item_type = 'line_item'
            INNER JOIN {$order_itemmeta} oim_pid
                ON oim_pid.order_item_id = oi.order_item_id
                AND oim_pid.meta_key = '_product_id'
            LEFT JOIN {$order_itemmeta} oim_vid
                ON oim_vid.order_item_id = oi.order_item_id
                AND oim_vid.meta_key = '_variation_id'
            LEFT JOIN {$postmeta} pm_prod
                ON pm_prod.post_id = oim_pid.meta_value
                AND pm_prod.meta_key = %s
            LEFT JOIN {$postmeta} pm_var
                ON pm_var.post_id = oim_vid.meta_value
                AND pm_var.meta_key = %s
            {$join_voucher_code}
            {$join_dynamic}
            WHERE o.post_type = 'shop_order'
              AND o.post_status = 'wc-completed'
              AND o.post_date >= %s
              AND o.post_date <= %s
              AND {$voucher_product_where}
              {$code_where}
        ";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = (int) $wpdb->get_var($wpdb->prepare(
            $sql_count,
            $voucher_flag_key,
            $voucher_flag_key,
            $from_dt,
            $to_dt
        ));

        $total_pages = max(1, (int) ceil($total / $per_page));

        $inner = "
            SELECT
                o.ID AS order_id,
                o.post_date AS created_at,
                {$code_select} AS coupon_code
            FROM {$posts} o
            INNER JOIN {$order_items} oi
                ON oi.order_id = o.ID
                AND oi.order_item_type = 'line_item'
            INNER JOIN {$order_itemmeta} oim_pid
                ON oim_pid.order_item_id = oi.order_item_id
                AND oim_pid.meta_key = '_product_id'
            LEFT JOIN {$order_itemmeta} oim_vid
                ON oim_vid.order_item_id = oi.order_item_id
                AND oim_vid.meta_key = '_variation_id'
            LEFT JOIN {$postmeta} pm_prod
                ON pm_prod.post_id = oim_pid.meta_value
                AND pm_prod.meta_key = %s
            LEFT JOIN {$postmeta} pm_var
                ON pm_var.post_id = oim_vid.meta_value
                AND pm_var.meta_key = %s
            {$join_voucher_code}
            {$join_dynamic}
            WHERE o.post_type = 'shop_order'
              AND o.post_status = 'wc-completed'
              AND o.post_date >= %s
              AND o.post_date <= %s
              AND {$voucher_product_where}
              {$code_where}
            GROUP BY o.ID
        ";

        $outer = "
            SELECT
                x.order_id,
                x.created_at,
                x.coupon_code
                " . ($has_lookup ? ",
                COALESCE(rl.redeemed_count, 0) AS redeemed_count,
                rl.last_redeemed_at AS last_redeemed_at
                " : ",
                0 AS redeemed_count,
                NULL AS last_redeemed_at
                ") . "
            FROM ({$inner}) x
            LEFT JOIN {$posts} c
                ON c.post_type = 'shop_coupon'
                AND c.post_status = 'publish'
                AND c.post_title = x.coupon_code
            " . ($has_lookup ? "
            LEFT JOIN (
                SELECT
                    l.coupon_id,
                    COUNT(DISTINCT l.order_id) AS redeemed_count,
                    MAX(o2.post_date) AS last_redeemed_at
                FROM {$lookup} l
                INNER JOIN {$posts} o2 ON o2.ID = l.order_id
                WHERE o2.post_type = 'shop_order'
                GROUP BY l.coupon_id
            ) rl ON rl.coupon_id = c.ID
            " : "") . "
            ORDER BY x.created_at DESC
            LIMIT %d OFFSET %d
        ";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results($wpdb->prepare(
            $outer,
            $voucher_flag_key,
            $voucher_flag_key,
            $from_dt,
            $to_dt,
            $per_page,
            $offset
        ), ARRAY_A);

        if (defined('WP_DEBUG') && WP_DEBUG && !empty($wpdb->last_error)) {
            error_log('[BAB Voucher Audit] SQL error: ' . $wpdb->last_error);
        }

        $out = [];
        foreach ((array)$rows as $r) {
            $redeemed_count = isset($r['redeemed_count']) ? (int)$r['redeemed_count'] : 0;
            $out[] = [
                'order_id'      => (int) ($r['order_id'] ?? 0),
                'created_at'    => (string) ($r['created_at'] ?? ''),
                'coupon_code'   => (string) ($r['coupon_code'] ?? ''),
                'redeemed'      => $redeemed_count > 0,
                'redeemed_date' => !empty($r['last_redeemed_at']) ? (string)$r['last_redeemed_at'] : '',
            ];
        }

        return [$out, $total, $total_pages, $has_lookup];
    }

    /**
     * Dedicated export endpoint (admin-post.php) – ALWAYS returns a clean CSV.
     */
    public function handle_export_csv(): void {
        if (!current_user_can('manage_woocommerce')) wp_die('Insufficient permissions.');
        check_admin_referer(self::NONCE_ACTION);

        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';
        $tab = in_array($tab, ['overview', 'cards'], true) ? $tab : 'overview';

        $today = wp_date('Y-m-d');
        $from = isset($_GET['from']) ? sanitize_text_field(wp_unslash($_GET['from'])) : '2024-12-01';
        $to   = isset($_GET['to'])   ? sanitize_text_field(wp_unslash($_GET['to']))   : $today;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = '2024-12-01';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = $today;

        $only_voucher_code = ($tab === 'cards');

        $filename = 'voucher-' . $tab . '-' . $from . '-to-' . $to . '.csv';

        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // UTF-8 BOM so Excel (Windows) opens correctly
        echo "\xEF\xBB\xBF";

        $fh = fopen('php://output', 'w');
        if (!$fh) exit;

        // EXACT columns as the table
        fputcsv($fh, ['Order ID', 'Creation Date', 'Coupon Code', 'Redeemed?', 'Redemption Date']);

        $per_page = 1000;
        $page = 1;

        while (true) {
            list($rows, $total, $total_pages) = $this->query_rows($from, $to, $page, $per_page, $only_voucher_code);

            foreach ($rows as $r) {
                $order_id = (int) ($r['order_id'] ?? 0);

                $created_at  = !empty($r['created_at']) ? wp_date('Y-m-d H:i', strtotime($r['created_at'])) : '';
                $redeemed_at = !empty($r['redeemed_date']) ? wp_date('Y-m-d H:i', strtotime($r['redeemed_date'])) : '';

                $coupon_code = (string) ($r['coupon_code'] ?? '');
                $redeemed    = !empty($r['redeemed']) ? 'Yes' : 'No';

                fputcsv($fh, ['#' . $order_id, $created_at, $coupon_code, $redeemed, $redeemed_at]);
            }

            if ($page >= $total_pages) break;
            $page++;
            if ($page > 2000) break;
        }

        fclose($fh);
        exit;
    }

    private function build_export_url(string $tab, string $from, string $to): string {
        return wp_nonce_url(
            add_query_arg(
                [
                    'action' => self::EXPORT_ACTION,
                    'tab'    => $tab,
                    'from'   => $from,
                    'to'     => $to,
                ],
                admin_url('admin-post.php')
            ),
            self::NONCE_ACTION
        );
    }

    public function render_page() {
        if (!current_user_can('manage_woocommerce')) wp_die('Insufficient permissions.');
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>WooCommerce is not active.</p></div>';
            return;
        }

        $tab = $this->get_tab();
        $page = $this->get_int_param('paged', 1);
        $per_page = 50;

        $today = wp_date('Y-m-d');
        $default_from = '2024-12-01';
        $from = $this->get_date_param('from', $default_from);
        $to   = $this->get_date_param('to', $today);

        $only_voucher_code = ($tab === 'cards');

        list($rows, $total, $total_pages, $has_lookup) = $this->query_rows($from, $to, $page, $per_page, $only_voucher_code);

        $export_url = $this->build_export_url($tab, $from, $to);
        ?>
        <div class="wrap">
            <h1>Voucher Audit Dashboard</h1>

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab <?php echo $tab === 'overview' ? 'nav-tab-active' : ''; ?>"
                   href="<?php echo esc_url($this->admin_url_self(['tab'=>'overview','from'=>$from,'to'=>$to])); ?>">Overview</a>
                <a class="nav-tab <?php echo $tab === 'cards' ? 'nav-tab-active' : ''; ?>"
                   href="<?php echo esc_url($this->admin_url_self(['tab'=>'cards','from'=>$from,'to'=>$to])); ?>">Cards</a>
            </h2>

            <div style="background:#fff; border:1px solid #dcdcde; padding:16px; border-radius:8px; margin:16px 0;">
                <form method="get" action="" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
                    <input type="hidden" name="page" value="<?php echo esc_attr(self::MENU_SLUG); ?>" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>" />

                    <div>
                        <label for="bab_from" style="display:block; font-weight:600; margin-bottom:4px;">From</label>
                        <input type="date" id="bab_from" name="from" value="<?php echo esc_attr($from); ?>" />
                    </div>

                    <div>
                        <label for="bab_to" style="display:block; font-weight:600; margin-bottom:4px;">To</label>
                        <input type="date" id="bab_to" name="to" value="<?php echo esc_attr($to); ?>" />
                    </div>

                    <div>
                        <button class="button button-primary" type="submit">Apply</button>

                        <!-- Export link uses admin-post endpoint (reliable) -->
                        <a class="button" href="<?php echo esc_url($export_url); ?>" style="margin-left:8px;">
                            Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <div style="background:#fff; border:1px solid #dcdcde; padding:16px; border-radius:8px;">
                <p style="margin-top:0;">
                    Matching orders: <strong><?php echo esc_html($total); ?></strong> (page size <?php echo esc_html($per_page); ?>).
                    Showing rows on this page: <strong><?php echo esc_html(count($rows)); ?></strong>.
                </p>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Creation Date</th>
                            <th>Coupon Code</th>
                            <th>Redeemed?</th>
                            <th>Redemption Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="5">No results.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r):
                            $order_id = (int) $r['order_id'];
                            $order_link = admin_url('post.php?post=' . $order_id . '&action=edit');

                            $created_at = $r['created_at'] ? wp_date('Y-m-d H:i', strtotime($r['created_at'])) : '—';
                            $redeemed_at = $r['redeemed_date'] ? wp_date('Y-m-d H:i', strtotime($r['redeemed_date'])) : '—';
                        ?>
                            <tr>
                                <td><a href="<?php echo esc_url($order_link); ?>">#<?php echo esc_html($order_id); ?></a></td>
                                <td><?php echo esc_html($created_at); ?></td>
                                <td><code><?php echo esc_html((string)$r['coupon_code']); ?></code></td>
                                <td><?php echo !empty($r['redeemed']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo esc_html($redeemed_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div style="margin-top:14px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <?php
                        $base_args = ['tab'=>$tab,'from'=>$from,'to'=>$to];
                        $prev = max(1, $page - 1);
                        $next = min($total_pages, $page + 1);
                        ?>
                        <a class="button <?php echo $page <= 1 ? 'disabled' : ''; ?>"
                           href="<?php echo esc_url($this->admin_url_self(array_merge($base_args, ['paged'=>$prev]))); ?>">‹ Prev</a>
                        <span>Page <strong><?php echo esc_html($page); ?></strong> of <strong><?php echo esc_html($total_pages); ?></strong></span>
                        <a class="button <?php echo $page >= $total_pages ? 'disabled' : ''; ?>"
                           href="<?php echo esc_url($this->admin_url_self(array_merge($base_args, ['paged'=>$next]))); ?>">Next ›</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        new BAB_Voucher_Audit_Dashboard_V25();
    }
});
