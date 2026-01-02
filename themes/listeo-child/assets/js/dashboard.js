jQuery(document).ready(function ($) {
  if (
    !$("body").hasClass("parent-pageid-98") &&
    !$("body").hasClass("page-id-98")
  ) {
    return; // Exit if the class is not present
  }
  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on("click", function (e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $(".sidebar .collapse").collapse("hide");
    }
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function () {
    if ($(window).width() < 768) {
      $(".sidebar .collapse").collapse("hide");
    }

    // Toggle the side navigation when window is resized below 480px
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
      $(".sidebar .collapse").collapse("hide");
    }
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation is hovered over
  $("body.fixed-nav .sidebar").on(
    "mousewheel DOMMouseScroll wheel",
    function (e) {
      if ($(window).width() > 768) {
        var e0 = e.originalEvent,
          delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
      }
    }
  );

  // Scroll to top button appear
  $(document).on("scroll", function () {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $(".scroll-to-top").fadeIn();
    } else {
      $(".scroll-to-top").fadeOut();
    }
  });

  //var blockedDates = ["02/10/2025", "02/14/2025", "02/20/2025"];
  var blockedDates = [];

  // function isBlocked(date) {
  //   var formattedDate = $.datepicker.formatDate('dd-mm-yy', date);//01-03-2025
  //   return blockedDates.includes(formattedDate);
  // }
  function isBlocked(date) {
    try {
        if (!date || !(date instanceof Date)) {
            console.error('Invalid date provided to isBlocked:', date);
            return false;
        }

        if (!Array.isArray(blockedDates) || blockedDates.length === 0) {
            console.warn('blockedDates is empty or not an array.');
            return false;
        }

        var formattedDate = $.datepicker.formatDate('d-mm-yy', date);
        return blockedDates.includes(formattedDate);
    } catch (error) {
        console.error('Error in isBlocked function:', error);
        return false;
    }
}
var originalBlockedDates = [];

  $("#smoobu_calendar_start").datepicker({
    beforeShowDay: function(date) {
      var formattedDate = $.datepicker.formatDate('d-mm-yy', date);
      
      return [!originalBlockedDates.includes(formattedDate)];
    },
    
    dateFormat: "dd-mm-yy",
    minDate: 0,
    onSelect: function(dateText, inst) {
      let selectedDate = $(this).datepicker('getDate');
      let checkinFormatted = $.datepicker.formatDate('d-mm-yy', selectedDate);

      // Store this globally to use in end datepicker's beforeShowDay
      window.selectedCheckinDate = selectedDate;

      // Set min date for checkout as next day
      let nextDay = new Date(selectedDate);
      nextDay.setDate(nextDay.getDate() + 1);
      $("#smoobu_calendar_end").datepicker("option", "minDate", nextDay);

      // Auto show end calendar
      setTimeout(() => {
          $("#smoobu_calendar_end").focus().datepicker("show");
      }, 200);

      // Refresh both calendars
      $("#smoobu_calendar_start").datepicker("refresh");
      $("#smoobu_calendar_end").datepicker("refresh");
    }
  
  });

  $("#smoobu_calendar_end").datepicker({ 
    // beforeShowDay: function(date) {
    //   if (!window.selectedCheckinDate) return [false];
  
    //   let checkin = new Date(window.selectedCheckinDate);
    //   checkin.setHours(0, 0, 0, 0);
    //   let current = new Date(date);
    //   current.setHours(0, 0, 0, 0);
  
    //   // Don't allow same as check-in
    //   if (current.getTime() === checkin.getTime()) return [false];
  
    //   // Don't allow dates before check-in
    //   if (current < checkin) return [false];
  
    //   let formatted = $.datepicker.formatDate('d-mm-yy', current);
  
    //   // Check if next day after check-in is blocked
    //   let nextDay = new Date(checkin);
    //   nextDay.setDate(nextDay.getDate() + 1);
    //   let nextFormatted = $.datepicker.formatDate('d-mm-yy', nextDay);
  
    //   if (blockedDates.includes(nextFormatted)) {
    //       // Only allow checkout on the next blocked day
    //       return current.getTime() === nextDay.getTime() ? [true] : [false];
    //   }
  
    //   // Next day is available → allow dates until the first blocked date after check-in
    //   for (let i = 1; i <= 180; i++) {
    //       let loopDate = new Date(checkin);
    //       loopDate.setDate(loopDate.getDate() + i);
    //       let loopFormatted = $.datepicker.formatDate('d-mm-yy', loopDate);
  
    //       // Found the first blocked date
    //       if (blockedDates.includes(loopFormatted)) {
    //           // Allow checkout until the first blocked date
    //           return current <= loopDate ? [true] : [false];
    //       }
    //   }
  
    //   // No blocked date found in range → allow all
    //   return [true];
    // },  
    beforeShowDay: function(date) {
      if (!window.selectedCheckinDate) return [false];
    
      // Ensure blockedDates is always a valid array
      if (!Array.isArray(blockedDates)) {
        blockedDates = [];
      }
    
      let checkin = new Date(window.selectedCheckinDate);
      checkin.setHours(0, 0, 0, 0);
      let current = new Date(date);
      current.setHours(0, 0, 0, 0);
    
      // Don't allow same as check-in
      if (current.getTime() === checkin.getTime()) return [false];
    
      // Don't allow dates before check-in
      if (current < checkin) return [false];
    
      let formatted = $.datepicker.formatDate('d-mm-yy', current);
    
      // Check if next day after check-in is blocked
      let nextDay = new Date(checkin);
      nextDay.setDate(nextDay.getDate() + 1);
      let nextFormatted = $.datepicker.formatDate('d-mm-yy', nextDay);
    
      if (blockedDates.includes(nextFormatted)) {
        // Only allow checkout on the next day
        return current.getTime() === nextDay.getTime() ? [true] : [false];
      }
    
      // Allow dates until the first blocked date after check-in
      for (let i = 1; i <= 180; i++) {
        let loopDate = new Date(checkin);
        loopDate.setDate(loopDate.getDate() + i);
        let loopFormatted = $.datepicker.formatDate('d-mm-yy', loopDate);
    
        if (blockedDates.includes(loopFormatted)) {
          return current <= loopDate ? [true] : [false];
        }
      }
    
      // No blocked date found, allow all
      return [true];
    },    
    dateFormat: "dd-mm-yy",
    minDate: 0
  });

  $(document).on("click", "a.scroll-to-top", function (e) {
    var $anchor = $(this);
    $("html, body")
      .stop()
      .animate(
        {
          scrollTop: $($anchor.attr("href")).offset().top,
        },
        1000,
        "easeInOutExpo"
      );
    e.preventDefault();
  });

  // $(document).on('click', '#filter-dropdown', function () {
  //   $('.filter-btn-dd').toggle();
  // });
  $(document).on("click", ".files-intro", function () {
    $(this).closest(".file").find(".files-details").slideToggle(300); // Smooth toggle
  });
  // $(document).on('click', '.month-header', function (){
  //   $(this).next('.month-content').slideToggle();
  // });
  $(document).on("click", ".toggle-icon", function () {
    $(this).closest(".month-details").find(".month-content").slideToggle();
  });
  $(".month-details").each(function (i, element) {
    const totalCommision = $(element)
      .find('input[name="totalMonthlyCommisionVendor"]')
      .val();
    $(element).find(".total-commission").text(totalCommision);
  });
  // $('.pdf-btn').each(function () {
  //     $(this).on('click', function () {
  //         const monthYear = $(this).attr('id').replace('pdf-btn-', '');
  //         const tableContent = $(`#month-section-${monthYear} table`).prop('outerHTML');

  //         const { jsPDF } = window.jspdf;
  //         const doc = new jsPDF();

  //         // Add Month Title
  //         doc.setFontSize(18);
  //         doc.text('Bookings for' + monthYear, 20, 20);

  //         // Add the table content
  //         doc.autoTable({ html: tableContent });

  //         // Save PDF
  //         doc.save(`Orders-${monthYear}.pdf`);
  //     });
  // });
  $("#fyear").on("change", function () {
    $('#year_form').submit();
  });

  // const selectedYear = $(this).val(); // Get the selected year
  // // Change the button text based on the selected year
  // if (selectedYear) {
  //   $("#downallorders").text(`Download all ${selectedYear} statements`);
  // }

  $("#downallorders").on("click", function (e) {
    e.preventDefault();
    const selectedYear = $("#fyear").val(); // Get selected year
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    let isFirstPage = true; // Flag to check if it's the first page

    // Loop through all month sections
    $(".month-section").each(function () {
      const monthYear = $(this).attr("id").replace("month-section-", "");
      const month = monthYear.split("-")[1]; // Extract month
      const year = monthYear.split("-")[0]; // Extract year

      // If the selected year matches or if "all orders" is clicked
      if (selectedYear === year || selectedYear === "all") {
        const monthTitle = $(this).find(".month-name").text();

        // Extract table data manually
        const tableData = [];
        $(this)
          .find("table tbody tr")
          .each(function () {
            const row = [];
            $(this)
              .find("td")
              .each(function () {
                row.push($(this).text().trim()); // Extract text content of each cell
              });
            tableData.push(row);
          });

        if (isFirstPage) {
          // Add the month title to the first page
          doc.setFontSize(18);
          doc.text("Bookings for " + monthTitle, 20, 20);

          // Add the table content to the first page
          doc.autoTable({
            head: [
              [
                "Booking n°",
                "Type",
                "Guest Name",
                "Check-in",
                "Check-out",
                "Amount",
                "Commission",
                "Total",
              ],
            ], // Column headers
            body: tableData, // Table rows
          });

          isFirstPage = false; // Set flag to false after first page
        } else {
          // Add a new page for subsequent months
          doc.addPage();
          doc.setFontSize(18);
          doc.text("Bookings for " + monthTitle, 20, 20);

          // Add the table content to the new page
          doc.autoTable({
            head: [
              [
                "Booking n°",
                "Type",
                "Guest Name",
                "Check-in",
                "Check-out",
                "Amount",
                "Commission",
                "Total",
              ],
            ], // Column headers
            body: tableData, // Table rows
          });
        }
      }
    });

    // Save the PDF
    doc.save(`Orders-${selectedYear}.pdf`);
  });

  $(".pdf-btn").each(function () {
    $(this).on("click", function () {
      const monthYear = $(this).attr("id").replace("pdf-btn-", "");

      // Get the table and clean it by removing non-text elements like <span>
      const tableContent = $(`#month-section-${monthYear} table`).clone();

      // Remove all <span> elements from the table (you can add more if needed)
      tableContent.find("span").each(function () {
        $(this).replaceWith($(this).text());
      });

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      // Add Month Title
      doc.setFontSize(18);
      doc.text("Bookings for " + monthYear, 20, 20);

      // Add the table content
      doc.autoTable({ html: tableContent[0] }); // Pass the cleaned-up table

      // Save PDF
      doc.save(`Orders-${monthYear}.pdf`);
    });
  });
  $(".days_selections").checkboxradio();
  function initSelect2() {
    $(".multi_select").select2({
      tags: true,
      tokenSeparators: [",", " "],
    });
  }
  initSelect2();
  $(".add-row").click(function () {
    var $clone = $(this).parent(".repeater-fields").find(".repeat-row").first().clone();
    $clone.append("<button type='button' class='remove-row'>-</button>");
    $clone.insertBefore(".add-row");
  });

  $(".repeater-fields").on("click", ".remove-row", function () {
    $(this).parent().remove();
  });
  $(".add-row-2").click(function () {
    var $clone = $(this).parent(".repeater-field-2").find(".repeat-row-2").first().clone();
    $clone.append("<button type='button' class='remove-row'>-</button>");
    $clone.insertBefore(".add-row-2");
  });

  $(".repeater-field-2").on("click", ".remove-row", function () {
    $(this).parent().remove();
  });
  $(".add-row-3").click(function () {
    var $clone = $(this).parent(".repeater-field-3").find(".repeat-row-3").first().clone();
    $clone.append("<button type='button' class='remove-row'>-</button>");
    $clone.insertBefore(".add-row-3");
  });

  $(".repeater-field-3").on("click", ".remove-row", function () {
    $(this).parent().remove();
  });
  $(".add-row-4").click(function () {
    var $clone = $(this).parent(".repeater-field-4").find(".repeat-row-4").first().clone();
    $clone.append("<button type='button' class='remove-row'>-</button>");
    $clone.insertBefore(".add-row-4");
  });

  $(".repeater-field-4").on("click", ".remove-row", function () {
    $(this).parent().remove();
  });

  $('#bubble_tent_listing').on('change', function () {
    const listingId = $(this).val();

    if (listingId) {
      $.ajax({
        url: ajaxurl, // WordPress Ajax URL
        type: 'POST',
        data: {
          action: 'fetch_addons',
          listing_id: listingId,
        },
        beforeSend: function () {
          $('#package_items').html('<p>Loading addons...</p>');
          $('#_number_of_adults').html('<option>Loading...</option>');
          $('#_number_of_kids').html('<option>Loading...</option>');
          $('#smoobu_calendar_start').prop('disabled', true).attr('placeholder', 'Loading please wait...'); 
          $('#smoobu_calendar_end').prop('disabled', true).attr('placeholder', 'Loading please wait...');
        },
        success: function (response) {
          if (response.success) {
            blockedDates = JSON.parse(response.data.blocked_dates);
            if(blockedDates){
              originalBlockedDates = [...blockedDates];
            }
            // Populate addons
            $('#package_items').html(response.data.addons_html);

            // Populate adult and children select options
            $('#_number_of_adults').html(response.data.adults_options);
            $('#_number_of_kids').html(response.data.kids_options);
            $('#discount_codes').html(response.data.coupons_options);
            $('#smoobu_calendar_start').prop('disabled', false).attr('placeholder', 'Checkin'); 
            $('#smoobu_calendar_end').prop('disabled', false).attr('placeholder', 'CheckOut');
          } else {
            $('#package_items').html('<p>No addons available.</p>');
          }
        },
        error: function () {
          $('#package_items').html('<p>Failed to load addons. Please try again.</p>');
        },
      });
    } else {
      $('#package_items').html('');
      $('#_number_of_adults').html('');
      $('#_number_of_kids').html('');
    }
  });

  // getting current date
  const today = new Date().toISOString().split('T')[0];
  $('#current_date').val(today);

  $('#smoobu_calendar_star, #smoobu_calendar_end, #_number_of_adults, #_number_of_kids, #discount_codes').on('change', function () {
    const property_id = $('#bubble_tent_listing').val();
    const security = $('#security').val();
    var smoobu_calendar_start = $('#smoobu_calendar_start').val();
    var smoobu_calendar_end = $('#smoobu_calendar_end').val();
    var number_of_adults = $('#_number_of_adults').val();
    var number_of_kids = $('#_number_of_kids').val();
    var discount_codes = $('#discount_codes').val();
    if (smoobu_calendar_start != null && smoobu_calendar_end != null) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'get_average_price_vdash',
          security: security,
          property_id: property_id,
          star_date: smoobu_calendar_start,
          end_date: smoobu_calendar_end,
          number_of_adults: number_of_adults,
          number_of_kids: number_of_kids,
          discount_codes: discount_codes,
        },
        beforeSend: function () {
          $('').html('<p>Loading Dates...</p>');
        },
        success: function (response) {
          if (response.success) {
            let price = response.data.averagePrice;
            let discount = 0;
            let qty = response.data.nights;

            if ((response.data.discount).length != 0) {
              if (response.data.discount.type == "fixed_cart") {
                discount = response.data.discount.amount;
              } else {
                discount = (price * response.data.discount.amount) / 100;
                discount = discount * qty;
              }
            }
            let symbol = response.data.currencySymbol;
            let guestFee = response.data.guestFee;
            let totalPrice = (price + guestFee) * qty;
            totalPrice = totalPrice - discount;


            $('#nights').val(qty);
            jQuery('#total_amount').val(totalPrice + ' ' + symbol);
            jQuery('#product_price').val(price);
            jQuery('#product_price_guest').val(guestFee);
            jQuery('#product_qty').val(qty);
            jQuery(document).on('change', '.addon_checkbox', function (e) {
              e.preventDefault();
              let total = parseInt(totalPrice) + parseInt(discount);
              let pkgName = $('.pkg-name').text();
              let pkgPrice = $('.pkg-price').text();
              let _number_of_adults = $('#_number_of_adults').val();
              let _number_of_kids = $('#_number_of_kids').val();
              let price_per_person = 0;
              let price_per_night = 0;
              let price_per_kid = 0;
              jQuery('.addon_checkbox:checked').each(function () {
                let is_per_person = $(this).closest('.package-item').find('.is_per_person').val(); 
                let include_kids = $(this).closest('.package-item').find('.include_kids').val();
                let is_per_night = $(this).closest('.package-item').find('.is_per_night').val();
                let adon_price = parseFloat($(this).val());
                //total += parseFloat(jQuery(this).val());
                jQuery('#addon_name').val(pkgName);
                if (is_per_person) {
                  price_per_person = (_number_of_adults > 1) ? _number_of_adults : 0;
                }
                if (include_kids) {
                  price_per_kid = (_number_of_kids > 1) ? _number_of_kids : 0;
                }
                if (is_per_night) {
                  price_per_night = (qty > 1) ? qty : 0;
                }
                let extraAdonPrice = parseInt(price_per_person) + parseInt(price_per_kid) + parseInt(price_per_night);
                
                pkgPrice = (extraAdonPrice) ? adon_price * extraAdonPrice : adon_price;
                total += parseFloat(pkgPrice);
                jQuery('#pkg_price').val(pkgPrice);
              });
              total = total - parseInt(discount);
              jQuery('#total_amount').val(total + ' ' + symbol);
            });
            $('.addon_checkbox').trigger('change');

          } else {
            $('').html('<p>No Dates available.</p>');
          }
        },
        error: function () {
          $('').html('<p>Failed to load Dates. Please try again.</p>');
        },
      });
    } else {
      $('').html('');
    }
  });
  // $('#booking_form').on('submit', function (e) {
  //   e.preventDefault();  // Prevent the default form submission

  //   // Collect form data
  //   const formData = {
  //     action: 'create_booking_order',
  //     data: $(this).serialize(),
  //   };

  //   // Make AJAX request
  //   $.ajax({
  //     url: ajaxurl,
  //     method: 'POST',
  //     data: formData,
  //     beforeSend: function () {
  //       $('.submit_msg_bf').show();
  //     },
  //     success: function (response) {
  //       if (response.success) {
  //         console.log('Booking successfully created.');
  //         $('.submit_msg_bf').text('Booking successfully created');
  //         $('#booking-details-Modal').hide();
  //       } else {
  //         console.log('Error: ' + response.data);
  //         $('.submit_msg_bf').hide();
  //       }
  //     }
  //   });
  //   // return false; // Prevent the default form submission
  // });
  $('#booking_form').on('submit', function (e) {
    e.preventDefault();  // Prevent the default form submission
    $('.crt-book').hide();
    $(".direct_book").hide();
    $(".block_date").hide();
    $(".hide-col").hide();
    $('.confirm-book').show();
    $('.confirm-txt').show();
  });
  $('.confirm-book').on('click', function (e) {
    e.preventDefault();  // Prevent the default form submission
    var selectedValue = $("#bubble_tent_listing_type").val();
    // Collect form data
    const formData = {
      action: 'create_booking_order',
      data: $('#booking_form').serialize(),
    };

    // Make AJAX request
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: formData,
      beforeSend: function () {
        $('.submit_msg_bf').show();
      },
      success: function (response) {
        if (response.success) {
          console.log('Booking successfully created.');
          $('.submit_msg_bf').text(response.data.message);
          if (selectedValue === "Block date") {
            $('.confirm-txt h2').text('Dates blocked successfully.');
          } else {
            $('.confirm-txt h2').text('Youre booking is confirmed');
          }
          //$('#booking-details-Modal').removeClass('show');
          setTimeout(function() {
            $('#booking-details-Modal').modal('hide');
            window.location.href = "/dashboard/booking-and-stornos/";
          }, 500);
          
        } else {
          console.log('Error: ' + response.data);
          $('.submit_msg_bf').hide();
        }
      }
    });
    // return false; // Prevent the default form submission
  });
  $(".personal-form").on("submit", function (e) {
    e.preventDefault();
    var personalformData = $(this).serialize();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'update_user_personal_info',
        form_data_pi: personalformData,
      },
      beforeSend: function () {
        $('.submit_msg_pi').show();
      },
      success: function (response) {
        if (response.success) {
          $(".success-message-pi").text('Your information has been updated successfully.').show();
          $(".error-message-pi").hide();
          $('.submit_msg_pi').hide();
        } else {
          $(".error-message-pi").text('There was an error updating your information.').show();
          $(".success-message-pi").hide();
          $('.submit_msg_pi').hide();
        }
      },
      error: function () {
        $(".error-message-pi").text('There was an error with the request.').show();
        $(".success-message-pi").hide();
      }
    });
  });
  $('.company-form').on('submit', function (e) {
    e.preventDefault();

    var companyformData = $('.company-form').serialize();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'update_user_company_info',
        form_data_ci: companyformData,
      },
      beforeSend: function () {
        $('.submit_msg_ci').show();
      },
      success: function (response) {
        if (response.success) {
          $('.success-message-ci').text(response.data.message).show();
          $('.error-message-ci').hide();
          $('.submit_msg_ci').hide();
        } else {
          $('.error-message-ci').text(response.data.message).show();
          $('.success-message-ci').hide();
          $('.submit_msg_ci').hide();
        }
      },
      error: function () {
        $('.error-message-ci').text('An unexpected error occurred. Please try again.').show();
        $('.success-message-ci').hide();
      }
    });
  });
  var listingTypeSelectedValue = $("#bubble_tent_listing_type").val();
  if (listingTypeSelectedValue === "Select") {
    $(".direct_book").hide();
    $(".block_date").hide();
    $(".crt-book").hide();
    $(".discard-book").hide();

  };
  $("#bubble_tent_listing_type").on("change", function () {
    var selectedValue = $(this).val();
        
    if (selectedValue === "Select") {
      $(".direct_book").hide();
      $(".block_date").hide();
      $(".booking-submit-btn").hide();
      $(".crt-book").hide();
      $(".discard-book").hide();

    } else if (selectedValue === "Block date") {
      $(".booking-submit-btn").show();
      $(".direct_book").hide();
      $(".block_date").show();
      $(".block_date").addClass('full-width');
      $(".crt-book").show();
      $(".discard-book").show();
      $(".crt-book").text('Block Dates');
      $(".confirm-book").text('Block Dates');
      $(".confirm-txt h2").text("Could you please confirm if you'd like to block this date?");

    } else {
      $(".booking-submit-btn").show();
      $(".direct_book").show();
      $(".block_date").show();
      $(".block_date").removeClass('full-width');
      $(".crt-book").show();
      $(".discard-book").show();
      $(".crt-book").text('Create booking');
      $(".confirm-book").text('Confirm booking');
      $(".confirm-txt h2").text("Please confirm you're booking");
    }
  });
  let isEditing = false;

  // Edit button functionality
  // $('#edit-guest').on('click', function (e) {
  //   e.preventDefault();
  //   isEditing = !isEditing;

  //   if (isEditing) {
  //     $('input, textarea').not('#guest-email').prop('disabled', false);
  //     $(this).text('Cancel Edit');
  //   } else {
  //     $('input, textarea').not('#guest-email').prop('disabled', true);
  //     $(this).text('Edit');
  //   }
  // });
  $('#edit-guest').on('click', function (e) {
    e.preventDefault();
    isEditing = !isEditing;

    if (isEditing) {
      $('input, textarea').not('#guest-email').prop('disabled', false);
      $(this).text('Cancel Edit');
    } else {
      $('input, textarea').not('#guest-email').prop('disabled', true);
      $(this).text('Edit');
    }
  });

  // Update button functionality
  $('#order_guest_form').on('submit', function (e) {
    e.preventDefault();

    var guestformData = $(this).serialize();

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'update_guest_info',
        guestformData: guestformData,
      },
      beforeSend: function () {
        $('.submit_msg_gi').show();
      },
      success: function (response) {
        if (response.success) {
          // alert('Guest info updated successfully.');
          $('.submit_msg_gi').text('Information Updated');
          location.reload(); // Optional: Reload to reflect changes
        } else {
          $('.submit_msg_gi').text('Error updating guest info: ' + response.data);
          // alert('Error updating guest info: ' + response.data);
        }
      },
    });
  });
  $('.order_notes').on('submit', function (e) {
    e.preventDefault();

    var notesformData = $(this).serialize();

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'update_order_notes',
        notesformData: notesformData,
      },
      beforeSend: function () {
        $('.submit_msg_on').show();
      },
      success: function (response) {
        if (response.success) {
          // alert('Guest info updated successfully.');
          $('.submit_msg_on').text('Internal notes updated');
          location.reload(); // Optional: Reload to reflect changes
        } else {
          $('.submit_msg_on').text('Error updating notes: ' + response.data);
          // alert('Error updating notes: ' + response.data);
        }
      },
    });
  });
  const slider = document.getElementById("cus-slider");
  const progressPath = document.getElementById("cus-progress");
  const percentageDisplay = document.getElementById("cus-percentage");

  const maxDashArray = 300; // Maximum visible part of the stroke

  function updateProgress() {
    const value = slider.value;
    const dashValue = (value / 100) * maxDashArray;

    // Update stroke-dasharray
    progressPath.setAttribute("stroke-dasharray", `${dashValue} 284`);

    // Update percentage display
    percentageDisplay.textContent = `${value}%`;
  }

  // Initialize
  updateProgress();

  // Add event listener for slider change
  slider.addEventListener("input", updateProgress);

});

document.addEventListener("DOMContentLoaded", function () {
  const fileSelectors = [
    document.getElementById("main_listing_image_1"),
    document.getElementById("main_listing_image_2"),
    document.getElementById("main_listing_image_3"),
    document.getElementById("main_listing_image_4"),
  ];

  const progressBars = [
    document.getElementById("progress1"),
    document.getElementById("progress2"),
    document.getElementById("progress3"),
    document.getElementById("progress4"),
  ];

  const progressBarFillers = [
    document.getElementById("progressBar1"),
    document.getElementById("progressBar2"),
    document.getElementById("progressBar3"),
    document.getElementById("progressBar4"),
  ];

  const uploadFields = [
    document.getElementById("uploadField2"),
    document.getElementById("uploadField3"),
    document.getElementById("uploadField4"),
  ];

  const messages = [
    document.getElementById("message1"),
    document.getElementById("message2"),
    document.getElementById("message3"),
    document.getElementById("message4"),
  ];

  fileSelectors.forEach((selector, index) => {
    if(selector){
      selector.addEventListener("change", function (event) {
        progressBars[index].style.display = "block";
  
        messages[index].style.display = "none";
  
        let progress = 0;
        let interval = setInterval(function () {
          progress += 10;
          if (progress <= 100) {
            progressBarFillers[index].style.width = progress + "%";
            progressBarFillers[index].setAttribute("aria-valuenow", progress);
          } else {
            clearInterval(interval);
  
            progressBars[index].style.display = "none";
  
            if (index < uploadFields.length) {
              uploadFields[index].style.display = "block";
            }
          }
        }, 200);
      });
    }
  });
});