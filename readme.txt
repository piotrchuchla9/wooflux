=== Live Product Filter — WooFlux ===
Contributors:      piotrusio
Tags:              woocommerce, product filter, ajax filter, shop filter, interactivity api
Requires at least: 6.5
Tested up to:      7.0
Requires PHP:      8.0
WC requires at least: 8.0
WC tested up to:   10.x
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Lightning-fast WooCommerce product filtering built on the WordPress Interactivity API. Zero jQuery. No conflicts.

== Description ==

WooFlux replaces jQuery-based AJAX filtering with a native, reactive approach built on the WordPress Interactivity API (shipped in WordPress 6.5). Instant UI updates, zero jQuery dependency, no conflicts with other plugins, and full SEO preservation via server-side rendering.

**Free Features:**

* Category filter — checkbox list of WooCommerce product categories
* Price range filter — min/max input fields with live validation
* On-sale filter — show only discounted products
* In-stock filter — show only available products
* URL sync — active filters reflected in browser URL for shareability
* SSR / SEO safe — initial render is server-side PHP

**Pro Features:**

* Color swatch filter
* Size swatch filter
* Price range slider
* Rating filter
* Custom attribute filters
* Priority support

== Installation ==

1. Upload the `wooflux` folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu
3. Go to any page and add the **WooFlux Filter Panel** and **WooFlux Product Grid** blocks
4. Publish the page — filters are live immediately

== Frequently Asked Questions ==

= Does WooFlux require jQuery? =
No. WooFlux is built entirely on the WordPress Interactivity API and loads zero jQuery.

= What WordPress version is required? =
WordPress 6.5 or higher (required for the Interactivity API).

= What WooCommerce version is required? =
WooCommerce 8.0 or higher.

= Does it work with Full Site Editing (FSE) themes? =
Yes — WooFlux is built with FSE compatibility as a core goal.

== Screenshots ==

1. WooFlux filter panel with category, price, on-sale and in-stock filters alongside the reactive product grid.
3. Admin settings page
4. Color and size swatches (Pro)

== Changelog ==

= 1.0.0 =
* Initial release
