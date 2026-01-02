<?php
namespace MWEW\Inc\Google_Tags;

class Booking_Tracker {
    public function track() {
        $this->left_over_booking();

        if (!Utilities::is_tracking_enabled() || !Utilities::is_thank_you_page()) return;

        $booking_data = $this->get_booking_data();

        if (empty($booking_data['bookings'])) {
            return;
        }

        ?>
        <script>
            sessionStorage.setItem('mwew_booking_completed', true);

            const reservationData = <?php echo wp_json_encode($booking_data); ?>;
            sessionStorage.setItem('mwew_reservation_data', JSON.stringify(reservationData));

            dataLayer.push({
                event: 'booking_completed',
                order_id: '<?php echo esc_js($booking_data['order_id']); ?>',
                booking_value: <?php echo esc_js($booking_data['value']); ?>,
                bookings: reservationData.bookings
            });
        </script>
        <?php
    }



    private function get_booking_data() {

        $order_id = absint(get_query_var('order-received'));
        $order = wc_get_order($order_id);
        if (!$order) return [];

        $result = [
            'order_id' => $order_id,
            'value' => (float) $order->get_total(),
            'bookings' => []
        ];


        $order_meta = [];
        foreach ($order->get_meta_data() as $meta) {
            $order_meta[$meta->key] = $meta->value;
        }

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product) {
                $booking = [
                    'bubble_id' => $product->get_id(),
                    'bubble_name' => $product->get_name(),
                    'checkin_date' => '',
                    'checkout_date' => '',
                    'num_guests' => 0,
                    'num_nights' => 0,
                    'num_adults' => (int) ($order_meta['_number_of_adults'] ?? 0),
                    'num_kids' => (int) ($order_meta['_number_of_kids'] ?? 0)
                ];

                $booking['checkin_date'] = $item->get_meta('checkin_date') ?: ($order_meta['smoobu_calendar_start'] ?? '');
                $booking['checkout_date'] = $item->get_meta('checkout_date') ?: ($order_meta['smoobu_calendar_end'] ?? '');

                $booking['num_guests'] = (int) ($item->get_meta('num_guests') ?: ($booking['num_adults'] + $booking['num_kids']));

                $num_nights_meta = $item->get_meta('num_nights');
                if ($num_nights_meta) {
                    $booking['num_nights'] = (int) $num_nights_meta;
                } else {
                    $checkin = \DateTime::createFromFormat('d-m-Y', $booking['checkin_date']);
                    $checkout = \DateTime::createFromFormat('d-m-Y', $booking['checkout_date']);

                    if ($checkin && $checkout) {
                        $interval = $checkin->diff($checkout);
                        $booking['num_nights'] = max((int) $interval->days, 0);
                    } else {
                        $booking['num_nights'] = 0;
                    }
                }

                $result['bookings'][] = $booking;
            }
        }

        return $result;
    }

    private function left_over_booking(){
        if (!Utilities::is_tracking_enabled()) return;
        ?>

        <script>
            if (sessionStorage.getItem('mwew_booking_completed') !== 'true') {
                window.addEventListener('beforeunload', function() {
                    var reservationData = sessionStorage.getItem('mwew_reservation_data');
                    if (reservationData) {
                        try {
                            var data = JSON.parse(reservationData);
                            if (Array.isArray(data) && data.length > 0) {
                                dataLayer.push({
                                    event: 'date_selected',
                                    reservations: data
                                });
                                //console.log('Abandonment tracked - all reservations:', data);
                                sessionStorage.setItem('mwew_reservation_data', []);
                            }
                        } catch (e) {
                            console.warn('Invalid reservation data in sessionStorage');
                        }
                    }
                });
            }
        </script>



        <?php
    }

}