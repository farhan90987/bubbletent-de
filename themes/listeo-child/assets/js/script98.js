jQuery(document).ready(function () {
	jQuery(".order_back").on("click", function () {
		jQuery("#booking_sec").hide();
		jQuery("#order-main").show();
	});
	jQuery(".listings-filters > a").on("click", function () {

		jQuery(this).siblings().removeClass('active');
		jQuery(this).addClass('active');

		jQuery('#listings-wrap').addClass('loading');
		var tax_id = jQuery(this).attr('id');
		var reg_id = jQuery('.regions-filters > a.active').attr('id');

		var data = {
			'action': 'listng_filters',
			'tax-id': tax_id,
			'reg-id': reg_id
		};
		jQuery.ajax({
			url: ajax_object.ajax_url,
			data: data,
			type: 'POST',

			success: function (data) {
				jQuery('#listings-wrap').html(data);
				jQuery('#listings-wrap').removeClass('loading');
			}
		});

	});

	jQuery(".regions-filters > a").on("click", function () {

		jQuery(this).siblings().removeClass('active');
		jQuery(this).addClass('active');

		jQuery('.listings-filters > a').removeClass('active');
		jQuery('.listings-filters > a#all_loc').addClass('active');

		jQuery('#listings-wrap').addClass('loading');
		var reg_id = jQuery(this).attr('id');
		// var tax_id = jQuery('.listings-filters > a.active').attr('id');
		var tax_id = 'all_loc';

		if (reg_id == 187) {
			jQuery('.listings-filters > a[catName="typ216"]').hide();
			jQuery('.listings-filters > a[catName="typ222"]').hide();
			jQuery('.listings-filters > a[catName="typ214"]').hide();
			jQuery('.listings-filters > a[catName="typ215"]').hide();
			jQuery('.listings-filters > a[catName="typ186"]').hide();
			jQuery('.listings-filters > a[catName="typ155"]').hide();
			jQuery('.listings-filters > a[catName="typ156"]').hide();
			jQuery('.listings-filters > a[catName="typ221"]').hide();
			jQuery('.listings-filters > a[catName="typ213"]').hide();
		} else {
			jQuery('.listings-filters > a[catName="typ216"]').show();
			jQuery('.listings-filters > a[catName="typ222"]').show();
			jQuery('.listings-filters > a[catName="typ214"]').show();
			jQuery('.listings-filters > a[catName="typ215"]').show();
			jQuery('.listings-filters > a[catName="typ186"]').show();
			jQuery('.listings-filters > a[catName="typ155"]').show();
			jQuery('.listings-filters > a[catName="typ156"]').show();
			jQuery('.listings-filters > a[catName="typ221"]').show();
			jQuery('.listings-filters > a[catName="typ213"]').show();
		}

		var data = {
			'action': 'listng_filters',
			'tax-id': tax_id,
			'reg-id': reg_id
		};
		jQuery.ajax({
			url: ajax_object.ajax_url,
			data: data,
			type: 'POST',

			success: function (data) {
				jQuery('#listings-wrap').html(data);
				jQuery('#listings-wrap').removeClass('loading');
			}
		});

	});



	// Region Filters
	jQuery('.proForm-cbox input').change(function () {

		jQuery('.region-tents').addClass('loading');
		var cur_trmID = jQuery('body').find('#cur_trm_id').val();
		let arr = [];

		jQuery(".proForm-cbox input:checkbox:checked").each(function () {
			arr.push(jQuery(this).val());
		});

		var data = {
			'action': 'rgn_tents_filter',
			'features': arr,
			'curID': cur_trmID
		};
		jQuery.ajax({
			url: ajax_object.ajax_url,
			data: data,
			type: 'POST',

			success: function (data) {
				jQuery('.region-tents').html(data);
				jQuery('.region-tents').removeClass('loading');
			}
		});


	});

	if (jQuery(window).width() < 768) {
		jQuery(".ts-dsk-sidebar").appendTo(".ts-mobsidebar");
	}
	jQuery(document).on("click", ".no-back", function() {
		jQuery('#order-login').show();
		jQuery('#order-main').hide();
		jQuery("window").scrollTop(0);
	});
	jQuery(document).on("click", "#modify_booking", function() {
		jQuery('#booking_sec').show();
		jQuery('#order-main').hide();
		jQuery("window").scrollTop(0);
	});
	jQuery(document).on("click", "#booking_cancel", function() {
		// e.preventDefault();
		jQuery('#booking_sec').hide();
		jQuery('#cancel_sec').show();
		jQuery("window").scrollTop(0);
	});
	jQuery(document).on("click", ".tent--details-btn", function() {
		jQuery('.cancel-policy').slideToggle();
		jQuery("window").scrollTop(0);
	});
	jQuery(document).on("click", ".pp-btn", function() {
		jQuery('.property-policy').slideToggle();
		jQuery("window").scrollTop(0);
	});
	jQuery(document).on("click", ".go-back button", function(){
		jQuery('#order-main').hide();
		jQuery('#order-login').show();
		jQuery("window").scrollTop(0);
	})
	jQuery(document).ready(function () {
		function getURLParam(param) {
			const urlParams = new URLSearchParams(window.location.search);
			return urlParams.get(param);
		}

		const orderNumber = getURLParam('order_id');
		const userPassword = getURLParam('password');

		if (orderNumber && userPassword) {
			jQuery('#order-number').val(orderNumber);
			jQuery('#user_password').val(userPassword);
			triggerOrderAjax(orderNumber, userPassword);
		}

		jQuery('#order-form').on('submit', function (e) {
			e.preventDefault();

			const orderNumber = jQuery('#order-number').val();
			const userPassword = jQuery('#user_password').val();

			triggerOrderAjax(orderNumber, userPassword);
		});

		function triggerOrderAjax(orderNumber, userPassword) {
			if (orderNumber) {
				jQuery('#display-order-number').text(orderNumber);

				jQuery.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'get_order_details',
						order_id: orderNumber,
						user_password: userPassword
					},
					beforeSend: function () {
						jQuery('.ajax-loading').show();
					},
					success: function (response) {
						jQuery('.ajax-loading').hide();
						jQuery('#order-main').show();
						jQuery('#order-login').hide();
						jQuery('#customer-order-info').html(response);
						var img = jQuery('#listing_thumnail').val();
						jQuery('.page--banner').css('background', 'url(' + img + ')');
						bindCustomFormSubmit();
					},
					error: function () {
						jQuery('.ajax-loading').hide();
						alert('Error retrieving order details.');
					}
				});
			} else {
				alert('Please enter a valid order number.');
			}
		}
	});

	
// 	jQuery('#req_submit').on('submit', function(e) {
// 		e.preventDefault();

// 		// var orderNumber = jQuery('#new_order_no').val();
// 		// var userName = jQuery('#user_name').val();

//         // Collect form data
//         const formData = {
//             action: 'process_form_data',
//             vendor_name: jQuery('#vendor_name').val() || '',
//             vendor_email: jQuery('#vendor_email').val() || '',
//             vendor_number: jQuery('#vendor_number').val() || '',
//             user_name: jQuery('#user_name').val() || '',
// 			customer_phone : jQuery('#customer_phone').val() || '',
// 			billing_address : jQuery('#billing_address').val() || '',
// 			billing_country : jQuery('#billing_country').val() || '',
// 			billing_email : jQuery('#billing_email').val() || '',
//             order_no: jQuery('#new_order_no').val() || '',
//             order_password: jQuery('#order_password').val() || '',
//             order_name: jQuery('#order_name').val() || '',
//             arrival_date: jQuery('#arrival_date').val() || '',
//             departure_date: jQuery('#departure_date').val() || '',
//             checkin_time: jQuery('#checkin_time').val() || '',
//             guest_count: jQuery('#guest_count').val() || '',
//             order_price: jQuery('#order_price').val() || '',
//             special_req: jQuery('#special_req').val() || '',
//         };

//         console.log('Form Data:', formData); // Debugging

//         // AJAX request
//         jQuery.ajax({
//             url: ajax_object.ajax_url,
//             method: 'POST',
//             data: formData,
//             beforeSend: function () {
//                 jQuery('.ajax-loading').show();
//             },
//             success: function (response) {
// 				jQuery('.ajax-loading').hide();
// 				jQuery('#success_popup').show();
// 				// jQuery('#booking_sec').hide();
//                 // alert(response.message || 'Form submitted successfully!');
// 				jQuery('#req_submit')[0].reset();
//             },
//             error: function (xhr, status, error) {
// 				jQuery('.ajax-loading').hide();
//                 // console.error(error);
//                 alert('There was an error. Please try again.');
//             },
//         });
//     });
	jQuery('#cancel_order').on('submit', function (e) {
        e.preventDefault();
        const isVoucher = jQuery('#voucher').is(':checked');
        const isBankPayment = jQuery('#bank_payment').is(':checked');
		// Get the order creation date from the hidden input field
		const orderCreateDate = jQuery('#order_start_date').val(); // Format: YYYY-MM-DD
		
		// Get the original order price
		const orderPrice = parseFloat(jQuery('#order_price').val().replace('€', '')); // Remove € sign and convert to number
		
		// Convert the order creation date to a Date object
		const orderDate = new Date(orderCreateDate);
		
		// Get the current date
		const currentDate = new Date();
		
		if (orderDate < currentDate) {
			alert('Check in Date must be greater then today');
			return false;
		}
		const timeDifference = orderDate - currentDate;
		// Calculate the difference in time (milliseconds)
		
		// Convert the time difference to days
		const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24)); // Milliseconds to days
		
		// Determine refund percentage based on the days difference
		let refundPercentage = 0;
		let noRefundPrice = 0;
		let tenDaysRefundPrice = 0;
		let afterSevenDaysRefundPrice = 0;
		let beforeSevenDaysRefundPrice = 0;
		if (daysDifference < 0) {
			refundPercentage = 0; // If the current date is before the order date
			noRefundPrice = 0;
		} else if (daysDifference > 10) {
			refundPercentage = 100; // X-10 days
			tenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		} else if (daysDifference > 7 && daysDifference < 10) {
			refundPercentage = 50; // 10-7 days
			afterSevenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		} else if (daysDifference <= 7) {
			refundPercentage = 10; // 7-0 days
			beforeSevenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		}
		
		// Calculate the refund price
		const refundPrice = (orderPrice * refundPercentage) / 100;
		const new_order_price = `€${refundPrice.toFixed(2)}`;
		const formData = {
			vendor_name : jQuery('#vendor_name').val(),
			vendor_email : jQuery('#vendor_email').val(),
			vendor_number : jQuery('#vendor_number').val(),
			first_name : jQuery('#first_name').val(),
			last_name : jQuery('#last_name').val(),
			get_date_created : jQuery('#order_create_date').val(),
			customer_phone : jQuery('#customer_phone').val(),
			billing_address : jQuery('#billing_address').val(),
			billing_country : jQuery('#billing_country').val(),
			billing_email : jQuery('#billing_email').val(),
			order_no : jQuery('#new_order_no').val(),
			order_password : jQuery('#order_password').val(),
			order_name : jQuery('#order_name').val(),
			order_price : `€${refundPrice.toFixed(2)}`,
			noRefundPrice : `€${noRefundPrice.toFixed(2)}`,
			tenDaysRefundPrice : `€${tenDaysRefundPrice.toFixed(2)}`,
			afterSevenDaysRefundPrice : `€${afterSevenDaysRefundPrice.toFixed(2)}`,
			beforeSevenDaysRefundPrice : `€${beforeSevenDaysRefundPrice.toFixed(2)}`,
			arrival_date : jQuery('#order_start_date').val(),
			departure_date : jQuery('#order_end_date').val(),
			checkin_time : jQuery('#checkin_time').val(),
			guest_count : jQuery('#guest_count').val(),
			addon_price : jQuery('#addon_price').val(),
			addons_quantity : jQuery('#addons_quantity').val(),
			reason : jQuery('#cancel_reason').val(),
		}
		console.log(formData);
		
		// Update the DOM with the calculated refund price and booking days
		jQuery('.booking-day').text(daysDifference);
		jQuery('.refund-price').text(new_order_price);


        if (isVoucher) {
            jQuery('#cancel_popup').show();
			jQuery('#payment_popup').hide();
			// jQuery('#cancel_sec').hide();
			jQuery("window").scrollTop(0);
        } else if (isBankPayment) {
            jQuery('#payment_popup').show();
			jQuery('#cancel_popup').hide();
			// jQuery('#cancel_sec').hide();
			jQuery("window").scrollTop(0);
        } else {
            jQuery(this).find('.error_msg').show();   
            
        }

        // Pass data to confirm buttons
        jQuery('#confirm_refund').data(formData );
        // jQuery('#confirm_payment').data({ orderNumber, userName, reason });
    });

    // Confirm voucher refund
    jQuery('#confirm_refund').on('click', function () {

		const vendor_name = jQuery(this).data('vendor_name');
		const vendor_email = jQuery(this).data('vendor_email');
		const vendor_number = jQuery(this).data('vendor_number');
		const first_name = jQuery(this).data('first_name');
		const last_name = jQuery(this).data('last_name');
		const get_date_created = jQuery(this).data('get_date_created');
		const customer_phone = jQuery(this).data('customer_phone');
		const billing_address = jQuery(this).data('billing_address');
		const billing_country = jQuery(this).data('billing_country');
		const billing_email = jQuery(this).data('billing_email');
		const order_no = jQuery(this).data('order_no');
		const order_password = jQuery(this).data('order_password');
		const order_name = jQuery(this).data('order_name');
		const order_price = jQuery(this).data('order_price');
		const noRefundPrice = jQuery(this).data('noRefundPrice');
		const tenDaysRefundPrice = jQuery(this).data('tenDaysRefundPrice');
		const afterSevenDaysRefundPrice = jQuery(this).data('afterSevenDaysRefundPrice');
		const beforeSevenDaysRefundPrice = jQuery(this).data('beforeSevenDaysRefundPrice');
		const arrival_date = jQuery(this).data('arrival_date');
		const departure_date = jQuery(this).data('departure_date');
		const checkin_time = jQuery(this).data('checkin_time');
		const guest_count = jQuery(this).data('guest_count');
		const addon_price = jQuery(this).data('addon_price');
		const addons_quantity = jQuery(this).data('addons_quantity');
		const reason = jQuery(this).data('reason');
		let totalPrice = '';
		if(addon_price){
			totalPrice = order_price + addon_price;
		}else{
			totalPrice = order_price;
		}
		const emailData = {
			action: 'process_refund_email',
			vendor_name,
			vendor_email,
			vendor_number,
			first_name,
			last_name,
			get_date_created,
			customer_phone,
			billing_address,
			billing_country,
			billing_email,
			order_no,
			order_password,
			order_name,
			order_price,
			noRefundPrice,
			tenDaysRefundPrice,
			afterSevenDaysRefundPrice,
			beforeSevenDaysRefundPrice,
			totalPrice,
			arrival_date,
			departure_date,
			checkin_time,
			guest_count,
			addon_price,
			addons_quantity,
			reason,
		}
		// console.log('Voucher Data:', emailData);
        sendEmail(emailData);
        // alert('Voucher refund confirmed and email sent.');
		jQuery('.voucher-detail').hide();
		jQuery('#cancel_popup .email-sucess').show();
    });

	jQuery('#bank_form').on('submit', function (e) {
		e.preventDefault();
	
		const ibanRradio = jQuery('#iban_radio').is(':checked');
		const ibanInput = jQuery('#iban').val();
		const paypalRadio = jQuery('#paypal_radio').is(':checked');
		const paypalInput = jQuery('#paypal_email').val();
		const creditcardRadio = jQuery('#creditcard_radio').is(':checked');
		const creditcardInput = jQuery('#creditcard_number').val();
		// Get the order creation date from the hidden input field
		const orderCreateDate = jQuery('#order_start_date').val(); // Format: YYYY-MM-DD
	
		// Get the original order price
		const orderPrice = parseFloat(jQuery('#order_price').val().replace('€', '')); // Remove € sign and convert to number
	
		// Convert the order creation date to a Date object
		const orderDate = new Date(orderCreateDate);
	
		// Get the current date
		const currentDate = new Date();
	
		if (orderDate < currentDate) {
			alert('Check in Date must be greater then today');
			return false;
		}
		const timeDifference = orderDate - currentDate;
		// Calculate the difference in time (milliseconds)
	
		// Convert the time difference to days
		const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24)); // Milliseconds to days
	
		// Determine refund percentage based on the days difference
		let refundPercentage = 0;
		let noRefundPrice = 0;
		let tenDaysRefundPrice = 0;
		let afterSevenDaysRefundPrice = 0;
		let beforeSevenDaysRefundPrice = 0;
		if (daysDifference < 0) {
			refundPercentage = 0; // If the current date is before the order date
			noRefundPrice = 0;
		} else if (daysDifference > 10) {
			refundPercentage = 100; // X-10 days
			tenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		} else if (daysDifference > 7 && daysDifference < 10) {
			refundPercentage = 50; // 10-7 days
			afterSevenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		} else if (daysDifference <= 7) {
			refundPercentage = 10; // 7-0 days
			beforeSevenDaysRefundPrice = (orderPrice * refundPercentage) / 100;
		}
	
		// Calculate the refund price
		const refundPrice = (orderPrice * refundPercentage) / 100;
		const new_order_price = `€${refundPrice.toFixed(2)}`;
		const formData = {
			vendor_name : jQuery('#vendor_name').val(),
			vendor_email : jQuery('#vendor_email').val(),
			vendor_number : jQuery('#vendor_number').val(),
			first_name : jQuery('#first_name').val(),
			last_name : jQuery('#last_name').val(),
			get_date_created : jQuery('#order_create_date').val(),
			customer_phone : jQuery('#customer_phone').val(),
			billing_address : jQuery('#billing_address').val(),
			billing_country : jQuery('#billing_country').val(),
			billing_email : jQuery('#billing_email').val(),
			order_no : jQuery('#new_order_no').val(),
			order_password : jQuery('#order_password').val(),
			order_name : jQuery('#order_name').val(),
			order_price : `€${refundPrice.toFixed(2)}`,
			noRefundPrice : `€${noRefundPrice.toFixed(2)}`,
			tenDaysRefundPrice : `€${tenDaysRefundPrice.toFixed(2)}`,
			afterSevenDaysRefundPrice : `€${afterSevenDaysRefundPrice.toFixed(2)}`,
			beforeSevenDaysRefundPrice : `€${beforeSevenDaysRefundPrice.toFixed(2)}`,
			arrival_date : jQuery('#order_start_date').val(),
			departure_date : jQuery('#order_end_date').val(),
			checkin_time : jQuery('#checkin_time').val(),
			guest_count : jQuery('#guest_count').val(),
			addon_price : jQuery('#addon_price').val(),
			addons_quantity : jQuery('#addons_quantity').val(),
			reason : jQuery('#cancel_reason').val(),
		}
		console.log('bank data',formData);
		if(ibanInput === '' && creditcardInput === '' && paypalInput === ''){
			alert('Das Zahlungsfeld ist leer');
		}else{
			jQuery('#payment_popup').hide();
			jQuery('#transfered_popup').show();
		}
		// Update the DOM with the calculated refund price and booking days
		jQuery('.booking-day').text(daysDifference);
		jQuery('.refund-price').text(new_order_price);
	
	
		jQuery('#confirm_bank_payment').data(formData);
	});
    // Confirm bank payment refund
    jQuery('#confirm_bank_payment').on('click', function (e) {
        e.preventDefault();

		const vendor_name = jQuery(this).data('vendor_name');
		const vendor_email = jQuery(this).data('vendor_email');
		const vendor_number = jQuery(this).data('vendor_number');
		const first_name = jQuery(this).data('first_name');
		const last_name = jQuery(this).data('last_name');
		const get_date_created = jQuery(this).data('get_date_created');
		const customer_phone = jQuery(this).data('customer_phone');
		const billing_address = jQuery(this).data('billing_address');
		const billing_country = jQuery(this).data('billing_country');
		const billing_email = jQuery(this).data('billing_email');
		const order_no = jQuery(this).data('order_no');
		const order_password = jQuery(this).data('order_password');
		const order_name = jQuery(this).data('order_name');
		const order_price = jQuery(this).data('order_price');
		const noRefundPrice = jQuery(this).data('noRefundPrice');
		const tenDaysRefundPrice = jQuery(this).data('tenDaysRefundPrice');
		const afterSevenDaysRefundPrice = jQuery(this).data('afterSevenDaysRefundPrice');
		const beforeSevenDaysRefundPrice = jQuery(this).data('beforeSevenDaysRefundPrice');
		const arrival_date = jQuery(this).data('arrival_date');
		const departure_date = jQuery(this).data('departure_date');
		const checkin_time = jQuery(this).data('checkin_time');
		const guest_count = jQuery(this).data('guest_count');
		const addon_price = jQuery(this).data('addon_price');
		const addons_quantity = jQuery(this).data('addons_quantity');
		const reason = jQuery(this).data('reason');
        const iban = jQuery('#iban').val();
        const paypalEmail = jQuery('#paypal_email').val();
        const creditCardNumber = jQuery('#creditcard_number').val();
		let totalPrice = '';

		if(addon_price){
			totalPrice = order_price + addon_price;
		}else{
			totalPrice = order_price;
		}
        let paymentDetails = '';

        if (iban) {
            paymentDetails = `IBAN: ${iban}`;
        } else if (paypalEmail) {
            paymentDetails = `PayPal Email: ${paypalEmail}`;
        } else if (creditCardNumber) {
            paymentDetails = `Credit Card Number: ${creditCardNumber}`;
        } else {
            alert('Please provide payment details.');
            return;
        }
		const emailData = {
			action: 'process_refund_email',
			paymentDetails,
			vendor_name,
			vendor_email,
			vendor_number,
			first_name,
			last_name,
			get_date_created,
			customer_phone,
			billing_address,
			billing_country,
			billing_email,
			order_no,
			order_password,
			order_name,
			order_price,
			noRefundPrice,
			tenDaysRefundPrice,
			afterSevenDaysRefundPrice,
			beforeSevenDaysRefundPrice,
			totalPrice,
			arrival_date,
			departure_date,
			checkin_time,
			guest_count,
			addon_price,
			addons_quantity,
			reason,
		}

		console.log('Email Data:', emailData);
        sendEmail(emailData);
        // alert('Bank payment refund confirmed and email sent.');
		jQuery('.payment-detail').hide();
		jQuery('#transfered_popup .email-sucess').show();
    });

    // Function to send email via AJAX
    function sendEmail(emailData) {
        jQuery.ajax({
            url: ajax_object.ajax_url, // Replace with your WordPress AJAX URL
            method: 'POST',
            data: emailData,
			beforeSend: function () {
				jQuery('.ajax-loading').show();
			},
            success: function (response) {
				jQuery('.ajax-loading').hide();
                console.log('Email sent:', response);
            },
            error: function (xhr, status, error) {
				jQuery('.ajax-loading').hide();
                console.error('Error sending email:', error);
            },
        });
    }
});