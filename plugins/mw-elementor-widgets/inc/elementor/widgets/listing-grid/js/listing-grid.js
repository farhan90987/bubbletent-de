document.addEventListener('DOMContentLoaded', function () {
    const features = document.querySelectorAll('.listing-feature');
    const countries = document.querySelectorAll('.listing-country');
    
    const grid = document.querySelector('.listing-grid');
    const maxPosts = grid.getAttribute('data-max') || 6;
    const loadingText = grid.getAttribute('data-loading');

    let activeFeatureId = null;
    let activeCountryId = null;

    function loadListingsByFilter(featureId, countryId) {
        grid.innerHTML = `<p class="mw-loading">${loadingText}...</p>`;

        // Build URL params including both feature and country IDs
        const url = new URL(mwewPluginData.ajax_url);
        url.searchParams.append('action', 'filter_listing_by_feature_and_country');
        url.searchParams.append('feature_id', encodeURIComponent(featureId));
        url.searchParams.append('country_id', encodeURIComponent(countryId));
        url.searchParams.append('max', maxPosts);

        fetch(url.toString())
            .then(res => res.text())
            .then(html => {
                grid.innerHTML = html;
            })
            .catch(() => {
                grid.innerHTML = `<p class="mwew-not-found">Error ${loadingText}.</p>`;
            });
    }

    function activateFeature(el) {
        features.forEach(feature => feature.classList.remove('active'));
        el.classList.add('active');
        activeFeatureId = el.getAttribute('data-id');
    }

    function activateCountry(el) {
        countries.forEach(country => country.classList.remove('active'));
        el.classList.add('active');
        activeCountryId = el.getAttribute('data-id');
    }

    // Add click listeners for features
    features.forEach(feature => {
        feature.addEventListener('click', function () {
            activateFeature(this);
            // Reload listings with new feature and current active country
            loadListingsByFilter(activeFeatureId, activeCountryId);
        });
    });

    // Add click listeners for countries
    countries.forEach(country => {
        country.addEventListener('click', function () {
            activateCountry(this);
            // Reload listings with current active feature and new country
            loadListingsByFilter(activeFeatureId, activeCountryId);
        });
    });

    // Initialize default active selections and load listings
    if (features.length > 0) {
        activateFeature(features[0]);
    }
    if (countries.length > 0) {
        activateCountry(countries[0]);
    }

    if (activeFeatureId && activeCountryId) {
        loadListingsByFilter(activeFeatureId, activeCountryId);
    }
});
