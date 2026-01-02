<?php 

namespace MWEW\Inc\Orders;

use \Smoobu_Booking_From_Woo;
class Order_Meta{
    public function __construct() {
        remove_action('woocommerce_admin_order_items_after_line_items', [Smoobu_Booking_From_Woo::instance(), 'display_custom_field_in_order_details'], 10);
        add_action('woocommerce_admin_order_items_after_line_items', [$this, 'smoobu_booking_from_table'], 10, 1);
    }
    public function smoobu_booking_from_table($order_id)
	{
		$order = wc_get_order($order_id);

		$has_simple_product = false;

		foreach ($order->get_items() as $item) {
			$product_id = $item->get_product_id();
			$product = wc_get_product($product_id);

			if ($product && $product->is_type('listing_booking')) {
				$has_simple_product = true;
				break;
			}
		}

		if (!$has_simple_product) {
			return;
		}

        $meta_keys = array(
            'smoobu_calendar_start' => ['label' => __('Check-in Date', 'mwew'), 'type' => 'text', 'id' => 'mwew-checkin-date', 'attr' => 'readonly', 'thumb' => 'Calendar.svg'],
            'smoobu_calendar_end'   => ['label' => __('Check-out Date', 'mwew'), 'type' => 'text', 'id' => 'mwew-checkout-date', 'attr' => 'readonly', 'thumb' => 'Calendar.svg'],
            'smoobu_checkin_time'   => ['label' => __('Check-in Time', 'mwew'), 'type' => 'text', 'id' => 'mwew-checkin-time', 'attr' => 'readonly', 'thumb' => 'time.png'],
            '_number_of_adults'     => ['label' => __('Number of Adults', 'mwew'), 'type' => 'number', 'id' => 'mwew-number-of-adults', 'attr' => 'readonly', 'thumb' => 'adults.png'],
            '_number_of_kids'       => ['label' => __('Number of Kids', 'mwew'), 'type' => 'number', 'id' => 'mwew-number-of-kids', 'attr' => 'readonly', 'thumb' => 'kids.png'],
            'message_to_landlord'   => ['label' => __('Message To Landlord', 'mwew'), 'type' => 'div', 'id' => 'mwew-message-to-landlord', 'attr' => 'readonly', 'thumb' => ''],
        );
		?>

		<?php foreach ($meta_keys as $key => $value): ?>
			<?php if ($order->get_meta($key)): ?>
				<tr class="item">
					<td class="thumb">
                        <?php if ($value['thumb']): ?>
                            <img src="<?php echo esc_url(MWEW_PATH_URL . 'assets/images/' . $value['thumb']); ?>" alt="<?php echo esc_attr($value['label']); ?>" style="width: 30px; height: 30px;">
                        <?php else: ?>
                            <img src="<?php echo esc_url(MWEW_PATH_URL . 'assets/images/default-thumb.jpg'); ?>" alt="<?php echo esc_attr($value['label']); ?>" style="width: 30px; height: 30px;">
                        <?php endif; ?>
					</td>
					<td class="name" colspan="2">
						<?php echo $value['label']; //phpcs:ignore ?>
					</td>
					<td class="item_cost" colspan="4">
						<?php if ($value['type'] === 'textarea'): ?>
							<textarea
								id="<?php echo esc_attr($value['id']); ?>"
								style="width: 100%; min-height: 90px;"
								<?php echo !empty($value['attr']) ? $value['attr'] : ''; ?>
							><?php echo esc_textarea($order->get_meta($key)); ?></textarea>
						<?php elseif($value['type'] === 'div'): ?>
							<div
								id="<?php echo esc_attr($value['id']); ?>"
								style="width: 100%; min-height: 90px;"
							><?php echo esc_html($order->get_meta($key)); ?></div>
						<?php else: ?>
							<input
								type="<?php echo esc_attr($value['type']); ?>"
								id="<?php echo esc_attr($value['id']); ?>"
								value="<?php echo esc_attr($order->get_meta($key)); ?>"
								style="width: 130px;"
								<?php echo !empty($value['attr']) ? $value['attr'] : ''; ?>
							/>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
        <tr>
            <td class="thumb"></td>
			<td class="name" colspan="1"></td>
            <td class="item_cost" colspan="5">
                <button type="button" class="button button-primary update-unit-price" data-order-id="<?php echo $order_id; ?>"><?php esc_html_e('Update Price', 'mwew'); ?></button>
                <button type="button" class="button button-primary save-smoobu-data" data-order-id="<?php echo $order_id; ?>"><?php esc_html_e('Update Data', 'mwew'); ?></button>
            </td>
        </tr>
	<?php
	}
}