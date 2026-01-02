<?php

if (!defined('ABSPATH')) {
	exit;
}

class Custom_Stats_Rest {
	private static $value_voucher_revenue = 0;
	
	public static function init() {
		add_action('rest_api_init', [__CLASS__, 'register_routes']);
	}

	public static function register_routes() {
		$permission = function () {
			return current_user_can('manage_woocommerce') || current_user_can('manage_options'); 
		};
		
		register_rest_route('custom-stats/v1', '/products', [
			'methods'  => 'GET',
			'callback' => [__CLASS__, 'get_products'],
			'permission_callback' => $permission,
		]);
		
		register_rest_route('custom-stats/v1', '/stats', [
			'methods'  => 'GET',
			'callback' => [__CLASS__, 'get_aggregated_stats'],
			'permission_callback' => $permission,
		]);
	}
	
	public static function get_products(\WP_REST_Request $request) {
		if (!class_exists('WooCommerce') || !function_exists('wc_get_products')) {
			return new \WP_Error('woocommerce_not_found', 'WooCommerce nicht aktiv', ['status' => 404]);
		}

		try {
			$products = wc_get_products(['limit' => -1, 'status' => 'any', 'orderby' => 'name']);
			$data = [];
			
			foreach ($products as $product) {
				if (!$product) continue;
				
				// Skip products that do not exist in published or private listings
				if (!self::is_product_in_listing($product->get_id())) {
					continue;
				}
				
				$data[] = [
					'id' => $product->get_id(),
					'name' => $product->get_name(),
					'price' => $product->get_price(),
					'voucher_type' => self::get_voucher_type($product)
				];
			}

			$response_data = ['success' => true, 'data' => $data, 'count' => count($data)];
			
			if ($logs = get_rest_debug_logs()) {
				$response_data['_debug'] = $logs;
			}
			
			return rest_ensure_response($response_data);

		} catch (Exception $e) {
			return new \WP_Error('api_error', $e->getMessage(), ['status' => 500]);
		}
	}

	public static function get_aggregated_stats(\WP_REST_Request $request) {
		if (!class_exists('WooCommerce') || !function_exists('wc_get_orders')) {
			return new \WP_Error('woocommerce_not_found', 'WooCommerce nicht aktiv', ['status' => 404]);
		}

		try {
			$from_date = $request->get_param('from_date');
			$to_date = $request->get_param('to_date');
			console_log($from_date, 'From Date');
			console_log($to_date, 'To Date');
			$product_ids = self::parse_product_ids($request->get_param('product_ids'));
			
			// No products selected = return empty data
			if (empty($product_ids)) {
				return rest_ensure_response([
					'success' => true,
					'data' => [
						'time_series' => [],
						'products' => [],
						'summary' => [
							'total_orders' => 0,
							'total_revenue' => 0,
							'total_amount_paid' => 0,
							'aggregation_mode' => 'daily',
							'days_in_range' => 0
						]
					],
					'filters' => ['from_date' => $from_date, 'to_date' => $to_date, 'product_ids' => []],
					'message' => 'Keine Produkte ausgewählt'
				]);
			}
			
			// Get orders
			$args = [
				'limit' => -1,
				'orderby' => 'date',
				'order' => 'ASC',
				'status' => ['wc-completed'],
				'type' => 'shop_order'
			];
			
			if ($from_date && $to_date) {
				$args['date_created'] = $from_date . '...' . $to_date;
			}
			
			$orders = wc_get_orders($args);
			/* Debug
			foreach ($orders as $order) {
				$order_items = $order->get_items();
				console_log($order_items, 'Order Items');
			}*/
			// Determine aggregation mode
			$days_diff = 0;
			if ($from_date && $to_date) {
				$date_from = new \DateTime($from_date);
				$date_to = new \DateTime($to_date);
				$days_diff = $date_from->diff($date_to)->days;
			}
			$use_monthly = $days_diff > 31;
			
			// Reset value voucher revenue tracking
			self::$value_voucher_revenue = 0;
			
			// Process orders
			$time_agg = [];
			$product_stats = [];
			
			foreach ($orders as $order) {
				
				$order_date = $order->get_date_created();
				if (!$order_date) continue;
				
				$order_items = $order->get_items();
				if (empty($order_items)) continue;
				
				// Check if order is relevant for filtering
				$has_selected_product = false;
				
				if (!empty($product_ids)) {
					foreach ($order_items as $item) {
						if ($product = $item->get_product()) {
							// Check if this is a selected product
							if (in_array($product->get_id(), $product_ids)) {
								$has_selected_product = true;
							}
						}
					}
					
					// Skip order if it doesn't contain selected products AND doesn't affect voucher revenue
					if (!$has_selected_product) {
						continue;
					}
				}
				
				// Aggregate by time
				$time_key = $use_monthly ? $order_date->format('Y-m') : $order_date->format('Y-m-d');
				
				if (!isset($time_agg[$time_key])) {
					$time_agg[$time_key] = [
						'date' => $time_key,
						'orders' => 0,
						'revenue' => 0,
						'amount_paid' => 0
					];
				}
			
				// Calculate revenue for each item (ONLY ONCE to avoid double-counting)
				$order_revenue = 0;
				$order_amount_paid = 0;
				
				foreach ($order_items as $item) {
					if (!($product = $item->get_product())) continue;
					
					$pid = $product->get_id();
					
					// Only process items that match the filter (or if no filter is active)
					if (empty($product_ids) || in_array($pid, $product_ids)) {
						$item_revenue = self::calculate_item_revenue($item, $product, $order);
						$order_revenue += $item_revenue;
						$order_amount_paid += (float)$item->get_total();
						
						// Track in product_stats
						if (!isset($product_stats[$pid])) {
							$product_stats[$pid] = [
								'product_id' => $pid,
								'name' => $product->get_name(),
								'voucher_type' => self::get_voucher_type($product),
								'count' => 0,
								'revenue' => 0,
								'amount_paid' => 0
							];
						}
						
						$product_stats[$pid]['count'] += $item->get_quantity();
						$product_stats[$pid]['revenue'] += $item_revenue;
						$product_stats[$pid]['amount_paid'] += (float)$item->get_total();
					}
				}
				
				// Add to time aggregation
				$time_agg[$time_key]['orders']++;
				$time_agg[$time_key]['revenue'] += $order_revenue;
				$time_agg[$time_key]['amount_paid'] += $order_amount_paid;
			}
			
			// Sort and prepare response
			$time_series = array_values($time_agg);
			usort($time_series, fn($a, $b) => strcmp($a['date'], $b['date']));
			
			$products = array_values($product_stats);
			usort($products, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
			
			$total_orders = array_reduce($time_series, fn($sum, $item) => $sum + $item['orders'], 0);
			$aggregated_revenue = array_reduce($time_series, fn($sum, $item) => $sum + $item['revenue'], 0);
			$total_amount_paid = array_reduce($time_series, fn($sum, $item) => $sum + $item['amount_paid'], 0);
			
			// Combine aggregated revenue with value voucher revenue
			$total_revenue = $aggregated_revenue + self::$value_voucher_revenue;
			
			$response_data = [
				'success' => true,
				'data' => [
					'time_series' => $time_series,
					'products' => $products,
					'summary' => [
						'total_orders' => $total_orders,
						'total_revenue' => round($total_revenue, 2),
						'total_amount_paid' => round($total_amount_paid, 2),
						'aggregation_mode' => $use_monthly ? 'monthly' : 'daily',
						'days_in_range' => $days_diff,
						'value_voucher_revenue' => round(self::$value_voucher_revenue, 2),
						'aggregated_revenue' => round($aggregated_revenue, 2)
					]
				],
				'filters' => ['from_date' => $from_date, 'to_date' => $to_date, 'product_ids' => $product_ids],
			];
			
			if ($logs = get_rest_debug_logs()) {
				$response_data['_debug'] = $logs;
			}
			
			return rest_ensure_response($response_data);

		} catch (Exception $e) {
			return new \WP_Error('api_error', $e->getMessage(), ['status' => 500]);
		}
	}

	// ===== CORE REVENUE LOGIC (PER ITEM) =====

	private static function calculate_item_revenue($item, $product, $order) {
		$amount_paid = (float)$item->get_total(); // Price after discounts
		$item_subtotal = (float)$item->get_subtotal(); // Price before discounts
		$total_discount = $item_subtotal - $amount_paid; // Total discount applied to this item
		
		$voucher_type = self::get_voucher_type($product);
		$is_own = self::is_own_location_product($product);
		$used_vouchers = self::get_used_vouchers_for_payment($order);
		$paid_with_voucher = $used_vouchers && count($used_vouchers) > 0;

		// CASE 1: Selling a value voucher
		if ($voucher_type === 'value') {
			self::$value_voucher_revenue += $amount_paid; // Track value voucher sales
			return $amount_paid; // 100% revenue
		}
		
		// CASE 2: Selling a location voucher
		if ($voucher_type === 'location') {
			return round($amount_paid * ($is_own ? 0.50 : 0.20), 2);
		}
	
		// CASE 3: Booking paid with vouchers
		if ($voucher_type === null && $paid_with_voucher) {
			// Calculate how much of the discount comes from each voucher type
			$voucher_discounts = self::calculate_voucher_discounts($used_vouchers, $total_discount);
			
			$value_voucher_discount = $voucher_discounts['value'];
			$location_voucher_discount = $voucher_discounts['location'];
			
			console_log([
				'item_subtotal' => $item_subtotal,
				'amount_paid' => $amount_paid,
				'total_discount' => $total_discount,
				'value_voucher_discount' => $value_voucher_discount,
				'location_voucher_discount' => $location_voucher_discount,
				'product_name' => $product->get_name()
			], 'Item Revenue Calculation');
			
			// Amount actually paid by customer (cash/card)
			$cash_payment = $amount_paid;
			
			// Amount paid with value vouchers
			if ($value_voucher_discount > 0) {
				// Deduct from value voucher revenue (was counted at sale)
				self::$value_voucher_revenue -= $value_voucher_discount;
			}
			
			// Calculate revenue:
			// - Cash payment gets normal percentage
			// - Value voucher payment gets normal percentage (revenue shift from voucher sale to booking)
			// - Location voucher payment gets 0% (already counted at voucher sale)
			
			$revenue_from_cash = $cash_payment * ($is_own ? 0.50 : 0.20);
			$revenue_from_value_voucher = $value_voucher_discount * ($is_own ? 0.50 : 0.20);
			$revenue_from_location_voucher = 0; // Already counted at sale
			
			$total_revenue = $revenue_from_cash + $revenue_from_value_voucher + $revenue_from_location_voucher;
			
			return round($total_revenue, 2);
		}
		
		// CASE 4: Standard booking (card/PayPal)
		if ($voucher_type === null) {
			return round($amount_paid * ($is_own ? 0.50 : 0.20), 2);
		}
		
		return 0.0;
	}
	
	/**
	 * Calculate how much discount each voucher type contributed
	 * Distributes total discount proportionally based on coupon amounts
	 */
	private static function calculate_voucher_discounts($used_vouchers, $total_discount) {
		$value_voucher_total = 0;
		$location_voucher_total = 0;
		$total_voucher_amount = 0;
		
		// Sum up voucher amounts by type
		foreach ($used_vouchers as $voucher) {
			$amount = (float)$voucher['coupon_amount'];
			$total_voucher_amount += $amount;
			
			if ($voucher['voucher_type'] === 'value') {
				$value_voucher_total += $amount;
			} else if ($voucher['voucher_type'] === 'location') {
				$location_voucher_total += $amount;
			}
		}
		
		// If no vouchers or no discount, return zeros
		if ($total_voucher_amount == 0 || $total_discount == 0) {
			return ['value' => 0, 'location' => 0];
		}
		
		// Distribute discount proportionally
		$value_discount = ($value_voucher_total / $total_voucher_amount) * $total_discount;
		$location_discount = ($location_voucher_total / $total_voucher_amount) * $total_discount;
		
		return [
			'value' => round($value_discount, 2),
			'location' => round($location_discount, 2)
		];
	}


	// parse for frontend product filter request helper function
	private static function parse_product_ids($product_ids) {
		if (is_string($product_ids)) {
			if (trim($product_ids) === '') return [];
			$product_ids = array_map('intval', explode(',', $product_ids));
			return array_values(array_filter($product_ids, fn($id) => $id > 0));
		}
		return is_array($product_ids) ? $product_ids : [];
	}

	private static function is_product_in_listing($product_id) {
		global $wpdb;
		
		$meta_values = $wpdb->get_col("
			SELECT pm.meta_value
			FROM {$wpdb->posts} p
			JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'listing'
			AND (p.post_status = 'publish' OR p.post_status = 'private')
			AND (pm.meta_key = 'product_id' OR pm.meta_key = '_wooproduct_id')
			AND pm.meta_value != ''
			AND pm.meta_value IS NOT NULL
		");
		
		foreach ($meta_values as $value) {
			
			$product_ids = array_map('trim', explode(',', $value)); // 27925, 146312 => [27925, 146312]
			
			if (in_array((string)$product_id, $product_ids, true)) { // true => strict type comparison
				return true;
			}
		}
		
		return false;
	}

	private static function get_voucher_type($product) {
		$product_id = $product->get_id();
		
		// Specific value voucher IDs
		if ($product_id == 27925 || $product_id == 146312) {
			return 'value';
		}
		
		// Downloadable = location voucher
		if ($product->is_downloadable()) {
			return 'location';
		}
		
		return null;
	}

	private static function get_used_vouchers_for_payment($order) {
		$coupon_codes = $order->get_used_coupons();
		
		if (empty($coupon_codes)) {
			return [];
		}
		
		// Get order postmeta to check for value vouchers
		$order_id = $order->get_id();
		$order_postmeta = get_post_meta($order_id);
		console_log($order_postmeta, 'Order Postmeta');
		
		$voucher_info = [];
		
		foreach ($coupon_codes as $coupon_code) {
			$coupon = new WC_Coupon($coupon_code);
			$coupon_id = $coupon->get_id();
			
			// Check if this is a value voucher by looking for fcpdf_order_item*_coupon_code
			$is_value_voucher = false;
			
			foreach ($order_postmeta as $meta_key => $meta_values) {
				// Check if key starts with 'fcpdf_order_item' and ends with '_coupon_code'
				if (strpos($meta_key, 'fcpdf_order_item') === 0 && substr($meta_key, -12) === '_coupon_code') {
					// Check if the value matches our coupon code
					if (isset($meta_values[0]) && $meta_values[0] === $coupon_code) {
						$is_value_voucher = true;
						break;
					}
				}
			}
			
			$voucher_type = $is_value_voucher ? 'value' : null;

			if ($voucher_type === null) {
				// Check if it's a location voucher by extracting order number from coupon code
				// and checking if that order purchased a location voucher
				
				// Extract order number from coupon code (e.g., "VOUCHER-12345" -> 12345)
				preg_match('/(\d+)/', $coupon_code, $matches);
				
				if (!empty($matches)) {
					$potential_order_id = (int)$matches[0];
					$voucher_order = wc_get_order($potential_order_id);
					
					if ($voucher_order) {
						// Check if this order contains a location voucher product
						$voucher_order_items = $voucher_order->get_items();
						
						foreach ($voucher_order_items as $voucher_item) {
							$voucher_product = $voucher_item->get_product();
							if (!$voucher_product) continue;
							
							$voucher_product_id = $voucher_product->get_id();

							$voucher_type = self::get_voucher_type($voucher_product);
							if ($voucher_type === 'location') {
								break;
							}
						}
					}
				}
			}
			
			$voucher_data = [
				'coupon_code' => $coupon_code,
				'coupon_id' => $coupon_id,
				'coupon_type' => $coupon->get_discount_type(),
				'coupon_amount' => $coupon->get_amount(),
				'coupon_usage_count' => $coupon->get_usage_count(),
				'voucher_type' => $voucher_type,
			];
			
			console_log($voucher_data, 'Coupon & Voucher Info');
			
			$voucher_info[] = $voucher_data;
		}
		
		return $voucher_info;
	}

	private static function is_own_location_product($product) {
		$property_is_owned = get_post_meta($product->get_id(), 'is_property_owned', true) === 'yes';
		//console_log($product->get_id(), 'Product ID');
		//console_log($property_is_owned, 'Property is owned');
		return $property_is_owned;
	}
}