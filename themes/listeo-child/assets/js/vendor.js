jQuery(document).ready(function ($) {
	setTimeout(function(){
		$('.panel-disable').click();
	}, 500);
	
    $(document).on('click', '.edit-notification-button', function() {
        const listingId = $(this).data('listing-id');

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_notification',
                listing_id: listingId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Populate the form fields
                    $('select[name="location"]').val(listingId);
                    $('input[name="subject"]').val(data.subject);
                    $('textarea[name="message"]').val(data.message);
                    $('select[name="before_arrival"]').val(data.before_arrival);
                    $('select[name="after_departure"]').val(data.after_departure);

                    // Show the edit form modal (if using a modal)
                    // $('#edit_notification_modal').modal('show');
                } else {
                    alert(response.data.message || 'Failed to fetch notification data.');
                }
            },
            error: function() {
                alert('An error occurred while fetching notification data.');
            }
        });
    });

    $(document).on('click', '.delete-notification', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }

        const row = $(this).closest('tr');
        const listingId = $(this).data('listing-id');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_listing_notification',
                listing_id: listingId
            },
            beforeSend: function () {
                row.css('opacity', '0.5');
            },
            success: function (response) {
                if (response.success) {
                    row.fadeOut(500, function () {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || 'Failed to delete notification.');
                    row.css('opacity', '1');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                row.css('opacity', '1');
            }
        });
    });

    $(document).on('click', '.import-ical-button', function (e) {
        e.preventDefault();

        var import_name = $(this).parent().find('.import-name').val();
        var import_url = $(this).parent().find('.import-url').val();
        var listing_id = $(this).data('listing_id');

        if (!import_name) {
            alert("Error: The name field is empty.");
            return;
        }

        if (!import_url) {
            alert("Error: The URL field is empty.");
            return;
        }

        if (!listing_id) {
            alert("Error: The listing ID is missing.");
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            method: "POST",
            data: {
                action: "add_new_listing_ical",
                name: import_name,
                url: import_url,
                listing_id: listing_id,
                force_update: true
            },
            success: function (response) {
                if (response.type === "success") {
                    alert("Success: " + response.notification);
                } else {
                    var decodedMessage = response.notification.replace(/&#039;/g, "'").replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                    alert("Error: " + decodedMessage);
                }
            },
            error: function (xhr, status, error) {
                alert("AJAX Error: " + error);
            }
        });
    });

    let sortAscending = true;

    $('#sort-arrival-dropdown').click(function () {
        const table = $('.booking-table');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr').toArray();
    
        rows.sort((a, b) => {
            // Extract date strings from the third column (index 2)
            const dateA = $(a).find('td').eq(2).text().trim();
            const dateB = $(b).find('td').eq(2).text().trim();
    
            // Convert DD/MM/YYYY to YYYY-MM-DD for comparison
            const parsedDateA = new Date(dateA.split('/').reverse().join('-'));
            const parsedDateB = new Date(dateB.split('/').reverse().join('-'));
    
            // Compare the dates
            return sortAscending ? parsedDateA - parsedDateB : parsedDateB - parsedDateA;
        });
    
        // Append the sorted rows back to the table body
        tbody.append(rows);
    
        // Toggle the sorting order for the next click
        sortAscending = !sortAscending;
    });
    
    $('button[data-bs-target="#update_vendor_reg_modal"]').on('click', function () {
        var userId = $(this).data('user_id');

        $('#update_vendor_registertaion')[0].reset();

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_update_vendor_data',
                user_id: userId
            },
            success: function (response) {
                if (response.success) {
                    var data = response.data;

                    $('input[name="update_vendor_id"]').val(data.user_id);
                    $('input[name="update_vendor_name"]').val(data.name);
                    $('input[name="update_vendor_email"]').val(data.email);

                    // console.log(data.permissions);
                    if (data.permissions && Array.isArray(data.permissions)) {
                        $('input[name="update_menu_options[]"]').each(function () {
                            $(this).prop('checked', data.permissions.includes($(this).val()));
                        });
                    }
                } else {
                    // alert('Error fetching vendor data: ' + response.data.message);
                }
            },
            error: function () {
                alert('An error occurred while fetching vendor data.');
            }
        });
    });
    
    $(document).on('click', '#add-new-coupon', function () {
        $('form input, form select').val('');
        $('form input[type="hidden"]').val('');
        $('.product-checkboxes').remove();
    });

    $(document).on('click', '.edit-coupon', function () {
        const couponId = $(this).data('coupon_id');

        if (!couponId) {
            alert('Coupon ID is missing.');
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'get_coupon_data',
                coupon_id: couponId
            },
            beforeSend: function () {
                console.log('Fetching coupon data...');
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#dealName').val(data.description);
                    $('input[name="coupon_id"]').val(data.id);
                    $('#code').val(data.title);
                    $('#usageLimit').val(data.usage_limit);
                    $('#usageLimitPerUser').val(data.usage_limit_per_user);

                    $('#discountType').val(data.discount_type);
                    $('#coupon_amount').val(data.amount);
                    $('#startDate').val(data.start_date);
                    $('#endDate').val(data.expiry_date);

                    $('#bubbleTents').attr('data-voucher_ids', data.product_vouchers);
                    $('#bubbleTents').val(data.product_ids).trigger('change');

                    console.log('Form data populated successfully.');
                } else {
                    alert(response.data.message || 'Failed to fetch coupon data.');
                }
            },
            error: function () {
                alert('An error occurred while fetching coupon data.');
            }
        });
    });

    $('#search-booking, .filter').on('input change', function () {
        var searchQuery = $('#search-booking').val();
        var pageType = $('#search-booking').attr('data-page_type');
    
        var filters = {
            location: $('[data-filter="location"]').val(),
            status: $('[data-filter="status"]').val(),
            year: $('[data-filter="year"]').val(),
            month: $('[data-filter="month"]').val(),
        };
    
        var isFilterApplied = Object.values(filters).some(function(value) {
            return value && value.trim() !== '';
        });
    
        if (searchQuery.length >= 3 || searchQuery.length == 0 || isFilterApplied) {
            $('.pagination .page-numbers').hide();
            $.ajax({
                url: ajax_object.ajax_url,
                method: 'GET',
                dataType: 'html',
                data: {
                    action: 'search_bookings',
                    query: searchQuery,
                    page_type: pageType,
                    filters: filters,
                },
                success: function (response) {
                    $('#dataTable tbody').html(response);
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Error: ' + error);
                }
            });
        }
    });

    function apply_filters() {
        var year = $('#filter_year').val();
        var month = $('#filter_month').val();
        var status = $('#filter_status').val();
        var location = $('#filter_location').val();

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_coupons',
                year: year,
                month: month,
                status: status,
                location: location
            },
            success: function(response) {
                if (response.success) {
                    $('#coupons-table-body').html(response.data.html);
                } else {
                    alert('Error fetching data.');
                }
            },
            error: function() {
                alert('Request failed.');
            }
        });
    }

    $('#filter_year, #filter_month, #filter_status, #filter_location').on('change', function() {
        apply_filters();
    });

    $(document).on('change', '#bubbleTents', function () {
        const postId = $(this).val();
        const selected_vouchers = $(this).data('voucher_ids');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_product_meta',
                post_id: postId,
            },
            success: function (response) {
                if (response.success) {
                    const products = response.data;
                    $('#bubbleTents').next('.product-checkboxes').remove();
                    const checkboxContainer = $('<div class="product-checkboxes"></div>');
                    const selectedVoucherArray = selected_vouchers
                        ? (typeof selected_vouchers === 'string' && selected_vouchers.includes(',')
                            ? selected_vouchers.split(',')
                            : [selected_vouchers])
                        : [];

                    products.forEach(function (product) {
                        const productIdStr = product.id ? product.id.toString().trim() : '';
                        const selectedVoucherArrayStr = selectedVoucherArray.map(item => item.toString().trim());
                        const isChecked = selectedVoucherArrayStr.includes(productIdStr);

                        const checkbox = $(`
                        <label>
                            <input type="checkbox" name="product_vouchers[]" value="${product.id}" ${isChecked ? 'checked' : ''} />
                            ${product.name}
                        </label>
                    `);
                        checkboxContainer.append(checkbox);
                    });

                    $('#bubbleTents').after(checkboxContainer);
                } else {
                    // alert(response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                // alert('Something went wrong. Please try again.');
            }
        });
    });

    // $(document).on('click', '.close-modal', function (e) {
    //     e.preventDefault();

    //     // $('body').removeClass('modal-open');
    //     // $('#add-Coupon-Modal').removeClass('show');
    //     // $('.modal-backdrop').remove();
    //     // $('#add-Coupon-Modal').hide();
    //     $('#add-Coupon-Modal').modal('hide');
    // });

    $(document).on('click', '.delete-coupon', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this coupon?')) {
            return;
        }

        const row = $(this).closest('tr');
        const couponId = $(this).data('coupon_id');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_woocommerce_coupon',
                coupon_id: couponId
            },
            beforeSend: function () {
                row.css('opacity', '0.5');
            },
            success: function (response) {
                if (response.success) {
                    row.fadeOut(500, function () {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || 'Failed to delete coupon.');
                    row.css('opacity', '1');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                row.css('opacity', '1');
            }
        });
    });

    $('#bubble_tent').on('change', function () {
        var listingId = $(this).val();

        if (listingId) {
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_listing_meta',
                    listing_id: listingId
                },
                success: function (response) {
                    if (response.success) {
                        const normalPrice = response.data._normal_price;
                        const weekendPrice = response.data._weekday_price || normalPrice;

                        $('#regular_price').val(normalPrice);
                        $('#weekend_price').val(weekendPrice);
                        $('#availability_calendar').val(JSON.stringify(response.data._availability));
                        $('#blocked_dates_calendar').val(JSON.stringify(response.data._blocked_checkin_dates));
                        $('#external_blocked_dates_calendar').val(JSON.stringify(response.data._external_checkin_dates));
                        $('#booked_blocked_dates_calendar').val(JSON.stringify(response.data._booked_dates));
                        $(".calendar-day").removeClass("date-booked");
                        $(".calendar-day").removeClass("date-blocked");
                        $(".calendar-day").removeClass("external-blocked");
                        $('.price-input').removeAttr("disabled");
                        $('.calendar-day').find('.price-input-button').removeClass('hide');
                        var availability = response.data._availability['price'];
                        var booked_date = response?.data?._availability?.['dates'] || null;
                        var bloked_date = response?.data?._blocked_checkin_dates?.['dates'] || null;
                        // var external_block_date = response.data._external_checkin_dates['dates'];
                        var external_block_date = response?.data?._external_checkin_dates?.['dates'] || null;
                        var order_booked_dates = response?.data?._booked_dates?.['dates'] || null;
                        // var targetDate = booked_date; // The date you are looking for

                        // // Find the matching order number
                        // var bookedDatesArray = booked_date.split('|');

                        // // Check if the target date exists in the array
                        // if (bookedDatesArray.includes(targetDate)) {
                        //     console.log("Date found in the booking list:", targetDate);
                        // } else {
                        //     console.log("No matching date found for:", targetDate);
                        // }
                        
                        if (availability) {
                            availability = JSON.parse(availability);
                        } if (booked_date && typeof booked_date === 'string') {
                            let cleanedDateString = booked_date.replace('Array', '').trim();
                            var bookedDatesArray = cleanedDateString.split('|');
                        } if (bloked_date && typeof bloked_date === 'string') {
                            let cleanedBlockedDateString = bloked_date.replace('Array', '').trim();
                            var blockedDatesArray = cleanedBlockedDateString.split('|');
                        } if (external_block_date && typeof external_block_date === 'string') {
                            let cleanedExternalDateString = external_block_date.replace('Array', '').trim();
                            var externalDatesArray = cleanedExternalDateString.split('|');
                        } if (order_booked_dates && typeof order_booked_dates === 'string') {
                            let cleanedOrderBookedDateString = order_booked_dates.replace('Array', '').trim();
                            var orderBookedDatesArray = cleanedOrderBookedDateString.split('|');
                        }
                        
                        $(".price-input").each(function () {
                            // const dayNumber = $(this).closest('.calendar-day').find(".day-number").text();
                            const dayNumber = parseInt($(this).closest('.calendar-day').find(".day-number").text());
                            // const currentDate = new Date(currentYear, currentMonth, parseInt(dayNumber));
                            const currentDate = new Date(currentYear, currentMonth, dayNumber);
                            const formattedMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                            const dateString = dayNumber + '-' + formattedMonth + '-' + currentYear;
                            let isExternal = false;
                            console.log("isExternal = ", isExternal);
                            
                            // const dateData = {dateString};
                            // const calOrderid = 221960;
                            const calOrderdata = {dateString};
                            // console.log('order date:', dateString);
                            if (booked_date && typeof booked_date === 'string') {
                                if (Array.isArray(bookedDatesArray) && bookedDatesArray.length > 0 && bookedDatesArray.includes(dateString)) {
                                    $(this).closest('.price-input').attr("disabled", true);
                                    $(this).closest(".calendar-day").addClass("date-booked");
                                    $(this).closest('.date-booked').find('.calorder-modal').data(calOrderdata);
                                    $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                                    // console.log($(this).closest('.date-booked').find('.calorder-modal'));
                                }

                                if (Array.isArray(blockedDatesArray) && blockedDatesArray.length > 0 && blockedDatesArray.includes(dateString)) {
                                    // console.log('blockedDatesArray =', dateString );
                                    $(this).closest('.price-input').attr("disabled", true);
                                    $(this).closest(".calendar-day").addClass("date-blocked").removeClass('date-booked').removeClass("external-blocked");
                                    $(this).closest('.date-blocked').find('.block-modal').removeClass('hide');
                                    $(this).closest('.date-blocked').find('.calorder-modal').addClass('hide');
                                    $(this).closest('.date-blocked').find('.external-block-modal').addClass('hide');
                                    $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                                    $(this).closest('.date-blocked').find('.block-modal').data(calOrderdata);
                                    $(this).closest('.date-blocked').find('.block-modal').data({listingId});
                                    // console.log($(this).closest('.date-booked').find('.calorder-modal'));
                                }
                                // console.log("Array.isArray(externalDatesArray) = ", Array.isArray(externalDatesArray));
                                // console.log("externalDatesArray = ", externalDatesArray);
                                // console.log("dateString = ", dateString);
                                
                                if ((Array.isArray(orderBookedDatesArray) && orderBookedDatesArray.length > 0 && !orderBookedDatesArray.includes(dateString)) 
                                    && (Array.isArray(blockedDatesArray) && blockedDatesArray.length > 0 && !blockedDatesArray.includes(dateString))) {
                                    isExternal = true;
                                }

                                if (Array.isArray(externalDatesArray) && externalDatesArray.includes(dateString) && isExternal) {
                                    // console.log('externalDatesArray =', externalDatesArray );
                                    // console.log('dateString =', dateString );
                                    $(this).closest('.price-input').attr("disabled", true);
                                    $(this).closest(".calendar-day").addClass("external-blocked").removeClass('date-booked').removeClass('date-blocked');
                                    $(this).closest('.external-blocked').find('.external-block-modal').removeClass('hide');
                                    $(this).closest('.external-blocked').find('.block-modal').addClass('hide');
                                    $(this).closest('.external-blocked').find('.calorder-modal').addClass('hide');
                                    $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                                    // console.log($(this).closest('.date-booked').find('.calorder-modal'));
                                }
                            }

                            if (availability) {
                                if (availability[dateString]) {
                                    $(this).val(availability[dateString]);
                                } else {
                                    const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                                    $(this).val(isWeekend ? weekendPrice : normalPrice);
                                }
                            } else {
                                const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                                $(this).val(isWeekend ? weekendPrice : normalPrice);
                            }
                        });
                    } else {
                        console.error(response.data.message || 'Failed to fetch data');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    });

    $('#to_date_price').on('change', function () {
        const toDateValue = $(this).val();
        if (toDateValue) {
            $('#from_date_price').attr('min', toDateValue);
        } else {
            $('#from_date_price').removeAttr('min');
        }
    });

    $('#from_date_price').on('change', function () {
        const fromDateValue = $(this).val();
        if (fromDateValue) {
            $('#to_date_price').attr('max', fromDateValue);
        } else {
            $('#to_date_price').removeAttr('max');
        }
    });

    $(document).on('click', '.prev, .next', function () {
        const normalPrice = $('#regular_price').val();
        const weekendPrice = $('#weekend_price').val();
        var availability_calendar = JSON.parse($('#availability_calendar').val());
        var blocked_dates_calendar = JSON.parse($('#blocked_dates_calendar').val());
        var external_blocked_dates_calendar = JSON.parse($('#external_blocked_dates_calendar').val());
        var order_booked_dates = JSON.parse($('#booked_blocked_dates_calendar').val());
        var listingId = $('#bubble_tent').val();

        availability = availability_calendar['price'];
        var booked_date = availability_calendar['dates'];
        var blocked_dates = blocked_dates_calendar['dates'];
        var external_blocked_dates = external_blocked_dates_calendar['dates'];
        var booked_blocked_dates = order_booked_dates['dates'];

        if (availability) {
            availability = JSON.parse(availability);
        } if (booked_date && typeof booked_date === 'string') {
            let cleanedDateString = booked_date.replace('Array', '').trim();
            var bookedDatesArray = cleanedDateString.split('|');
        } if (blocked_dates && typeof blocked_dates === 'string') {
            let cleanedBlockedDateString = blocked_dates.replace('Array', '').trim();
            var blockedDatesArray = cleanedBlockedDateString.split('|');
        } if (external_blocked_dates && typeof external_blocked_dates === 'string') {
            let cleanedExternalBlockedDateString = external_blocked_dates.replace('Array', '').trim();
            var externalBlockedDatesArray = cleanedExternalBlockedDateString.split('|');
        } if (booked_blocked_dates && typeof booked_blocked_dates === 'string') {
            let cleanedOrderBlockedDateString = booked_blocked_dates.replace('Array', '').trim();
            var orderBlockedDatesArray = cleanedOrderBlockedDateString.split('|');
        }
        
        $(".price-input").each(function () {
            const dayNumber = parseInt($(this).closest('.calendar-day').find(".day-number").text());
            const currentDate = new Date(currentYear, currentMonth, dayNumber);
            const formattedMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
            const dateString = dayNumber + '-' + formattedMonth + '-' + currentYear;
            let isExternal = false;
            const calOrderdata = {dateString};

            if (booked_date && typeof booked_date === 'string') {
                //if (Array.isArray(bookedDatesArray) && bookedDatesArray.length > 0 && bookedDatesArray.includes(dateString)) {
                    if(Array.isArray(bookedDatesArray) && bookedDatesArray.length > 0 && bookedDatesArray.includes(dateString))  {
                        
                        $(this).closest(".calendar-day").addClass("date-booked").removeClass("external-blocked").removeClass("date-blocked");
                        $(this).closest('.calendar-day').find('.calorder-modal').removeClass('hide');
                        $(this).closest('.calendar-day').find('.block-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.external-block-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                        $(this).closest('.date-booked').find('.calorder-modal').data(calOrderdata);
                        // $(this).closest('.price-input-button').hide();
                        // $(this).closest('.calorder-modal').show();
                        $(this).closest('.price-input').attr("disabled", true);
                    }
                    
                    if (Array.isArray(blockedDatesArray) && blockedDatesArray.length > 0 && blockedDatesArray.includes(dateString)) {
                        $(this).closest(".calendar-day").addClass("date-blocked").removeClass("date-booked").removeClass("external-blocked");
                        $(this).closest('.calendar-day').find('.block-modal').removeClass('hide');
                        $(this).closest('.calendar-day').find('.calorder-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.external-block-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                        $(this).closest('.date-blocked').find('.block-modal').data({dateString});
                        $(this).closest('.date-blocked').find('.block-modal').data({listingId});
                        $(this).closest('.price-input').attr("disabled", true);
                        $(this).closest('.date-blocked').find('.block-modal').data(calOrderdata);
                    }
                    if ((Array.isArray(orderBlockedDatesArray) && orderBlockedDatesArray.length > 0 && !orderBlockedDatesArray.includes(dateString)) 
                        && (Array.isArray(blockedDatesArray) && blockedDatesArray.length > 0 && !blockedDatesArray.includes(dateString))) {
                        isExternal = true;
                    }

                    if(Array.isArray(externalBlockedDatesArray) && externalBlockedDatesArray.length > 0 && externalBlockedDatesArray.includes(dateString) && isExternal) {
                        // console.log('externalBlockedDatesArray -> ', dateString);
                        $(this).closest(".calendar-day").addClass("external-blocked").removeClass("date-booked").removeClass("date-blocked");
                        $(this).closest('.calendar-day').find('.external-block-modal').removeClass('hide');
                        $(this).closest('.calendar-day').find('.block-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.calorder-modal').addClass('hide');
                        $(this).closest('.calendar-day').find('.price-input-button').addClass('hide');
                        $(this).closest('.price-input').attr("disabled", true);
                    }
                //}
            }

            if (availability) {
                if (availability[dateString]) {
                    $(this).val(availability[dateString]);
                } else {
                    const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                    $(this).val(isWeekend ? weekendPrice : normalPrice);
                }
            } else {
                const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                $(this).val(isWeekend ? weekendPrice : normalPrice);
            }
        });
    });

    $(document).on('click', '.price-input-button', function () {
        var listingId = $('#bubble_tent').val();

        if (listingId) {
            var priceData = {};
            var clickedDay = $(this).closest(".calendar-day");
            var dayNumber = clickedDay.find(".day-number").text();
            var priceInput = clickedDay.find(".price-input");

            if (priceInput.val()) {
                var currentDate = new Date(currentYear, currentMonth, dayNumber);
                var formattedMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                var dateString = dayNumber + '-' + formattedMonth + '-' + currentYear;

                priceData[dateString] = priceInput.val();
            }

            var data = {
                action: 'save_calendar_specific_prices',
                listing_id: listingId,
                availability_data: priceData
            };

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.success) {
                        alert('Price set successfully!');
                    } else {
                        alert('Failed to set price');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    });

    const currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    const calendarBody = document.getElementById("calendar-body");
    const currentMonthSpan = document.getElementById("currentMonth");
    const currentYearSpan = document.getElementById("currentYear");

    const months = [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];

    function updateCalendar() {
        calendarBody.innerHTML = "";
        const firstDay = (new Date(currentYear, currentMonth, 1).getDay() || 7) - 1;
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        let dayCount = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement("tr");
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement("td");
                if (i === 0 && j < firstDay) {
                    cell.classList.add("empty-cell");
                } else if (dayCount <= daysInMonth) {
                    cell.classList.add("calendar-day");
                    cell.innerHTML = `
                    <span class="day-number">${dayCount}</span>
                    <input type="text" class="price-input">
                    <input type="submit" class="price-input-button" value="Set price">
                    <button class="calorder-modal" data-bs-toggle="modal" data-bs-target="#viewcalorder_modal">Show Detail</button>
                    <button class="block-modal hide" data-bs-toggle="modal" data-bs-target="#viewblockdate_modal">Un Block</button>
                    <button class="external-block-modal hide">External</button>
                `;
                    dayCount++;
                } else {
                    cell.classList.add("empty-cell");
                }
                row.appendChild(cell);
            }
            calendarBody.appendChild(row);
        }
        currentMonthSpan.textContent = months[currentMonth];
        currentYearSpan.textContent = currentYear;
    }

    const prevButton = document.querySelector(".prev");
    const nextButton = document.querySelector(".next");

    if (prevButton) {
        prevButton.addEventListener("click", () => {
            if (currentMonth === 0) {
                currentMonth = 11;
                currentYear--;
            } else {
                currentMonth--;
            }
            updateCalendar();
        });
    }

    if (nextButton) {
        nextButton.addEventListener("click", () => {
            if (currentMonth === 11) {
                currentMonth = 0;
                currentYear++;
            } else {
                currentMonth++;
            }
            updateCalendar();
        });
    }

    if (calendarBody) {
        updateCalendar();
    }
    $(document).on('click', '.calorder-modal', function(e) {
        e.preventDefault();

        const calOrderid = $(this).data();

        $('#viewcalorder_modal .modal-body').html('<p>Loading...</p>');

        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                action: 'get_calorder_details',
                calorder_id: calOrderid       
            },
            success: function(response) {
                if (response.success) {
                    
                    $('#viewcalorder_modal .modal-body').html(response.data.html);
                } else {
                    
                    $('#viewcalorder_modal .modal-body').html('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $('#viewcalorder_modal .modal-body').html('<p>Something went wrong. Please try again.</p>');
            }
        });
    });

    $('.copyBtn').on('click', function() {
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val($(this).text().trim()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Text copied to clipboard!');
    });


    $("body").on("click", "a.ical-remove-dash", function (e) {
        e.preventDefault();
        var $this = $(this),
          index = $(this).data("remove"),
          nonce = $(this).data("nonce");
        var listing_id = $(this).data("listing-id");
        $this.parents(".saved-icals").addClass("loading");
    
        $.ajax({
          type: "POST",
          dataType: "json",
          url: ajaxurl,
          data: {
            action: "add_remove_listing_ical",
            index: index,
            listing_id: listing_id,
            //'nonce': nonce
          },
          success: function (data) {
            if (data.type == "success") {
              $this
                .parents(".saved-icals")
                .removeClass("loading")
                .html(data.output);
            }
            // $(".ical-import-dialog .notification").show().html(data.notification);
            $('.popup-ical-content-'+listing_id+' .saved-icals ul').html('<li>'+data.notification+'</li>');
          },
        });
      });
    
      $("body").on("click", "a.update-all-icals-dash", function (e) {
        e.preventDefault();
        var $this = $(this),
          listing_id = $(this).data("listing-id");
        $this.addClass("loading");
        $this.find(".update-all-icals-text").hide();
        $this.find(".update-all-icals-loading").show();
        $.ajax({
          type: "POST",
          dataType: "json",
          url: ajaxurl,
          data: {
            action: "refresh_listing_import_ical",
            listing_id: listing_id,
            //'nonce': nonce
          },
          success: function (data) {
            $this.removeClass("loading");
            $this.find(".update-all-icals-text").show();
            $this.find(".update-all-icals-loading").hide();
            if (data.type == "success") {
              $(".ical-import-dialog .notification")
                .removeClass("error notice")
                .addClass("success")
                .show()
                .html(data.notification);
                alert(data.notification);
            } else if (data.type == "error") {
              $(".ical-import-dialog .notification")
                .removeClass("success notice")
                .addClass("error")
                .show()
                .html(data.notification);
                alert(data.notification);
            }
          },
        });
    });

   
    $(document).on('shown.bs.modal', '#ical-imports-popup', function (event) {
        console.log('Modal shown:', this);
        var button = $(event.relatedTarget);
        var dataId = button.data('listing_id');
        var html = $('.popup-ical-content-'+dataId).html();
        $('#ical-imports-popup .modal-content').html(html);
        
        // console.log('Modal shown:', $('body > *').not(`#${this.id}, .modal-backdrop, #wrapper`));
        // $('body > *').not(`#${this.id}, .modal-backdrop`).attr('inert', 'true');
    });

    $(document).on('hidden.bs.modal', '[id^=ical-imports-popup-]', function () {
        console.log('Modal hidden:', this.id);
        // $('body > *').removeAttr('inert');
    });
    // Automatically close any open modal before opening another
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
        trigger.addEventListener('click', function () {
            let openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                bootstrap.Modal.getInstance(modal)?.hide();
            });
        });
    });

    $(document).on('shown.bs.modal', '#viewblockdate_modal', function (event) {
        var button = $(event.relatedTarget);
        var dataId = button.data('listingId');
        var date = button.data('dateString');
        $('.delete-slot-id').val(dataId);
        $('.delete-slot-date').val(date);
        $('#viewblockdate_modal .modal-body').find('.unblock_text').show();
        $('#viewblockdate_modal .modal-body').find('.unblock_text_success').hide();
    });

    $(document).on('click', '#confirmDelete', function (e) {
        console.log("this = ", $(this));
        
        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                action: 'get_unblock_date',
                listingId: $(this).closest('.modal-content').find('.delete-slot-id').val(),     
                date: $(this).closest('.modal-content').find('.delete-slot-date').val()     
            },
            success: function(response) {
                if (response.success) {
                    $('#viewblockdate_modal .modal-body').find('.unblock_text').hide();
                    $('#viewblockdate_modal .modal-body').find('.unblock_text_success').show();
                    $('#bubble_tent').trigger('change');
                    setTimeout(function(){
                        $('#viewblockdate_modal').modal('hide');
                    }, 1000);

                } else {
                    $('#viewblockdate_modal .modal-body').html('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $('#viewblockdate_modal .modal-body').html('<p>Something went wrong. Please try again.</p>');
            }
        });
    });

});
