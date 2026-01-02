<?php

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

class German_Market_Lexoffice_API_Shipping_Address {

	/**
	 * Normalize order references (e.g. refunds) to their parent order.
	 *
	 * @param mixed $order
	 * @return WC_Abstract_Order|null
	 */
	protected static function get_base_order( $order ) {

		if ( ! is_object( $order ) || ! is_a( $order, 'WC_Abstract_Order' ) ) {
			return null;
		}

		if ( method_exists( $order, 'get_type' ) && 'shop_order_refund' === $order->get_type() ) {
			$parent_id = method_exists( $order, 'get_parent_id' ) ? $order->get_parent_id() : 0;
			if ( $parent_id ) {
				$parent_order = wc_get_order( $parent_id );
				if ( $parent_order instanceof WC_Abstract_Order ) {
					return $parent_order;
				}
			}
		}

		return $order;
	}

	/**
	 * Determine if an order uses a local pickup shipping method.
	 *
	 * @param mixed $order Order or refund object.
	 * @return bool
	 */
	public static function get_order_has_local_pickup( $order ) {

		$order = self::get_base_order( $order );
		$has_local_pickup = false;

		if ( ! $order ) {
			return apply_filters( 'german_market_lexoffice_order_has_local_pickup', false, $order );
		}

		$shipping_methods = $order->get_shipping_methods();

		if ( ! empty( $shipping_methods ) ) {
			foreach ( $shipping_methods as $shipping_method ) {
				$method_id = self::get_shipping_method_id( $shipping_method );

				if ( '' === $method_id ) {
					continue;
				}

				$normalized_id = self::normalize_shipping_method_id( $method_id );

				if ( in_array( $normalized_id, array( 'pickup_location', 'local_pickup' ), true ) ) {
					$has_local_pickup = true;
					break;
				}
			}
		}

		return apply_filters( 'german_market_lexoffice_order_has_local_pickup', $has_local_pickup, $order );
	}

	/**
	 * Extract the shipping method identifier from the provided object/array.
	 *
	 * @param mixed $shipping_method
	 * @return string
	 */
	protected static function get_shipping_method_id( $shipping_method ) {
		$method_id = '';

		if ( is_object( $shipping_method ) ) {
			if ( method_exists( $shipping_method, 'get_method_id' ) ) {
				$method_id = $shipping_method->get_method_id();
			} elseif ( isset( $shipping_method->method_id ) ) {
				$method_id = $shipping_method->method_id;
			} elseif ( isset( $shipping_method->id ) ) {
				$method_id = $shipping_method->id;
			}
		} elseif ( is_array( $shipping_method ) ) {
			if ( isset( $shipping_method['method_id'] ) ) {
				$method_id = $shipping_method['method_id'];
			} elseif ( isset( $shipping_method['id'] ) ) {
				$method_id = $shipping_method['id'];
			}
		}

		return is_string( $method_id ) ? $method_id : '';
	}

	/**
	 * Normalize a shipping method identifier (remove instance suffix, lowercase).
	 *
	 * @param string $method_id
	 * @return string
	 */
	protected static function normalize_shipping_method_id( $method_id ) {
		$method_id = strtolower( (string) $method_id );

		if ( false !== strpos( $method_id, ':' ) ) {
				$method_id = strstr( $method_id, ':', true );
		}

		return $method_id;
	}

	

	/**
	 * Resolve a shipping value with pickup awareness.
	 *
	 * @param mixed  $order
	 * @param string $field
	 * @return string
	 */
	protected static function get_shipping_value( $order, $field ) {
		$value	= '';
		$location = self::get_taxable_location_for_order( $order );

		if ( isset( $location[ $field ] ) && '' !== $location[ $field ] ) {
			$value = $location[ $field ];
		} else {
			$order  = self::get_base_order( $order );
			$method = 'get_shipping_' . $field;

			if ( $order && method_exists( $order, $method ) ) {
				$value = $order->$method();
			}
		}

		$filter_name = sprintf( 'german_market_lexoffice_shipping_%s', $field );

		return apply_filters( $filter_name, $value, $order );
	}

	/**
	 * Retrieve the shipping country for the given order.
	 *
	 * @param mixed $order
	 * @return string
	 */
	public static function get_shipping_country( $order ) {
		return self::get_shipping_value( $order, 'country' );
	}

	/**
	 * Retrieve the shipping state for the given order.
	 *
	 * @param mixed $order
	 * @return string
	 */
	public static function get_shipping_state( $order ) {
		return self::get_shipping_value( $order, 'state' );
	}

	/**
	 * Retrieve the shipping postcode for the given order.
	 *
	 * @param mixed $order
	 * @return string
	 */
	public static function get_shipping_postcode( $order ) {
		return self::get_shipping_value( $order, 'postcode' );
	}

	/**
	 * Retrieve the shipping city for the given order.
	 *
	 * @param mixed $order
	 * @return string
	 */
	public static function get_shipping_city( $order ) {
		return self::get_shipping_value( $order, 'city' );
	}

	/**
	 * Retrieve the shipping street for the given order.
	 *
	 * @param mixed $order Order or refund object.
	 * @return string
	 */
	public static function get_shipping_address_1( $order ) {
		return self::get_shipping_value( $order, 'address_1' );
	}

	/**
	 * Retrieve the shipping address supplement for the given order.
	 *
	 * @param mixed $order Order or refund object.
	 * @return string
	 */
	public static function get_shipping_address_2( $order ) {
		return self::get_shipping_value( $order, 'address_2' );
	}

	/**
	 * Return the list of location fields supported when resolving pickup data.
	 *
	 * @return array
	 */
	protected static function get_location_fields() {
		return array( 'country', 'state', 'postcode', 'city', 'address_1', 'address_2' );
	}

	/**
	 * Normalize location values from the provided source array.
	 *
	 * @param array $source Source data.
	 * @param array $map	Mapping of normalized fields to possible keys in the source array.
	 * @return array
	 */
	protected static function normalize_location_values( $source, $map ) {
		$fields	 = self::get_location_fields();
		$normalized = array_fill_keys( $fields, '' );

		foreach ( $map as $field => $candidates ) {
			foreach ( (array) $candidates as $candidate ) {
				if ( isset( $source[ $candidate ] ) && '' !== $source[ $candidate ] ) {
					$normalized[ $field ] = $source[ $candidate ];
					break;
				}
			}
		}

		return $normalized;
	}

	/**
	 * Retrieve pickup address data stored on the shipping method item.
	 *
	 * @param WC_Order_Item_Shipping $shipping_method Shipping method item.
	 * @return array
	 */
	protected static function get_pickup_address_from_meta( $shipping_method ) {
			
			$address = array();

			if ( is_object( $shipping_method ) && method_exists( $shipping_method, 'get_meta' ) ) {
				$order_pickup_address = $shipping_method->get_meta( 'pickup_address' );
		   		
				$pickup_locations = get_option( 'pickup_location_pickup_locations', array() );
				foreach ( $pickup_locations as $pickup_location ) {
					if ( isset( $pickup_location[ 'address' ] ) ) {
						$formatted_address = wc()->countries->get_formatted_address( $pickup_location[ 'address' ], ', ' );
						if ( $order_pickup_address === $formatted_address ) {
							$address = $pickup_location[ 'address' ];
							break;
						}
					}
				}
			}

			if ( ! is_array( $address ) || empty( $address ) ) {
					return array();
			}

			$map = array(
					'country'   => array( 'country', 'country_code', 'countryCode' ),
					'state'	 => array( 'state', 'state_code', 'stateCode', 'region' ),
					'postcode'  => array( 'postcode', 'post_code', 'zip', 'postalCode' ),
					'city'	  => array( 'city', 'locality', 'town' ),
					'address_1' => array( 'address_1', 'address1', 'line1', 'street_1', 'street1', 'street' ),
					'address_2' => array( 'address_2', 'address2', 'line2', 'street_2', 'street2' ),
			);

			return self::normalize_location_values( $address, $map );
	}

	/**
	 * Get the store address configuration for local pickup fallback.
	 *
	 * @return array
	 */
	protected static function get_store_pickup_address() {
		$fields = array_fill_keys( self::get_location_fields(), '' );

		$fields['address_1'] = get_option( 'woocommerce_store_address', '' );
		$fields['address_2'] = get_option( 'woocommerce_store_address_2', '' );
		$fields['postcode']  = get_option( 'woocommerce_store_postcode', '' );
		$fields['city']	  = get_option( 'woocommerce_store_city', '' );

		$base_location = array();

		if ( function_exists( 'wc_get_base_location' ) ) {
				$base_location = wc_get_base_location();
		} else {
				$default_country = get_option( 'woocommerce_default_country', '' );
				if ( false !== strpos( $default_country, ':' ) ) {
						list( $fields['country'], $fields['state'] ) = array_pad( explode( ':', $default_country ), 2, '' );
				} else {
						$fields['country'] = $default_country;
				}
		}

		if ( isset( $base_location['country'] ) ) {
				$fields['country'] = $base_location['country'];
		}

		if ( isset( $base_location['state'] ) ) {
				$fields['state'] = $base_location['state'];
		}

		return $fields;
	}

	/**
	 * Retrieve the taxable location data for pickup orders.
	 *
	 * @param mixed $order Order or refund object.
	 * @return array
	 */
	protected static function get_taxable_location_for_order( $order ) {
		$order = self::get_base_order( $order );

		if ( ! $order || ! self::get_order_has_local_pickup( $order ) ) {
				return array();
		}

		$shipping_methods = $order->get_shipping_methods();
		$resolved_method  = '';
		$resolved_object  = null;

		if ( ! empty( $shipping_methods ) ) {
			foreach ( $shipping_methods as $shipping_method ) {
				$method_id = self::get_shipping_method_id( $shipping_method );

				if ( '' === $method_id ) {
						continue;
				}

				$normalized_id = self::normalize_shipping_method_id( $method_id );

				if ( ! in_array( $normalized_id, array( 'pickup_location', 'local_pickup' ), true ) ) {
						continue;
				}

				$resolved_method = $normalized_id;
				$resolved_object = $shipping_method;

				if ( 'pickup_location' === $normalized_id ) {
						$pickup_location = self::get_pickup_address_from_meta( $shipping_method );

						if ( array_filter( $pickup_location ) ) {
								return apply_filters( 'german_market_lexoffice_pickup_taxable_location', $pickup_location, $order, $shipping_method );
						}
				}
			}
		}

		if ( ! method_exists( $order, 'get_taxable_location' ) ) {
				return array();
		}

		$location = $order->get_taxable_location();

		if ( ! is_array( $location ) || empty( $location ) ) {
				return array();
		}

		$map = array(
				'country'  => array( 'country', 0 ),
				'state'	=> array( 'state', 1 ),
				'postcode' => array( 'postcode', 2 ),
				'city'	 => array( 'city', 3 ),
		);

		$normalized = self::normalize_location_values( $location, $map );

		if ( 'local_pickup' === $resolved_method ) {
				$store_address = self::get_store_pickup_address();

				foreach ( $store_address as $field => $value ) {
						if ( '' === $value ) {
								continue;
						}

						if ( in_array( $field, array( 'address_1', 'address_2' ), true ) ) {
								$normalized[ $field ] = $value;
						} elseif ( '' === $normalized[ $field ] ) {
								$normalized[ $field ] = $value;
						}
				}
		}

		return apply_filters( 'german_market_lexoffice_pickup_taxable_location', $normalized, $order, $resolved_object );
	}
}
