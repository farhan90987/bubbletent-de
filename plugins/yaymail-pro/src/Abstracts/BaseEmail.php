<?php
namespace YayMail\Abstracts;

use YayMail\Integrations\TranslationModule;
use YayMail\YayMailTemplate;

/**
 * Base Email Class
 */
abstract class BaseEmail {

    /**
     * Contains id of the email
     * Id also means template name in some case
     *
     * @var string
     */
    protected $id;

    /**
     * Email name
     *
     * @var string
     */
    protected $title;

    /**
     * Contains recipient
     *
     * @var string
     */
    protected $recipient;

    /**
     * Which plugin email created by
     */
    protected $source = [
        'plugin_id'   => 'woocommerce',
        'plugin_name' => 'WooCommerce',
    ];

    protected $elements = [];

    protected $shortcodes = [];

    protected $root_email = null;

    protected $is_existed = true;

    /**
     * Example values: non_order, order, global_header_footer, ...
     */
    public $email_types = [ YAYMAIL_WITH_ORDER_EMAILS ];

    /**
     * Indicate which template that process is working on
     */
    public $template = null;

    /**
     * Render priority
     *
     * @var int
     */
    protected $render_priority = YAYMAIL_EMAIL_RENDER_PRIORITY;

    /**
     * Callback for yaymail_emails hook
     * Return this email data
     */
    public function get_email_data() {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'recipient' => $this->recipient,
            'source'    => $this->source,
        ];
    }

    abstract public function get_template_path();

    abstract public function get_default_elements();

    /**
     * Function check current template is WooCommerce email
     * Return boolean
     */
    protected function is_template_email( \WC_Email $email ) {
        return ! empty( $email->id ) && $email->id === $this->id;
    }

    public function get_language( $order ) {
        $current_integration = TranslationModule::get_instance()->current_integration;
        if ( ! empty( $current_integration ) ) {
            $language = $current_integration->get_order_language( $order );
            return $language;
        }
        return '';
    }

    public function get_id() {
        return $this->id;
    }

    public function get_template_file( $located, $template_name, $args ) {
        if ( ! isset( $args['email'] ) ) {
            return $located;
        }
        if ( ! $args['email'] instanceof \WC_Email || ! $this->is_template_email( $args['email'] ) ) {
            return $located;
        }
        $template_path = $this->get_template_path();
        if ( ! file_exists( $template_path ) ) {
            return $located;
        }

        $order = apply_filters( 'yaymail_order_for_language', isset( $args['order'] ) ? $args['order'] : null, $args );

        $language = $this->get_language( $order );

        $this->template = new YayMailTemplate( $this->id, $language );

        if ( ! $this->template->is_enabled() ) {
            return $located;
        }

        return $template_path;
    }

    public function get_title() {
        return $this->title ?? '';
    }

    public function get_recipient() {
        return $this->recipient ?? '';
    }

    public function get_source() {
        return $this->source;
    }

    public function register_element( $element ) {
        if ( ! ( $element instanceof BaseElement ) ) {
            return;
        }
        $this->elements[] = $element;
    }

    public function get_elements() {
        return $this->elements;
    }

    public function register_shortcodes( $shortcodes ) {
        $this->shortcodes = array_merge( $this->shortcodes, $shortcodes );
    }

    public function get_shortcodes() {
        return $this->shortcodes;
    }

    public function get_root_email() {
        return $this->root_email;
    }

    public function is_existed() {
        return ! empty( $this->id );
    }
}
