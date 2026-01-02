"use strict";

(function($) {
    function save_order_meta(){

        $(document).on('click', '.save-smoobu-data', function(e) {
            e.preventDefault();
            var $button = $(this);
            var btn_text = $button.text();
            $button.text('Saving...').prop('disabled', true);
            var order_id = $button.data('order-id');
            var check_in_date = $('#mwew-checkin-date').val();
            var check_out_date = $('#mwew-checkout-date').val();

            var data = {
                action: 'mwew_save_smoobu_data',
                order_id: order_id,
                check_in_date: check_in_date,
                check_out_date: check_out_date,
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('Data saved successfully!');
                    location.reload()
                } else {
                    alert('Error saving data: ' + response.data);
                }
                $button.text(btn_text).prop('disabled', false);
            });
            
        });
    }

    function save_price_meta() {
        $(document).on('click', '.update-unit-price', function(e) {
            e.preventDefault();
            var $button = $(this);
            var btn_text = $button.text();

            var new_price = prompt("Enter the new price:");

            if (new_price === null) {
                return;
            }

            new_price = new_price.trim();

            if (new_price === "" || isNaN(new_price)) {
                alert("Please enter a valid number (e.g., 1 or 1.0).");
                return;
            }

            new_price = parseFloat(new_price);

            $button.text('Saving...').prop('disabled', true);
            var order_id = $button.data('order-id');

            var data = {
                action: 'mwew_save_price_data',
                order_id: order_id,
                price: new_price
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('Data saved successfully!');
                    location.reload();
                } else {
                    alert('Error saving data: ' + response.data);
                }
                $button.text(btn_text).prop('disabled', false);
            });
        });
    }

    $(document).ready(function() {
        save_order_meta();
        save_price_meta();
    });
})(jQuery);