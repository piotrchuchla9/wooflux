# WooFlux — Technical Specification

**WooCommerce Live Product Filter Plugin built on WordPress Interactivity API**

**Version:** 1.0 · **Date:** June 2026 · **Status:** Ready for Development

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [Scope & MVP Definition](#2-scope--mvp-definition)
3. [Technology Stack](#3-technology-stack)
4. [Plugin Architecture](#4-plugin-architecture)
5. [File Structure](#5-file-structure)
6. [Database & Data Layer](#6-database--data-layer)
7. [PHP Backend](#7-php-backend)
8. [JavaScript — Interactivity API](#8-javascript--interactivity-api)
9. [REST API Endpoints](#9-rest-api-endpoints)
10. [Admin Settings Panel](#10-admin-settings-panel)
11. [Frontend UI Specification](#11-frontend-ui-specification)
12. [Freemium & Licensing](#12-freemium--licensing)
13. [Build System & Tooling](#13-build-system--tooling)
14. [WordPress.org Submission](#14-wordpressorg-submission)
15. [Freemius Integration (Pro)](#15-freemius-integration-pro)
16. [Testing Strategy](#16-testing-strategy)
17. [Development Phases](#17-development-phases)
18. [Security Checklist](#18-security-checklist)
19. [Open Questions](#19-open-questions)
20. [Glossary](#20-glossary)

---

## 1. Product Overview

### 1.1 Problem Statement

WooCommerce stores need product filtering — customers filter by category, price, color, size, and attributes. The dominant solution (YITH Ajax Product Filter, 1M+ installs) is built on jQuery, loads heavy JavaScript bundles, frequently conflicts with other plugins, and causes visible page lag on filter interactions.

WordPress 6.5+ ships the **Interactivity API** — a native, lightweight reactive system designed exactly for this. WooCommerce itself migrated its Mini Cart to this API in December 2025 (WooCommerce 10.4). But no third-party filter plugin has been built on it yet.

### 1.2 Solution — WooFlux

WooFlux is a WooCommerce product filter plugin built natively on the WordPress Interactivity API. It replaces jQuery-based AJAX filtering with a reactive, server-rendered approach: instant UI updates, zero jQuery dependency, no conflicts with other Interactivity API plugins, and full SEO preservation via server-side rendering.

> **Core loop:** User clicks filter → Interactivity API updates state → REST API fetches filtered products → products update in page without reload → URL updates for shareability

### 1.3 Target Users

| User | Pain | Willingness to pay |
|---|---|---|
| WooCommerce store owner | Current filter plugin conflicts with theme or other plugins | High — directly affects sales |
| WordPress developer / agency | Needs modern, maintainable filter solution for client sites | High — saves hours of custom work |
| Store owner on FSE (Full Site Editing) theme | Old jQuery plugins break FSE themes | High — no alternatives |

### 1.4 Competitive Advantage

| Feature | WooFlux | YITH Ajax Filter | FacetWP |
|---|---|---|---|
| Built on Interactivity API | ✅ | ❌ jQuery | ❌ Custom JS |
| Zero jQuery dependency | ✅ | ❌ | ❌ |
| Works with FSE themes | ✅ | ⚠️ partial | ⚠️ partial |
| SSR + SEO safe | ✅ | ⚠️ | ✅ |
| Inter-block communication | ✅ native | ❌ | ❌ |
| Free tier on WordPress.org | ✅ | ✅ | ❌ |
| Price (Pro) | $69/year | $89/year | $99/year |

---

## 2. Scope & MVP Definition

### 2.1 MVP Features (Phase 1 — 8 weeks)

> **Goal:** 500 active installs on WordPress.org within 60 days of launch. 10 Pro sales within 90 days.

#### 2.1.1 Filter Types (Free)

- **Category filter** — checkbox list of WooCommerce product categories
- **Price range filter** — min/max input fields with live validation
- **On-sale filter** — single checkbox "Show only on-sale products"
- **In-stock filter** — single checkbox "In stock only"

#### 2.1.2 Filter Types (Pro)

- **Color swatch filter** — visual color circles for `pa_color` attribute
- **Size swatch filter** — button-style selectors for `pa_size` attribute
- **Rating filter** — star rating minimum selector
- **Custom attribute filter** — any `pa_*` taxonomy as checkbox list
- **Price range slider** — drag handles instead of input fields

#### 2.1.3 Display & Layout

- Gutenberg block: `wooflux/filter-panel` — sidebar or horizontal bar placement
- Gutenberg block: `wooflux/product-grid` — replaces or wraps WooCommerce Products block
- Both blocks communicate via shared Interactivity API store
- Filter panel renders server-side (PHP), reactive client-side (Interactivity API)

#### 2.1.4 URL & State

- Active filters reflected in URL query params (`?wf_cat=shirts&wf_price_max=100`)
- Browser back/forward works correctly (Interactivity API router)
- Shareable filtered URLs load correct state on first render

#### 2.1.5 Performance

- Zero jQuery loaded by WooFlux
- Filter panel JS payload: < 5 KB gzipped
- Product grid update: < 300ms on average hosting (target)
- Compatible with WP Rocket, LiteSpeed Cache, W3 Total Cache

### 2.2 Out of Scope for MVP

| Feature | Phase |
|---|---|
| Elementor / Divi widget | Phase 2 |
| AJAX pagination integrated with filters | Phase 2 |
| Search-as-you-type product search | Phase 2 |
| Multi-currency filter | Phase 2 |
| Saved filter presets | Phase 3 |
| Analytics (which filters are used most) | Phase 3 |

### 2.3 Success Metrics

| Metric | Target | How measured |
|---|---|---|
| WordPress.org active installs | 500 at 60 days | WordPress.org stats |
| WordPress.org rating | ≥ 4.5 stars | WordPress.org reviews |
| Pro conversions | 10 at 90 days | Freemius dashboard |
| Filter response time | < 300ms | Manual testing on shared hosting |
| Zero JS errors | 0 console errors | Manual + automated testing |

---

## 3. Technology Stack

| Layer | Technology | Notes |
|---|---|---|
| Language (backend) | PHP 8.0+ | Minimum requirement |
| Language (frontend) | JavaScript (ESM) | Via `@wordpress/interactivity` |
| Build tool | `@wordpress/scripts` (webpack) | Standard WP block toolchain |
| Block framework | WordPress Block API v3 | `block.json` + `register_block_type()` |
| Interactivity | WordPress Interactivity API | Shipped in WP core 6.5+ |
| Styling | CSS (no Tailwind, no preprocessor) | Plain CSS with CSS custom properties |
| Testing (PHP) | PHPUnit + WP_Mock | Standard WP plugin testing |
| Testing (JS) | Jest + `@wordpress/jest-preset-default` | Standard WP JS testing |
| E2E testing | Playwright + `@wordpress/e2e-test-utils-playwright` | WP standard |
| Licensing / payments | Freemius SDK | For Pro version sales |
| i18n | WordPress i18n (`__()`, `_e()`, `_n()`) | `.pot` file generated |
| Minimum WP version | 6.5 | Required for Interactivity API |
| Minimum WC version | 8.0 | Required for block-based products |
| Minimum PHP | 8.0 | For named arguments, match expressions |

---

## 4. Plugin Architecture

### 4.1 How It Works — End to End

```
1. Admin installs WooFlux, adds two blocks to a page:
   [wooflux/filter-panel] + [wooflux/product-grid]

2. Page loads:
   - PHP renders filter panel HTML with data-wp-* directives
   - PHP renders initial product grid HTML (SSR, SEO-safe)
   - Interactivity API store initializes with current filter state
     (read from URL query params or defaults)

3. User interacts with a filter:
   - Interactivity API directive (data-wp-on--change) fires
   - Store action updates state.filters
   - Store side effect (data-wp-watch) triggers fetch to REST API
   - REST API returns new product HTML (server-rendered)
   - Store updates state.productsHtml
   - data-wp-html directive swaps product grid content
   - URL updates via history.pushState

4. User shares URL / presses Back:
   - PHP reads URL params on load, sets initial store state
   - Same SSR flow as step 2
```

### 4.2 Block Communication

Both blocks share a single Interactivity API store with namespace `wooflux/filters`:

```
wooflux/filter-panel  ──┐
                         ├── store("wooflux/filters", { state, actions })
wooflux/product-grid  ──┘
```

This is the key advantage over jQuery-based plugins: no custom events, no DOM manipulation, no race conditions. The store is the single source of truth.

### 4.3 Store Shape

```javascript
// Defined in src/store.js — shared between both blocks
const { state, actions } = store("wooflux/filters", {
  state: {
    // Active filter values
    filters: {
      categories: [],       // array of term IDs
      priceMin: 0,
      priceMax: 0,          // 0 = no limit
      onSale: false,
      inStock: false,
      attributes: {},       // { pa_color: ['red','blue'], pa_size: ['M'] }
      rating: 0,            // 0 = no filter, 1-5 = minimum rating
    },
    // UI state
    isLoading: false,
    productsHtml: "",       // raw HTML from REST API
    totalProducts: 0,
    currentPage: 1,
    // Config (set by PHP via wp_interactivity_config)
    config: {
      restUrl: "",
      nonce: "",
      currency: "USD",
      priceDecimals: 2,
      isPro: false,
    }
  },
  actions: {
    toggleCategory(termId) { /* ... */ },
    setPrice(min, max) { /* ... */ },
    toggleOnSale() { /* ... */ },
    toggleInStock() { /* ... */ },
    toggleAttribute(taxonomy, value) { /* ... */ },
    setRating(stars) { /* ... */ },
    resetFilters() { /* ... */ },
    *fetchProducts() { /* generator — async action */ },
  },
  callbacks: {
    onFiltersChange() { /* watches state.filters, triggers fetchProducts */ },
    onProductsLoaded() { /* updates URL */ },
  }
});
```

---

## 5. File Structure

```
wooflux/
├── wooflux.php                        # Plugin entry point, headers, bootstrap
├── readme.txt                         # WordPress.org readme (required)
├── uninstall.php                      # Cleanup on uninstall
├── LICENSE.txt                        # GPL v2
│
├── includes/
│   ├── class-wooflux.php              # Main plugin class, singleton
│   ├── class-wooflux-blocks.php       # Block registration
│   ├── class-wooflux-rest.php         # REST API endpoints
│   ├── class-wooflux-query.php        # WooCommerce product query builder
│   ├── class-wooflux-settings.php     # Admin settings page
│   ├── class-wooflux-assets.php       # Script/style enqueuing
│   └── class-wooflux-freemius.php     # Freemius SDK integration (Pro)
│
├── blocks/
│   ├── filter-panel/
│   │   ├── block.json                 # Block metadata
│   │   ├── render.php                 # Server-side render (PHP)
│   │   ├── src/
│   │   │   ├── index.js               # Block editor registration
│   │   │   ├── edit.js                # Block editor UI
│   │   │   ├── view.js                # Frontend Interactivity API
│   │   │   └── style.css             # Frontend styles
│   │   └── build/                     # Compiled output (gitignored)
│   │
│   └── product-grid/
│       ├── block.json
│       ├── render.php
│       ├── src/
│       │   ├── index.js
│       │   ├── edit.js
│       │   ├── view.js
│       │   └── style.css
│       └── build/
│
├── src/
│   └── store.js                       # Shared Interactivity API store
│
├── templates/
│   ├── filter-panel.php               # Filter panel HTML template
│   ├── product-card.php               # Single product card (used by REST)
│   └── product-grid.php              # Product grid wrapper
│
├── assets/
│   ├── css/
│   │   └── admin.css                  # Admin panel styles
│   └── js/
│       └── admin.js                   # Admin panel scripts
│
├── languages/
│   └── wooflux.pot                    # Translation template
│
├── tests/
│   ├── php/
│   │   ├── bootstrap.php
│   │   ├── test-query.php
│   │   └── test-rest.php
│   └── js/
│       └── store.test.js
│
├── package.json
├── composer.json
└── webpack.config.js                  # Extends @wordpress/scripts config
```

---

## 6. Database & Data Layer

### 6.1 No Custom Tables

WooFlux does not create custom database tables. All data is stored using existing WordPress/WooCommerce structures:

- Product queries use `WC_Product_Query` and `WP_Query` — standard WooCommerce APIs
- Filter configuration per block stored in block attributes (JSON in `post_content`)
- Plugin settings stored in `wp_options` with prefix `wooflux_`

### 6.2 Plugin Options (wp_options)

| Option key | Type | Default | Description |
|---|---|---|---|
| `wooflux_version` | string | `"1.0.0"` | Installed version, used for migrations |
| `wooflux_cache_ttl` | int | `300` | REST response cache TTL in seconds |
| `wooflux_license_key` | string | `""` | Freemius license key (Pro) |
| `wooflux_is_pro` | bool | `false` | Pro status flag |
| `wooflux_default_per_page` | int | `12` | Default products per page |
| `wooflux_enable_url_sync` | bool | `true` | Sync filters to URL |

### 6.3 Transient Caching

REST API responses are cached using WordPress transients:

```php
// Cache key pattern
$cache_key = 'wooflux_' . md5(serialize($query_args));
$cached    = get_transient($cache_key);

if (false === $cached) {
    $result = $this->run_query($query_args);
    set_transient($cache_key, $result, get_option('wooflux_cache_ttl', 300));
}
```

Cache is invalidated on:
- `woocommerce_product_set_stock_status` (stock change)
- `save_post_product` (product save)
- `wooflux_flush_cache` (manual flush from settings)

---

## 7. PHP Backend

### 7.1 Main Plugin File — `wooflux.php`

```php
<?php
/**
 * Plugin Name:       WooFlux — Live Product Filter
 * Plugin URI:        https://wooflux.com
 * Description:       Lightning-fast WooCommerce product filtering built on the WordPress Interactivity API. Zero jQuery. No conflicts.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wooflux
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   10.x
 */

defined('ABSPATH') || exit;

define('WOOFLUX_VERSION', '1.0.0');
define('WOOFLUX_FILE',    __FILE__);
define('WOOFLUX_DIR',     plugin_dir_path(__FILE__));
define('WOOFLUX_URL',     plugin_dir_url(__FILE__));
define('WOOFLUX_MIN_WP',  '6.5');
define('WOOFLUX_MIN_WC',  '8.0');
define('WOOFLUX_MIN_PHP', '8.0');

// Autoloader
require_once WOOFLUX_DIR . 'includes/class-wooflux.php';

// Boot
add_action('plugins_loaded', [WooFlux::class, 'get_instance']);
```

### 7.2 Main Class — `includes/class-wooflux.php`

```php
<?php
class WooFlux {
    private static ?WooFlux $instance = null;

    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->check_requirements();
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function check_requirements(): void {
        // Check PHP version
        if (version_compare(PHP_VERSION, WOOFLUX_MIN_PHP, '<')) {
            add_action('admin_notices', fn() => $this->notice_php_version());
            return;
        }
        // Check WooCommerce active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', fn() => $this->notice_woocommerce());
            return;
        }
    }

    private function load_dependencies(): void {
        require_once WOOFLUX_DIR . 'includes/class-wooflux-blocks.php';
        require_once WOOFLUX_DIR . 'includes/class-wooflux-rest.php';
        require_once WOOFLUX_DIR . 'includes/class-wooflux-query.php';
        require_once WOOFLUX_DIR . 'includes/class-wooflux-settings.php';
        require_once WOOFLUX_DIR . 'includes/class-wooflux-assets.php';
    }

    private function init_hooks(): void {
        add_action('init', [new WooFlux_Blocks(), 'register']);
        add_action('rest_api_init', [new WooFlux_REST(), 'register_routes']);
        add_action('admin_menu', [new WooFlux_Settings(), 'add_menu']);
        add_action('wp_enqueue_scripts', [new WooFlux_Assets(), 'enqueue_frontend']);
        add_action('admin_enqueue_scripts', [new WooFlux_Assets(), 'enqueue_admin']);

        // WooCommerce HPOS compatibility declaration
        add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'custom_order_tables', WOOFLUX_FILE, true
                );
            }
        });
    }
}
```

### 7.3 Block Registration — `includes/class-wooflux-blocks.php`

```php
<?php
class WooFlux_Blocks {
    public function register(): void {
        // Register filter-panel block
        register_block_type(WOOFLUX_DIR . 'blocks/filter-panel', [
            'render_callback' => [$this, 'render_filter_panel'],
        ]);

        // Register product-grid block
        register_block_type(WOOFLUX_DIR . 'blocks/product-grid', [
            'render_callback' => [$this, 'render_product_grid'],
        ]);
    }

    public function render_filter_panel(array $attributes, string $content, WP_Block $block): string {
        // Build available filter data
        $categories  = $this->get_categories($attributes);
        $price_range = $this->get_price_range();
        $attributes_list = $this->get_product_attributes();

        // Pass config to Interactivity API store
        wp_interactivity_config('wooflux/filters', [
            'restUrl'       => rest_url('wooflux/v1/products'),
            'nonce'         => wp_create_nonce('wp_rest'),
            'currency'      => get_woocommerce_currency_symbol(),
            'priceDecimals' => wc_get_price_decimals(),
            'isPro'         => wooflux_is_pro(),
        ]);

        // Parse URL params for initial state
        $initial_state = $this->parse_url_state();

        // Set initial state in store
        wp_interactivity_state('wooflux/filters', [
            'filters'       => $initial_state,
            'isLoading'     => false,
            'productsHtml'  => '',
            'totalProducts' => 0,
            'currentPage'   => 1,
        ]);

        ob_start();
        include WOOFLUX_DIR . 'templates/filter-panel.php';
        return ob_get_clean();
    }

    public function render_product_grid(array $attributes, string $content, WP_Block $block): string {
        // Run initial query (SSR — important for SEO and first load)
        $query_args = $this->build_query_from_url();
        $products   = (new WooFlux_Query())->run($query_args);

        ob_start();
        include WOOFLUX_DIR . 'templates/product-grid.php';
        return ob_get_clean();
    }

    private function parse_url_state(): array {
        return [
            'categories' => array_map('intval', (array) ($_GET['wf_cat'] ?? [])),
            'priceMin'   => (float) ($_GET['wf_price_min'] ?? 0),
            'priceMax'   => (float) ($_GET['wf_price_max'] ?? 0),
            'onSale'     => (bool) ($_GET['wf_sale'] ?? false),
            'inStock'    => (bool) ($_GET['wf_stock'] ?? false),
            'attributes' => $this->parse_url_attributes(),
            'rating'     => (int) ($_GET['wf_rating'] ?? 0),
        ];
    }

    private function get_categories(array $attributes): array {
        return get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $attributes['parentCategory'] ?? 0,
        ]);
    }

    private function get_price_range(): array {
        global $wpdb;
        $min = (float) $wpdb->get_var("SELECT MIN(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key='_price' AND meta_value != ''");
        $max = (float) $wpdb->get_var("SELECT MAX(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key='_price' AND meta_value != ''");
        return compact('min', 'max');
    }
}
```

### 7.4 Query Builder — `includes/class-wooflux-query.php`

```php
<?php
class WooFlux_Query {
    public function run(array $args): array {
        $query = new WC_Product_Query($this->build_args($args));
        $products = $query->get_products();

        return [
            'products' => $products,
            'total'    => $query->found_products ?? 0,
            'pages'    => $query->max_num_pages ?? 1,
        ];
    }

    public function build_args(array $filters): array {
        $args = [
            'status'         => 'publish',
            'limit'          => (int) get_option('wooflux_default_per_page', 12),
            'page'           => max(1, (int) ($filters['page'] ?? 1)),
            'paginate'       => true,
            'return'         => 'objects',
        ];

        // Category filter
        if (!empty($filters['categories'])) {
            $args['category'] = array_map(function($id) {
                $term = get_term($id, 'product_cat');
                return $term ? $term->slug : '';
            }, (array) $filters['categories']);
        }

        // Price filter
        if (!empty($filters['priceMin']) || !empty($filters['priceMax'])) {
            $args['min_price'] = $filters['priceMin'] ?? 0;
            if (!empty($filters['priceMax'])) {
                $args['max_price'] = $filters['priceMax'];
            }
        }

        // On-sale filter
        if (!empty($filters['onSale'])) {
            $args['include'] = wc_get_product_ids_on_sale();
        }

        // In-stock filter
        if (!empty($filters['inStock'])) {
            $args['stock_status'] = 'instock';
        }

        // Rating filter (Pro)
        if (!empty($filters['rating']) && wooflux_is_pro()) {
            $args['average_rating'] = (float) $filters['rating'];
        }

        // Attribute filters (Pro)
        if (!empty($filters['attributes']) && wooflux_is_pro()) {
            $args['tax_query'] = $this->build_attribute_tax_query($filters['attributes']);
        }

        return $args;
    }

    private function build_attribute_tax_query(array $attributes): array {
        $tax_query = ['relation' => 'AND'];

        foreach ($attributes as $taxonomy => $values) {
            if (empty($values)) continue;
            $tax_query[] = [
                'taxonomy' => sanitize_key($taxonomy),
                'field'    => 'slug',
                'terms'    => array_map('sanitize_text_field', (array) $values),
                'operator' => 'IN',
            ];
        }

        return $tax_query;
    }
}
```

### 7.5 REST API — `includes/class-wooflux-rest.php`

```php
<?php
class WooFlux_REST {
    public function register_routes(): void {
        register_rest_route('wooflux/v1', '/products', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_products'],
            'permission_callback' => '__return_true',   // Public — products are public
            'args'                => $this->get_request_args(),
        ]);
    }

    public function get_products(WP_REST_Request $request): WP_REST_Response {
        // Check cache
        $cache_key = 'wooflux_' . md5(serialize($request->get_params()));
        $cached    = get_transient($cache_key);

        if (false !== $cached) {
            return new WP_REST_Response($cached, 200);
        }

        // Build filters from request
        $filters = [
            'categories' => array_map('intval', (array) $request->get_param('categories')),
            'priceMin'   => (float) $request->get_param('price_min'),
            'priceMax'   => (float) $request->get_param('price_max'),
            'onSale'     => (bool) $request->get_param('on_sale'),
            'inStock'    => (bool) $request->get_param('in_stock'),
            'attributes' => (array) $request->get_param('attributes'),
            'rating'     => (int) $request->get_param('rating'),
            'page'       => (int) $request->get_param('page'),
        ];

        // Run query
        $result = (new WooFlux_Query())->run($filters);

        // Render product HTML server-side
        $html = $this->render_products($result['products']);

        $response = [
            'html'  => $html,
            'total' => $result['total'],
            'pages' => $result['pages'],
        ];

        // Cache response
        set_transient($cache_key, $response, (int) get_option('wooflux_cache_ttl', 300));

        return new WP_REST_Response($response, 200);
    }

    private function render_products(array $products): string {
        if (empty($products)) {
            return '<p class="wooflux-no-products">' . esc_html__('No products found.', 'wooflux') . '</p>';
        }

        ob_start();
        foreach ($products as $product) {
            include WOOFLUX_DIR . 'templates/product-card.php';
        }
        return ob_get_clean();
    }

    private function get_request_args(): array {
        return [
            'categories' => ['type' => 'array',   'items' => ['type' => 'integer'], 'default' => []],
            'price_min'  => ['type' => 'number',  'default' => 0,     'minimum' => 0],
            'price_max'  => ['type' => 'number',  'default' => 0,     'minimum' => 0],
            'on_sale'    => ['type' => 'boolean', 'default' => false],
            'in_stock'   => ['type' => 'boolean', 'default' => false],
            'attributes' => ['type' => 'object',  'default' => []],
            'rating'     => ['type' => 'integer', 'default' => 0,     'minimum' => 0, 'maximum' => 5],
            'page'       => ['type' => 'integer', 'default' => 1,     'minimum' => 1],
        ];
    }
}
```

---

## 8. JavaScript — Interactivity API

### 8.1 Shared Store — `src/store.js`

```javascript
import { store, getContext, getElement } from "@wordpress/interactivity";

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
    productsHtml: "",
    totalProducts: 0,
    currentPage: 1,
  },

  actions: {
    toggleCategory(event) {
      const termId = parseInt(event.target.value, 10);
      const index = state.filters.categories.indexOf(termId);
      if (index === -1) {
        state.filters.categories = [...state.filters.categories, termId];
      } else {
        state.filters.categories = state.filters.categories.filter((id) => id !== termId);
      }
    },

    setPriceMin(event) {
      state.filters.priceMin = parseFloat(event.target.value) || 0;
    },

    setPriceMax(event) {
      state.filters.priceMax = parseFloat(event.target.value) || 0;
    },

    toggleOnSale() {
      state.filters.onSale = !state.filters.onSale;
    },

    toggleInStock() {
      state.filters.inStock = !state.filters.inStock;
    },

    toggleAttribute(event) {
      const { taxonomy, value } = getContext();
      const current = state.filters.attributes[taxonomy] || [];
      const index = current.indexOf(value);
      state.filters.attributes = {
        ...state.filters.attributes,
        [taxonomy]: index === -1
          ? [...current, value]
          : current.filter((v) => v !== value),
      };
    },

    setRating(event) {
      state.filters.rating = parseInt(event.target.value, 10) || 0;
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
    },

    *fetchProducts() {
      state.isLoading = true;

      const config = wp.interactivity.getConfig("wooflux/filters");
      const params = buildQueryParams(state.filters, state.currentPage);

      try {
        const url = `${config.restUrl}?${params.toString()}`;
        const response = yield fetch(url, {
          headers: { "X-WP-Nonce": config.nonce },
        });

        if (!response.ok) throw new Error("Filter request failed");

        const data = yield response.json();

        state.productsHtml = data.html;
        state.totalProducts = data.total;

        // Update URL for shareability
        if (get_option("wooflux_enable_url_sync")) {
          const newUrl = `${window.location.pathname}?${params.toString()}`;
          history.pushState({ wooflux: state.filters }, "", newUrl);
        }
      } catch (error) {
        console.error("WooFlux filter error:", error);
      } finally {
        state.isLoading = false;
      }
    },
  },

  callbacks: {
    // Watches filters object — fires fetchProducts on any filter change
    onFiltersChange() {
      // Debounce: wait 300ms before fetching (prevents rapid firing on price input)
      clearTimeout(window.__woofluxDebounce);
      window.__woofluxDebounce = setTimeout(() => {
        actions.fetchProducts();
      }, 300);
    },
  },
});

// Helper: convert state.filters to URLSearchParams
function buildQueryParams(filters, page) {
  const params = new URLSearchParams();
  if (filters.categories.length) params.set("categories", filters.categories.join(","));
  if (filters.priceMin) params.set("price_min", filters.priceMin);
  if (filters.priceMax) params.set("price_max", filters.priceMax);
  if (filters.onSale) params.set("on_sale", "1");
  if (filters.inStock) params.set("in_stock", "1");
  if (filters.rating) params.set("rating", filters.rating);
  if (page > 1) params.set("page", page);

  // Attribute filters
  Object.entries(filters.attributes).forEach(([tax, values]) => {
    if (values.length) params.set(`attr_${tax}`, values.join(","));
  });

  return params;
}
```

### 8.2 Filter Panel View — `blocks/filter-panel/src/view.js`

```javascript
// Import shared store (registers it)
import "../../src/store.js";

// The filter panel view.js only needs to exist to import the store.
// All interaction is handled by data-wp-* directives in render.php
// and the shared store in src/store.js.
```

### 8.3 Product Grid View — `blocks/product-grid/src/view.js`

```javascript
import { store } from "@wordpress/interactivity";
// Import shared store
import "../../src/store.js";

// Product grid only needs to watch for productsHtml changes.
// The data-wp-html directive on the grid container handles DOM updates.
// This file can be minimal — all logic is in the shared store.
```

---

## 9. REST API Endpoints

### 9.1 GET `/wp-json/wooflux/v1/products`

**Authentication:** None required (products are public). Nonce included for cache-busting and future Pro endpoints.

**Query Parameters:**

| Parameter | Type | Default | Description |
|---|---|---|---|
| `categories` | string | `""` | Comma-separated category term IDs |
| `price_min` | float | `0` | Minimum price |
| `price_max` | float | `0` | Maximum price (0 = no limit) |
| `on_sale` | bool | `false` | Show only on-sale products |
| `in_stock` | bool | `false` | Show only in-stock products |
| `attributes` | object | `{}` | `{ pa_color: "red,blue", pa_size: "M,L" }` |
| `rating` | int | `0` | Minimum average rating (0 = no filter) |
| `page` | int | `1` | Pagination page number |

**Response:**

```json
{
  "html": "<ul class=\"wooflux-products\">...</ul>",
  "total": 47,
  "pages": 4
}
```

**Error Response:**

```json
{
  "code": "wooflux_query_error",
  "message": "Invalid filter parameters",
  "data": { "status": 400 }
}
```

**Caching:** Responses cached in WordPress transients for 300 seconds (configurable). Cache key = MD5 of all query params. Invalidated on product save or stock change.

---

## 10. Admin Settings Panel

### 10.1 Settings Page Location

`WooCommerce → WooFlux Settings` (added under WooCommerce menu, not top-level)

### 10.2 Settings Fields

| Setting | Type | Default | Description |
|---|---|---|---|
| Products per page | number | `12` | Default products per filter result |
| Cache duration | number | `300` | REST response cache in seconds |
| Sync filters to URL | toggle | `on` | Push filter state to browser URL |
| No results message | text | `"No products found."` | Shown when filter returns 0 products |
| Loading indicator | select | `spinner` | spinner / skeleton / none |

### 10.3 Pro Settings (visible but locked in free)

| Setting | Type | Description |
|---|---|---|
| Color swatch taxonomy | select | Which attribute to use for color swatches |
| Size swatch taxonomy | select | Which attribute to use for size swatches |
| Enable price slider | toggle | Range slider instead of inputs |
| Enable rating filter | toggle | Star rating filter |

Locked settings show a "Upgrade to Pro" badge and link to Freemius checkout.

---

## 11. Frontend UI Specification

### 11.1 Filter Panel HTML Template — `templates/filter-panel.php`

```php
<?php
// $categories, $price_range, $initial_state available from render callback
?>
<div
  class="wooflux-filter-panel"
  data-wp-interactive="wooflux/filters"
  data-wp-watch="callbacks.onFiltersChange"
>

  <?php if (!empty($categories)) : ?>
  <div class="wooflux-filter-group">
    <h3 class="wooflux-filter-title"><?php esc_html_e('Categories', 'wooflux'); ?></h3>
    <ul class="wooflux-categories">
      <?php foreach ($categories as $cat) : ?>
      <li>
        <label>
          <input
            type="checkbox"
            value="<?php echo esc_attr($cat->term_id); ?>"
            data-wp-on--change="actions.toggleCategory"
            data-wp-bind--checked="state.filters.categories.includes(<?php echo (int) $cat->term_id; ?>)"
          />
          <?php echo esc_html($cat->name); ?>
          <span class="wooflux-count">(<?php echo (int) $cat->count; ?>)</span>
        </label>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="wooflux-filter-group">
    <h3 class="wooflux-filter-title"><?php esc_html_e('Price', 'wooflux'); ?></h3>
    <div class="wooflux-price-range">
      <input
        type="number"
        placeholder="<?php esc_attr_e('Min', 'wooflux'); ?>"
        data-wp-on--change="actions.setPriceMin"
        data-wp-bind--value="state.filters.priceMin"
        min="0"
        step="1"
      />
      <span>—</span>
      <input
        type="number"
        placeholder="<?php esc_attr_e('Max', 'wooflux'); ?>"
        data-wp-on--change="actions.setPriceMax"
        data-wp-bind--value="state.filters.priceMax"
        min="0"
        step="1"
      />
    </div>
  </div>

  <div class="wooflux-filter-group">
    <label>
      <input
        type="checkbox"
        data-wp-on--change="actions.toggleOnSale"
        data-wp-bind--checked="state.filters.onSale"
      />
      <?php esc_html_e('On sale', 'wooflux'); ?>
    </label>
    <label>
      <input
        type="checkbox"
        data-wp-on--change="actions.toggleInStock"
        data-wp-bind--checked="state.filters.inStock"
      />
      <?php esc_html_e('In stock only', 'wooflux'); ?>
    </label>
  </div>

  <button
    class="wooflux-reset"
    data-wp-on--click="actions.resetFilters"
    data-wp-bind--disabled="state.isLoading"
  >
    <?php esc_html_e('Reset filters', 'wooflux'); ?>
  </button>

</div>
```

### 11.2 Product Grid HTML Template — `templates/product-grid.php`

```php
<div
  class="wooflux-product-grid"
  data-wp-interactive="wooflux/filters"
>
  <!-- Loading overlay -->
  <div
    class="wooflux-loading"
    data-wp-bind--hidden="!state.isLoading"
    aria-live="polite"
    aria-label="<?php esc_attr_e('Loading products', 'wooflux'); ?>"
  >
    <span class="wooflux-spinner"></span>
  </div>

  <!-- Products container — updated by Interactivity API -->
  <div
    class="wooflux-products-container"
    data-wp-html="state.productsHtml || context.initialHtml"
  >
    <!-- SSR initial render (populated by PHP on first load) -->
    <?php foreach ($products['products'] as $product) : ?>
      <?php include WOOFLUX_DIR . 'templates/product-card.php'; ?>
    <?php endforeach; ?>
  </div>

  <!-- Results count -->
  <p class="wooflux-results-count" data-wp-text="state.totalProducts + ' products'">
    <?php echo (int) $products['total']; ?> <?php esc_html_e('products', 'wooflux'); ?>
  </p>

</div>
```

### 11.3 CSS Variables (for theme compatibility)

```css
/* blocks/filter-panel/src/style.css */
.wooflux-filter-panel {
  --wooflux-accent:        var(--wp--preset--color--primary, #0073aa);
  --wooflux-text:          var(--wp--preset--color--foreground, #1e1e1e);
  --wooflux-border:        #ddd;
  --wooflux-border-radius: 4px;
  --wooflux-spacing:       1rem;
}
```

All colors use CSS custom properties that fall back to WordPress theme presets, then hardcoded defaults. This ensures compatibility with any FSE theme.

---

## 12. Freemium & Licensing

### 12.1 Free vs Pro Feature Map

| Feature | Free | Pro |
|---|---|---|
| Category filter | ✅ | ✅ |
| Price range (inputs) | ✅ | ✅ |
| On-sale filter | ✅ | ✅ |
| In-stock filter | ✅ | ✅ |
| URL sync | ✅ | ✅ |
| SSR / SEO safe | ✅ | ✅ |
| Color swatches | ❌ | ✅ |
| Size swatches | ❌ | ✅ |
| Price range slider | ❌ | ✅ |
| Rating filter | ❌ | ✅ |
| Custom attribute filters | ❌ | ✅ |
| Priority email support | ❌ | ✅ |

### 12.2 Gating Implementation

```php
// includes/class-wooflux-freemius.php

function wooflux_is_pro(): bool {
    if (!function_exists('wooflux_fs')) return false;
    return wooflux_fs()->is_plan('pro', true);
}

// In query builder — gate Pro features:
if (!empty($filters['rating']) && wooflux_is_pro()) {
    $args['average_rating'] = (float) $filters['rating'];
}

// In REST endpoint — ignore Pro params if not Pro:
$rating = wooflux_is_pro() ? (int) $request->get_param('rating') : 0;
```

### 12.3 Pricing

| Plan | Price | License |
|---|---|---|
| Free | $0 | Unlimited sites |
| Pro — Single Site | $69/year | 1 site |
| Pro — 5 Sites | $99/year | 5 sites |
| Pro — Unlimited | $149/year | Unlimited sites |

All Pro plans: annual subscription, auto-renews, cancel anytime.

---

## 13. Build System & Tooling

### 13.1 `package.json`

```json
{
  "name": "wooflux",
  "version": "1.0.0",
  "scripts": {
    "build":   "wp-scripts build   src/store.js blocks/filter-panel/src/index.js blocks/product-grid/src/index.js",
    "start":   "wp-scripts start   src/store.js blocks/filter-panel/src/index.js blocks/product-grid/src/index.js",
    "test:js": "wp-scripts test-unit-js",
    "test:e2e":"wp-scripts test-playwright",
    "lint:js": "wp-scripts lint-js",
    "lint:css":"wp-scripts lint-style",
    "lint:php":"composer run-script lint",
    "packages-update": "wp-scripts packages-update"
  },
  "devDependencies": {
    "@wordpress/scripts": "^30.0.0"
  }
}
```

> **IMPORTANT:** Build command must include `--experimental-modules` flag for Interactivity API Script Modules to work correctly. Add to wp-scripts config:

```json
"wp-scripts": {
  "webpack-config": "webpack.config.js"
}
```

```javascript
// webpack.config.js
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
module.exports = {
  ...defaultConfig,
  entry: {
    ...defaultConfig.entry(),
    'store': './src/store.js',
  }
};
```

### 13.2 `block.json` — Filter Panel

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "wooflux/filter-panel",
  "version": "1.0.0",
  "title": "WooFlux Filter Panel",
  "category": "woocommerce",
  "description": "Interactive product filter panel for WooCommerce built on the Interactivity API.",
  "keywords": ["woocommerce", "filter", "products", "shop"],
  "textdomain": "wooflux",
  "supports": {
    "html": false,
    "interactivity": true
  },
  "attributes": {
    "parentCategory": {
      "type": "integer",
      "default": 0
    },
    "showCategories": {
      "type": "boolean",
      "default": true
    },
    "showPrice": {
      "type": "boolean",
      "default": true
    },
    "showOnSale": {
      "type": "boolean",
      "default": true
    },
    "showInStock": {
      "type": "boolean",
      "default": true
    }
  },
  "viewScriptModule": "file:./build/view.js",
  "editorScript": "file:./build/index.js",
  "style": "file:./build/style-index.css"
}
```

### 13.3 `composer.json`

```json
{
  "name": "yourname/wooflux",
  "type": "wordpress-plugin",
  "require": {
    "php": ">=8.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "brain/monkey": "^2.6",
    "squizlabs/php_codesniffer": "^3.7",
    "wp-coding-standards/wpcs": "^3.0"
  },
  "scripts": {
    "lint": "phpcs --standard=WordPress includes/ blocks/",
    "test":  "phpunit"
  },
  "autoload": {
    "classmap": ["includes/"]
  }
}
```

---

## 14. WordPress.org Submission

### 14.1 `readme.txt` Structure (required)

```
=== WooFlux — Live Product Filter ===
Contributors:      yourwpusername
Tags:              woocommerce, product filter, ajax filter, shop filter, interactivity api
Requires at least: 6.5
Tested up to:      7.0
Requires PHP:      8.0
WC requires at least: 8.0
WC tested up to:   10.x
Stable tag:        1.0.0
License:           GPLv2 or later

Lightning-fast WooCommerce product filtering built on the WordPress Interactivity API. Zero jQuery. No conflicts.

== Description ==
...

== Installation ==
...

== Frequently Asked Questions ==
...

== Screenshots ==
1. Filter panel in sidebar layout
2. Horizontal filter bar layout
3. Admin settings page
4. Color and size swatches (Pro)

== Changelog ==
= 1.0.0 =
* Initial release
```

### 14.2 SVN Structure for WordPress.org

```
/trunk/          ← current development version
/tags/1.0.0/     ← released version (copy of trunk at release time)
/assets/         ← plugin page assets (NOT plugin files)
  banner-772x250.png
  banner-1544x500.png
  icon-128x128.png
  icon-256x256.png
  screenshot-1.png
  screenshot-2.png
```

### 14.3 Review Checklist Before Submission

- [ ] No `die()` or `exit()` without message
- [ ] All user-facing strings wrapped in `__()` / `_e()` with `'wooflux'` text domain
- [ ] All database queries use `$wpdb->prepare()`
- [ ] All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- [ ] All nonces verified on form submissions
- [ ] `uninstall.php` cleans up all `wp_options` entries
- [ ] No hardcoded URLs — use `plugin_dir_url()`, `plugin_dir_path()`
- [ ] No calls to external APIs without disclosure
- [ ] `readme.txt` complete with all required fields
- [ ] Tested on WordPress.org Plugin Check plugin (no errors)

---

## 15. Freemius Integration (Pro)

### 15.1 Freemius Bootstrap — `wooflux.php`

```php
// After plugin constants, before WooFlux class init:
if (!function_exists('wooflux_fs')) {
    function wooflux_fs() {
        global $wooflux_fs;
        if (!isset($wooflux_fs)) {
            require_once WOOFLUX_DIR . 'freemius/start.php';
            $wooflux_fs = fs_dynamic_init([
                'id'             => 'YOUR_FREEMIUS_PRODUCT_ID',
                'slug'           => 'wooflux',
                'type'           => 'plugin',
                'public_key'     => 'pk_YOUR_PUBLIC_KEY',
                'is_premium'     => false,   // true in Pro build
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => [
                    'slug'       => 'wooflux',
                    'parent'     => ['slug' => 'woocommerce'],
                ],
            ]);
        }
        return $wooflux_fs;
    }
    wooflux_fs();
    do_action('wooflux_fs_loaded');
}
```

### 15.2 Freemius Setup Steps

1. Create account at freemius.com
2. Add new product → type: Plugin → name: WooFlux
3. Copy Product ID and Public Key → paste into bootstrap above
4. Set up pricing plans (Single $69, 5 Sites $99, Unlimited $149)
5. Set annual billing period
6. Download Freemius SDK → place in `/freemius/` directory
7. Add `/freemius/` to `.gitignore` (distribute separately)

---

## 16. Testing Strategy

### 16.1 PHP Unit Tests

```php
// tests/php/test-query.php
class Test_WooFlux_Query extends WP_UnitTestCase {

    public function test_category_filter_builds_correct_args(): void {
        $query = new WooFlux_Query();
        $args  = $query->build_args(['categories' => [5, 12]]);

        $this->assertArrayHasKey('category', $args);
        $this->assertCount(2, $args['category']);
    }

    public function test_price_filter_sets_min_max(): void {
        $query = new WooFlux_Query();
        $args  = $query->build_args(['priceMin' => 10, 'priceMax' => 100]);

        $this->assertEquals(10,  $args['min_price']);
        $this->assertEquals(100, $args['max_price']);
    }

    public function test_pro_rating_filter_ignored_in_free(): void {
        $query = new WooFlux_Query();
        $args  = $query->build_args(['rating' => 4]);

        $this->assertArrayNotHasKey('average_rating', $args);
    }
}
```

### 16.2 JS Store Tests

```javascript
// tests/js/store.test.js
import { buildQueryParams } from "../../src/store.js";

describe("buildQueryParams", () => {
  it("includes category IDs when set", () => {
    const filters = { categories: [5, 12], priceMin: 0, priceMax: 0, onSale: false, inStock: false };
    const params = buildQueryParams(filters, 1);
    expect(params.get("categories")).toBe("5,12");
  });

  it("omits empty filters", () => {
    const filters = { categories: [], priceMin: 0, priceMax: 0, onSale: false, inStock: false };
    const params = buildQueryParams(filters, 1);
    expect(params.has("categories")).toBe(false);
    expect(params.has("price_min")).toBe(false);
  });
});
```

### 16.3 E2E Tests (Playwright)

```javascript
// tests/e2e/filter.spec.js
import { test, expect } from "@wordpress/e2e-test-utils-playwright";

test("Category filter updates products", async ({ page, admin, editor }) => {
  await admin.visitAdminPage("post-new.php");
  // Add filter-panel and product-grid blocks
  // ...

  await page.goto("/shop-page/");

  // Click a category checkbox
  await page.click('[data-wp-on--change="actions.toggleCategory"]');

  // Wait for loading to finish
  await page.waitForSelector(".wooflux-loading[hidden]");

  // Verify URL updated
  expect(page.url()).toContain("wf_cat=");

  // Verify products updated
  const products = page.locator(".wooflux-products-container .product");
  expect(await products.count()).toBeGreaterThan(0);
});
```

### 16.4 Manual QA Checklist

- [ ] Install on fresh WordPress + WooCommerce, no other plugins
- [ ] Add 20+ products across 3+ categories with different prices
- [ ] Filter by category → products update, URL changes
- [ ] Filter by price range → correct products shown
- [ ] Toggle on-sale → only sale products shown
- [ ] Toggle in-stock → only in-stock products shown
- [ ] Reset filters → all products shown
- [ ] Copy filtered URL, open in new tab → same filter state loads
- [ ] Press browser Back → previous filter state restored
- [ ] Test with popular page builders: Elementor, Bricks, Kadence
- [ ] Test with popular caching plugins: WP Rocket, LiteSpeed Cache
- [ ] Test with HPOS (High-Performance Order Storage) enabled
- [ ] Validate no jQuery loaded by WooFlux (check Network tab)
- [ ] Check accessibility: keyboard navigation through filters
- [ ] Check mobile: filters usable on 375px viewport

---

## 17. Development Phases

### Phase 1 — MVP Free (weeks 1–4)

#### Week 1 — Setup & Structure
- [ ] Initialize plugin with `wooflux.php` headers and constants
- [ ] Create file structure (all directories and empty files)
- [ ] Set up `package.json` with `@wordpress/scripts`
- [ ] Set up `composer.json` with PHPUnit + PHPCS
- [ ] Run `npm run build` — verify no errors
- [ ] Scaffold both blocks with `@wordpress/create-block-interactive-template`
- [ ] Register blocks — verify they appear in block inserter

#### Week 2 — Filter Panel
- [ ] Implement `WooFlux_Blocks::render_filter_panel()` PHP
- [ ] Build category filter HTML with `data-wp-*` directives
- [ ] Build price range filter HTML
- [ ] Build on-sale and in-stock checkboxes
- [ ] Implement shared store `src/store.js` with all actions
- [ ] Implement `WooFlux_Query::build_args()` for all free filter types
- [ ] Verify: clicking checkbox updates `state.filters` (use browser DevTools → WP Interactivity tab)

#### Week 3 — REST API & Product Grid
- [ ] Implement `WooFlux_REST::get_products()` endpoint
- [ ] Implement `WooFlux_REST::render_products()` with product card template
- [ ] Implement `templates/product-card.php` matching WooCommerce default markup
- [ ] Implement `store.js actions.fetchProducts()` async generator
- [ ] Connect `callbacks.onFiltersChange` → debounced `fetchProducts`
- [ ] Implement `templates/product-grid.php` with `data-wp-html` directive
- [ ] Verify end-to-end: filter click → REST call → products update
- [ ] Implement URL sync (`history.pushState`)
- [ ] Implement initial state from URL params (SSR)

#### Week 4 — Admin, Polish, Tests
- [ ] Implement `WooFlux_Settings` admin page under WooCommerce menu
- [ ] Implement transient caching in REST endpoint
- [ ] Implement cache invalidation on product save/stock change
- [ ] Write PHPUnit tests for `WooFlux_Query`
- [ ] Write Jest tests for `buildQueryParams`
- [ ] Write basic Playwright E2E test
- [ ] Run PHPCS linting — fix all violations
- [ ] Run Plugin Check (WordPress.org checker) — fix all errors
- [ ] Complete `readme.txt`
- [ ] Create plugin assets (banner, icon, screenshots)
- [ ] Submit to WordPress.org

### Phase 2 — Pro Features (weeks 5–8)

#### Week 5–6 — Freemius + Pro Filters
- [ ] Set up Freemius account and product
- [ ] Integrate Freemius SDK into plugin
- [ ] Implement `wooflux_is_pro()` gate function
- [ ] Build color swatch filter block (Pro)
- [ ] Build size swatch filter block (Pro)
- [ ] Build rating filter (Pro)
- [ ] Build custom attribute filter (Pro)
- [ ] Gate all Pro features behind `wooflux_is_pro()` check

#### Week 7 — Pro Polish
- [ ] Build price range slider (Pro) — native HTML `<input type="range">` with dual handles
- [ ] Lock Pro settings in admin with "Upgrade" CTA
- [ ] Build upgrade prompt in block editor sidebar for Pro features
- [ ] Build `wooflux.com` landing page with pricing table

#### Week 8 — Launch
- [ ] Announce on WordPress subreddit (r/Wordpress, r/WooCommerce)
- [ ] Post on MasterWP, WP Tavern, Post Status community
- [ ] Create demo video (Loom) showing filter in action vs YITH
- [ ] Email 10 WordPress agencies directly with demo link
- [ ] Submit to WooCommerce.com marketplace (extension listing)

---

## 18. Security Checklist

### 18.1 Input Sanitization

Every piece of user input must be sanitized before use:

```php
// REST parameters — use schema validation (already done in register_routes args)
// But also sanitize manually as defense-in-depth:
$categories = array_map('absint',         (array) $request->get_param('categories'));
$price_min  = abs((float)                  $request->get_param('price_min'));
$price_max  = abs((float)                  $request->get_param('price_max'));
$on_sale    = (bool)                       $request->get_param('on_sale');
$attributes = array_map('sanitize_key',   array_keys((array) $request->get_param('attributes')));
```

### 18.2 Output Escaping

```php
// In templates — always escape:
echo esc_html($category->name);
echo esc_attr($category->term_id);
echo esc_url(get_term_link($category));

// Product HTML from WooCommerce functions is trusted:
// wc_get_template_html() output is safe — do not double-escape
```

### 18.3 Nonce Verification

REST API uses WP REST API nonce (`X-WP-Nonce` header) — automatically verified by WordPress core for authenticated requests. Public endpoints (product listing) do not require authentication — this is correct and intentional, products are public.

### 18.4 Capability Checks

```php
// Admin settings save:
if (!current_user_can('manage_woocommerce')) {
    wp_die(esc_html__('Unauthorized', 'wooflux'));
}
```

### 18.5 SQL Injection

Never write raw SQL. Always use:
- `WC_Product_Query` for product queries
- `WP_Query` for post queries
- `$wpdb->prepare()` for any custom queries (only price range min/max)

---

## 19. Open Questions

| # | Question | Options | Priority |
|---|---|---|---|
| 1 | Should free version support more than one filter panel per page? | A) Yes (multiple instances) B) No (single filter) | Medium |
| 2 | Product card template — match WooCommerce default or custom? | A) Match WooCommerce (better compat) B) Custom (better control) | High |
| 3 | Pagination — reload full page or AJAX paginate? | A) Full page reload (simpler, MVP) B) AJAX pagination (better UX, Phase 2) | High — decide before Week 3 |
| 4 | Loading state — spinner or skeleton screen? | A) CSS spinner (simpler) B) Skeleton (better UX) | Low |
| 5 | WooCommerce.com extension listing — pursue? | A) Yes (more distribution) B) No (complex review) | Medium |
| 6 | Plugin slug — `wooflux` or `wooflux-product-filter`? | A) Short: wooflux B) Descriptive: wooflux-product-filter | High — needed for WP.org submission |

---

## 20. Glossary

| Term | Definition |
|---|---|
| Interactivity API | Native WordPress API (since 6.5) for adding reactive client-side behavior to blocks via `data-wp-*` HTML directives without external JS frameworks |
| Block | A Gutenberg content element registered with `block.json` and `register_block_type()` |
| `block.json` | JSON metadata file that defines a block's name, attributes, scripts, and styles |
| `view.js` | Frontend JavaScript file for a block — runs on the published page (not in editor) |
| `index.js` | Editor JavaScript file for a block — runs only in Gutenberg editor |
| `render.php` | Server-side PHP render callback — generates initial HTML for dynamic blocks |
| Store | Interactivity API concept: shared reactive state object accessible by multiple blocks via a namespace (e.g. `wooflux/filters`) |
| SSR | Server-Side Rendering — generating HTML on the server (PHP) before sending to browser. Critical for SEO and first-load performance |
| FSE | Full Site Editing — WordPress's modern site-building approach using block themes |
| HPOS | High-Performance Order Storage — WooCommerce's modern order database system (custom tables instead of `wp_posts`) |
| Freemius | SaaS platform for selling WordPress plugins: handles licensing, payments, subscriptions, EU VAT |
| `pa_*` | WordPress taxonomy naming convention for WooCommerce product attributes (e.g. `pa_color`, `pa_size`) |
| Transient | WordPress time-limited cache stored in `wp_options`. Used for caching REST responses |
| `wp_interactivity_config()` | PHP function to pass configuration data from server to Interactivity API store |
| `wp_interactivity_state()` | PHP function to set initial state for Interactivity API store (used for SSR state hydration) |

---

*End of Document — WooFlux Technical Specification v1.0*