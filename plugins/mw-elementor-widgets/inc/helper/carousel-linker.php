<?php 

namespace MWEW\Inc\Helper;

class Carousel_Linker{

    public function __construct(){
        add_action("wp_footer", [$this, 'link']);
    }

    public function link() {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const gridLoop = document.getElementById('mw-grid-loop');
                if (!gridLoop) return;

                // Target swiper slides instead of section[id]
                const slides = gridLoop.querySelectorAll('.swiper-slide.e-loop-item');
                slides.forEach(function(slide) {
                    // Find the <a> inside the <h4>
                    const linkElement = slide.querySelector('h4.elementor-heading-title a');
                    if (!linkElement) return;

                    const permalink = linkElement.href;
                    if (!permalink) return;

                    // Make slide clickable
                    slide.style.cursor = 'pointer';
                    slide.addEventListener('click', function(e) {
                        // Prevent overriding clicks on actual links/buttons inside
                        if (!e.target.closest('a')) {
                            window.location.href = permalink;
                        }
                    });
                });
            });
        </script>

        <?php
    }




}