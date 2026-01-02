document.addEventListener('DOMContentLoaded', function() {

    const uniqueId = window.mwewHeroSliderUniqueId;
    const ajaxUrl = window.mwewHeroSliderAjaxUrl;

    if (!uniqueId) {
        console.warn('Unique ID not found.');
        return;
    }

    const modal = document.getElementById(uniqueId + '-modal');
    if (!modal) {
        console.warn('Modal container not found.');
        return;
    }

    const loader = modal.querySelector('.modal-loader');
    const modalTitle = modal.querySelector('.modal-title');
    const modalBody = modal.querySelector('.modal-body');
    const modalClose = modal.querySelector('.modal-close');

    function showLoader() {
        loader.style.display = 'flex';
        modalBody.innerHTML = '';
    }

    function hideLoader() {
        loader.style.display = 'none';
    }

    async function fetchPopupContent(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error');
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const popupSection = doc.querySelector('#mwew-popup');
            return popupSection ? popupSection.outerHTML : '<p>Content not found.</p>';
        } catch (error) {
            return `<p>Error loading content: ${error.message}</p>`;
        }
    }

    async function fetchProductContent(productIdOrUrl) {
        let productId = productIdOrUrl;
        if (productIdOrUrl.startsWith('http')) {
            const match = productIdOrUrl.match(/product\/(\d+)/) || productIdOrUrl.match(/product=([0-9]+)/);
            if (match) productId = match[1];
        }
        if (!productId) return '<p>Invalid product identifier.</p>';

        try {
            const response = await fetch(`${ajaxUrl}?action=mwew_fetch_product&product_id=${productId}`);
            if (!response.ok) throw new Error('Network error');
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const popupSection = doc.querySelector('#mwew-popup');
            return popupSection ? popupSection.outerHTML : '<p>Popup content not found in product page.</p>';
        } catch (error) {
            return `<p>Error loading product: ${error.message}</p>`;
        }
    }

    function initWooVariationForms() {
        if (typeof window.jQuery === 'undefined') {
            console.warn('jQuery not found, cannot init WooCommerce variations.');
            return;
        }

        try {
            document.querySelectorAll('.variations_form').forEach(form => {
                window.jQuery(form).wc_variation_form();
                form.querySelectorAll('.variations select').forEach(select => {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        } catch (e) {
            console.error('Error initializing WooCommerce variation forms:', e);
        }
    }

    function openModal(title, content) {
        modalTitle.textContent = title;
        modalBody.innerHTML = content;
        hideLoader();
        modal.style.display = 'flex';

        initWooVariationForms();
    }

    function closeModal() {
        modal.style.display = 'none';
        modalBody.innerHTML = '';
    }

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.body.addEventListener('click', async function(e) {
        const btn = e.target.closest('.open-modal-btn');
        if (!btn) return;
        e.preventDefault();

        const contentType = btn.getAttribute('data-modal-content-type') || 'custom';
        const title = btn.getAttribute('data-modal-title') || '';
        showLoader();
        modal.style.display = 'flex';

        if (contentType === '1') {
            let content = btn.getAttribute('data-modal-content') || '';
            try {
                content = JSON.parse(content);
            } catch {
                // fallback: string content
            }
            openModal(title, content);
        } else if (contentType === '2') {
            const pageUrl = btn.getAttribute('data-modal-page-url');
            if (!pageUrl) {
                openModal(title, '<p>Page URL not provided.</p>');
                return;
            }
            const content = await fetchPopupContent(pageUrl);
            openModal(title, content);
        } else if (contentType === '3') {
            const productIdOrUrl = btn.getAttribute('data-modal-product');
            if (!productIdOrUrl) {
                openModal(title, '<p>Product ID or URL not provided.</p>');
                return;
            }
            const content = await fetchProductContent(productIdOrUrl);
            openModal(title, content);
        } else {
            openModal(title, '<p>Unsupported modal content type. </p>' + contentType);
        }
    });


    document.querySelectorAll('form.variations_form, form.cart').forEach(form => {
        const nameInput = form.querySelector('#flexible_coupon_recipient_name');
        const messageInput = form.querySelector('#flexible_coupon_recipient_message');
        const alertMessage = form.dataset.alert || 'Please check required fields.';

        if (!nameInput || !messageInput) return;

        form.addEventListener('submit', function(e) {
            const name = nameInput.value.trim();
            const message = messageInput.value.trim();
            let valid = true;

            if (name.length < 2) {
                nameInput.style.borderColor = 'red';
                valid = false;
            } else {
                nameInput.style.borderColor = '';
            }

            if (message.length < 5) {
                messageInput.style.borderColor = 'red';
                valid = false;
            } else {
                messageInput.style.borderColor = '';
            }

            if (!valid) {
                e.preventDefault();
                alert(alertMessage);
            }
        });
    });

});
