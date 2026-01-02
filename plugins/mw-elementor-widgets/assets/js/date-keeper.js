(function() {
  // console.log("date keeper loaded");

  var link = document.querySelector('.smoobu-calendar-button-container a.button.st-cashier');

  if (!link) {
    return;
  }

  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'href') {
        if (!link.classList.contains('disabled')) {
          var url = new URL(link.href);
          var params = new URLSearchParams(url.search);

          var listing_id = params.get('page-id');

          var startDates = params.getAll('start-date');
          var endDates = params.getAll('end-date');

          var startDate = startDates.length > 0 ? startDates[startDates.length - 1] : '';
          var endDate = endDates.length > 0 ? endDates[endDates.length - 1] : '';

          var reservationData = {
            id: listing_id,
            start_date: startDate,
            end_date: endDate
          };

          sessionStorage.setItem('mwew_booking_completed', false);

          var reservations = [];
          try {
            var existing = sessionStorage.getItem('mwew_reservation_data');
            if (existing) {
              reservations = JSON.parse(existing);
              if (!Array.isArray(reservations)) {
                reservations = [];
              }
            }
          } catch(e) {
            reservations = [];
          }

          var existingIndex = reservations.findIndex(function(r) {
            return r.id === listing_id;
          });

          if (existingIndex !== -1) {
            reservations[existingIndex] = reservationData;
          } else {
            reservations.push(reservationData);
          }

          sessionStorage.setItem('mwew_reservation_data', JSON.stringify(reservations));

          // console.log('Reservation data updated in sessionStorage:', reservations);
        }
      }
    });
  });

  observer.observe(link, { attributes: true, attributeFilter: ['href'] });
})();
