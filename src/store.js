import { store, getConfig } from "@wordpress/interactivity";

const { state, actions } = store("wooflux/filters", {
  state: {
    filters: {
      categories: [],
      priceMin: 0,
      priceMax: 0,
      onSale: false,
      inStock: false,
      attributes: {},
      rating: 0,
    },
    isLoading: false,
    totalProducts: 0,
    totalPages: 1,
    currentPage: 1,
    showPagination: false,
    get hasPrev() {
      return state.currentPage > 1;
    },
    get hasNext() {
      return state.currentPage < state.totalPages;
    },
  },

  callbacks: {
    // WP Interactivity API does not reliably deep-merge boolean values from
    // server state into nested JS store objects. Read URL params directly.
    syncFromUrl() {
      const params = new URLSearchParams(window.location.search);
      state.filters.onSale  = params.has("wf_sale");
      state.filters.inStock = params.has("wf_stock");
    },
  },

  actions: {
    toggleCategory() {
      actions._scheduleRefetch();
    },

    setPriceMin(event) {
      state.filters.priceMin = parseFloat(event.target.value) || 0;
      actions._scheduleRefetch();
    },

    setPriceMax(event) {
      state.filters.priceMax = parseFloat(event.target.value) || 0;
      actions._scheduleRefetch();
    },

    toggleOnSale() {
      state.filters.onSale = !state.filters.onSale;
      actions._scheduleRefetch();
    },

    toggleInStock() {
      state.filters.inStock = !state.filters.inStock;
      actions._scheduleRefetch();
    },

    toggleAttribute(event) {
      const taxonomy = event.target.dataset.taxonomy;
      const value = event.target.value;
      const current = state.filters.attributes[taxonomy] || [];
      const idx = current.indexOf(value);
      state.filters.attributes = {
        ...state.filters.attributes,
        [taxonomy]:
          idx === -1 ? [...current, value] : current.filter((v) => v !== value),
      };
      actions._scheduleRefetch();
    },

    setRating(event) {
      state.filters.rating = parseInt(event.target.value, 10) || 0;
      actions._scheduleRefetch();
    },

    resetFilters() {
      state.filters = {
        categories: [],
        priceMin: 0,
        priceMax: 0,
        onSale: false,
        inStock: false,
        attributes: {},
        rating: 0,
      };
      state.currentPage = 1;
      document
        .querySelectorAll(".wooflux-categories input[type='checkbox']")
        .forEach((cb) => { cb.checked = false; });
      document
        .querySelectorAll(".wooflux-price-range input[type='number']")
        .forEach((input) => { input.value = ""; });
      actions._scheduleRefetch();
    },

    *prevPage() {
      const curr = parseInt(
        document.querySelector(".wooflux-page-current")?.textContent ?? "1",
        10
      );
      if (curr > 1) {
        state.currentPage = curr - 1;
        yield* actions.fetchProducts();
      }
    },

    *nextPage() {
      const total = parseInt(
        document.querySelector(".wooflux-page-total")?.textContent ?? "1",
        10
      );
      if (state.currentPage < total) {
        state.currentPage++;
        yield* actions.fetchProducts();
      }
    },

    _scheduleRefetch() {
      state.currentPage = 1;
      clearTimeout(window.__woofluxDebounce);
      window.__woofluxDebounce = setTimeout(() => {
        actions.fetchProducts();
      }, 300);
    },

    *fetchProducts() {
      // Always sync filter state from DOM before fetching — WP Interactivity
      // API does not reliably hydrate arrays or values from server state into
      // the JS reactive store. DOM is the source of truth for all filter inputs.
      const checked = document.querySelectorAll(
        ".wooflux-categories input[type='checkbox']:checked"
      );
      state.filters.categories = Array.from(checked).map((cb) =>
        parseInt(cb.value, 10)
      );

      const priceInputs = document.querySelectorAll(
        ".wooflux-price-range input[type='number']"
      );
      if (priceInputs[0]) state.filters.priceMin = parseFloat(priceInputs[0].value) || 0;
      if (priceInputs[1]) state.filters.priceMax = parseFloat(priceInputs[1].value) || 0;

      state.isLoading = true;

      const config = getConfig("wooflux/filters");
      const params = buildQueryParams(state.filters, state.currentPage);

      try {
        const url = `${config.restUrl}?${params.toString()}`;
        const response = yield fetch(url, {
          headers: { "X-WP-Nonce": config.nonce },
        });

        if (!response.ok) throw new Error(`Filter request failed: ${response.status}`);

        const data = yield response.json();

        const container = document.querySelector(".wooflux-products-container");
        if (container) {
          container.innerHTML = data.html;
        }

        state.totalProducts  = data.total;
        state.totalPages     = data.pages ?? 1;
        state.showPagination = state.totalPages > 1;

        // Direct DOM updates for pagination — same pattern as products container.
        const pag = document.querySelector(".wooflux-pagination");
        if (pag) {
          pag.style.display = state.totalPages > 1 ? "" : "none";
          const curr = pag.querySelector(".wooflux-page-current");
          const tot  = pag.querySelector(".wooflux-page-total");
          const prev = pag.querySelector(".wooflux-page-prev");
          const next = pag.querySelector(".wooflux-page-next");
          if (curr) curr.textContent = state.currentPage;
          if (tot)  tot.textContent  = state.totalPages;
          if (prev) prev.disabled    = state.currentPage <= 1;
          if (next) next.disabled    = state.currentPage >= state.totalPages;
        }

        if (config.enableUrlSync) {
          const newUrl = `${window.location.pathname}?${buildUrlParams(state.filters).toString()}`;
          history.pushState({ wooflux: true }, "", newUrl);
        }
      } catch (error) {
        console.error("WooFlux filter error:", error);
      } finally {
        state.isLoading = false;
      }
    },
  },
});

export function buildQueryParams(filters, page) {
  const params = new URLSearchParams();
  filters.categories.forEach((id) => params.append("categories[]", id));
  if (filters.priceMin) params.set("price_min", filters.priceMin);
  if (filters.priceMax) params.set("price_max", filters.priceMax);
  if (filters.onSale) params.set("on_sale", "1");
  if (filters.inStock) params.set("in_stock", "1");
  if (filters.rating) params.set("rating", filters.rating);
  if (page > 1) params.set("page", page);

  Object.entries(filters.attributes).forEach(([tax, values]) => {
    if (values.length) params.set(`attributes[${tax}]`, values.join(","));
  });

  return params;
}

function buildUrlParams(filters) {
  const params = new URLSearchParams();
  if (filters.categories.length) params.set("wf_cat", filters.categories.join(","));
  if (filters.priceMin) params.set("wf_price_min", filters.priceMin);
  if (filters.priceMax) params.set("wf_price_max", filters.priceMax);
  if (filters.onSale) params.set("wf_sale", "1");
  if (filters.inStock) params.set("wf_stock", "1");
  if (filters.rating) params.set("wf_rating", filters.rating);

  Object.entries(filters.attributes).forEach(([tax, values]) => {
    if (values.length) params.set(`wf_attr_${tax}`, values.join(","));
  });

  return params;
}
