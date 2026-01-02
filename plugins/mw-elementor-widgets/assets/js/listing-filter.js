document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("listing-search-form");
  if (!form) return;

  const button = form.querySelector(".listing-search-btn");
  const checkinInput = form.querySelector(".checkin-date");
  const checkoutInput = form.querySelector(".checkout-date");
  const loadingText = button
    ? button.getAttribute("data-loading-text")
    : "Loading...";
  const results = document.getElementById("listeo-listings-container");
  const paginationContainer = document.querySelector(
    "div.pagination-container"
  );

  function updateTopLevelLinksWithQuery(queryString) {
    if (!queryString) return;

    document.querySelectorAll(".top-level-link").forEach((link) => {
      const url = new URL(link.href);
      const params = new URLSearchParams(url.search);
      const newParams = new URLSearchParams(queryString);

      ["check_in", "check_out", "country_id", "page"].forEach((key) => {
        if (newParams.has(key)) {
          params.set(key, newParams.get(key));
        } else {
          params.delete(key);
        }
      });

      url.search = params.toString();
      link.href = url.toString();
    });
  }

  function submitListingsForm(page = 1) {
    if (!button) return;

    const btnText = button.textContent;

    button.disabled = true;
    button.textContent = loadingText;
    document.body.classList.add("loading-listing");
    if (results) results.classList.add("loading");

    const countrySelect = form.querySelector('select[name="country_id"]');
    const countryValue = countrySelect ? countrySelect.value : "";

    const params = new URLSearchParams(window.location.search);
    if (checkinInput) params.set("check_in", checkinInput.value);
    if (checkoutInput) params.set("check_out", checkoutInput.value);

    if (countryValue) {
      params.set("country_id", countryValue);
    } else {
      params.delete("country_id");
    }

    params.set("page", page);

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({}, "", newUrl);

    updateTopLevelLinksWithQuery(params.toString());

    const formData = new FormData(form);
    formData.append("action", "mwew_get_listings");
    formData.append("security", mwewPluginData.nonce);
    formData.set("page", page);

    fetch(mwewPluginData.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (results) {
          results.classList.remove("loading");
          results.innerHTML = data.html || "<p>No listings found.</p>";
        }
        if (paginationContainer) {
          paginationContainer.innerHTML = data.pagination || "";
        }

        if (typeof numericalRating === "function") numericalRating();
        if (typeof starRating === "function") starRating();

        if (results) {
          results.dispatchEvent(new CustomEvent("update_results_success"));
        }

        if (
          typeof listeo_core !== "undefined" &&
          listeo_core.map_provider === "google"
        ) {
          const map = document.getElementById("map");
          if (map) {
            // mainMap();
          }
        }

        button.disabled = false;
        button.textContent = btnText;
        document.body.classList.remove("loading-listing");

        document.querySelectorAll('.listing-small-badge.pricing-badge').forEach(el => {
          el.textContent = el.textContent.replace(/€/g, '').trim();
        });
      })
      .catch((error) => {
        console.error("Listing AJAX error:", error);
        if (button) {
          button.disabled = false;
          button.textContent = btnText;
        }
        document.body.classList.remove("loading-listing");
        if (results) results.classList.remove("loading");
      });
  }

  if (paginationContainer) {
    paginationContainer.addEventListener("click", function (e) {
      const link = e.target.closest("a");
      if (!link) return;

      e.preventDefault();

      const li = link.closest("li");

      let page = li ? li.getAttribute("data-paged") : null;

      if (!page) {
        page = link.getAttribute("data-page");
      }
      if (!page) {
        const href = link.getAttribute("href");
        if (href) {
          const match = href.match(/[?&]page=(\d+)/);
          if (match && match[1]) page = match[1];
        }
      }

      if (!page) page = 1;

      console.log("Pagination clicked, page:", page);

      submitListingsForm(page);
    });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    submitListingsForm(1);
  });

  ["input", "change"].forEach((eventType) => {
    if (checkinInput)
      checkinInput.addEventListener(eventType, () => submitListingsForm(1));
    if (checkoutInput)
      checkoutInput.addEventListener(eventType, () => submitListingsForm(1));
  });

  submitListingsForm();

});
