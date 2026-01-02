"use strict";

(function($) {

    function hero_slider() {
        var container = $(".book-hero-bg-slider");

        var desktopImages = container.data("src") || [];
        var mobileImages = container.data("mobile-src") || [];
        var isMobile = window.innerWidth <= 768;

        // Merge: use mobile image if exists at index, else desktop
        var images = desktopImages.map(function(url, i) {
            return (isMobile && mobileImages[i]) ? mobileImages[i] : url;
        });

        var index = 0,
            slides = [],
            dotsContainer = container.find(".slider-dots");

        if (!images.length) return;

        images.forEach(function(url, i) {
            var slide = $("<div class='bg-slide'></div>").css("background-image", "url(" + url + ")");
            var dot = $("<span></span>").on("click", function() {
                goToSlide(i);
            });
            container.append(slide);
            dotsContainer.append(dot);
            slides.push(slide);
        });

        var dots = dotsContainer.find("span");

        function goToSlide(i) {
            slides[index].removeClass("active");
            dots.eq(index).removeClass("active");
            index = i;
            slides[index].addClass("active");
            dots.eq(index).addClass("active");
        }

        function autoSlide() {
            goToSlide((index + 1) % slides.length);
        }

        goToSlide(0);
        setInterval(autoSlide, 9000);
    }



    function search_btn(){
        const checkinInput = document.querySelector(".checkin-date");
        const checkoutInput = document.querySelector(".checkout-date");
        const searchButton = document.querySelector(".search-button");

        if (searchButton) {
            searchButton.addEventListener("click", function () {
                const checkInDate = checkinInput.value;
                const checkOutDate = checkoutInput.value;
                const homeUrl = searchButton.dataset.homeUrl;
                

                if (checkInDate && checkOutDate) {
                    const url = `${homeUrl}/listings/?check_in=${checkInDate}&check_out=${checkOutDate}`;
                    window.location.href = url;
                }
            });
        }else{
            console.log("search-button")
        }
    }
    $(document).ready(function() {
        hero_slider()
        search_btn()
    });
})(jQuery);
