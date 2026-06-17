<?php
/**
 * Gutenberg block registration and server-side rendering.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers both WooFlux blocks and provides their render callbacks.
 */
class WooFlux_Blocks {

	/**
	 * Registers the filter-panel and product-grid blocks and their style variations.
	 */
	public function register(): void {
		register_block_type(
			WOOFLUX_DIR . 'build/filter-panel',
			array(
				'render_callback' => array( $this, 'render_filter_panel' ),
			)
		);

		register_block_type(
			WOOFLUX_DIR . 'build/product-grid',
			array(
				'render_callback' => array( $this, 'render_product_grid' ),
			)
		);

		// Strip any is-style-* classes saved in the block editor from old style variant selections.
		$strip_old_styles = static function ( string $content ): string {
			return preg_replace( '/\bis-style-\S+\s*/', '', $content );
		};
		add_filter( 'render_block_wooflux/filter-panel', $strip_old_styles );
		add_filter( 'render_block_wooflux/product-grid', $strip_old_styles );

		add_action( 'wp_head', array( $this, 'output_css_variables' ) );
	}

	/**
	 * Outputs WooFlux CSS custom properties into <head>.
	 * Light mode at :root, dark mode inside a prefers-color-scheme media query.
	 */
	public function output_css_variables(): void {
		$defaults = WooFlux_Settings::COLOR_DEFAULTS;
		$light    = array();
		$dark     = array();

		foreach ( $defaults['light'] as $key => $default ) {
			$val           = get_option( "wooflux_color_light_{$key}", $default );
			$light[ $key ] = $val ? $val : $default;
		}
		foreach ( $defaults['dark'] as $key => $default ) {
			$val          = get_option( "wooflux_color_dark_{$key}", $default );
			$dark[ $key ] = $val ? $val : $default;
		}

		// Light mode always includes bg: transparent (not user-configurable).
		$light_vars = '--wf-bg:transparent;';
		foreach ( $light as $key => $val ) {
			$light_vars .= "--wf-{$key}:" . esc_attr( $val ) . ';';
		}

		$dark_vars = '';
		foreach ( $dark as $key => $val ) {
			$dark_vars .= "--wf-{$key}:" . esc_attr( $val ) . ';';
		}

		printf(
			'<style id="wooflux-vars">:root{%s}@media(prefers-color-scheme:dark){:root{%s}}</style>' . "\n",
			$light_vars, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$dark_vars   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Server-side render callback for the filter-panel block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_filter_panel( array $attributes ): string {
		$categories         = $this->get_categories( $attributes );
		$price_range        = $this->get_price_range();
		$initial_state      = $this->parse_url_state();
		$wrapper_attributes = get_block_wrapper_attributes(
			array( 'class' => 'wooflux-filter-panel' )
		);

		wp_interactivity_config(
			'wooflux/filters',
			array(
				'restUrl'       => rest_url( 'wooflux/v1/products' ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'currency'      => get_woocommerce_currency_symbol(),
				'priceDecimals' => wc_get_price_decimals(),
				'isPro'         => wooflux_is_pro(),
				'enableUrlSync' => (bool) get_option( 'wooflux_enable_url_sync', true ),
			)
		);

		wp_interactivity_state(
			'wooflux/filters',
			array(
				'filters'     => $initial_state,
				'isLoading'   => false,
				'currentPage' => 1,
			)
		);

		ob_start();
		include WOOFLUX_DIR . 'templates/filter-panel.php';
		return ob_get_clean();
	}

	/**
	 * Server-side render callback for the product-grid block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_product_grid( array $attributes ): string {
		$layout             = ( 'list' === ( $attributes['layout'] ?? 'grid' ) ) ? 'is-layout-list' : '';
		$columns            = max( 1, (int) ( $attributes['columns'] ?? 3 ) );
		$query_args         = $this->build_query_from_url();
		$products           = ( new WooFlux_Query() )->run( $query_args );
		$products_html      = $this->render_products_to_html( $products['products'] );
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => trim( 'wooflux-product-grid ' . $layout ),
				'style' => "--wooflux-columns:{$columns}",
			)
		);

		wp_interactivity_state(
			'wooflux/filters',
			array(
				'productsHtml'   => $products_html,
				'totalProducts'  => $products['total'],
				'totalPages'     => $products['pages'],
				'currentPage'    => 1,
				'showPagination' => $products['pages'] > 1,
			)
		);

		ob_start();
		include WOOFLUX_DIR . 'templates/product-grid.php';
		return ob_get_clean();
	}

	/**
	 * Renders an array of WC_Product objects to HTML via the product-card template.
	 *
	 * @param WC_Product[] $products Array of product objects.
	 * @return string Rendered HTML.
	 */
	public function render_products_to_html( array $products ): string {
		if ( empty( $products ) ) {
			$message = get_option( 'wooflux_no_results_message', __( 'No products found.', 'live-product-filter-wooflux' ) );
			return '<p class="wooflux-no-products">' . esc_html( $message ) . '</p>';
		}

		ob_start();
		foreach ( $products as $product ) {
			include WOOFLUX_DIR . 'templates/product-card.php';
		}
		return ob_get_clean();
	}

	/**
	 * Parses URL query params into an initial filter state array.
	 *
	 * @return array
	 */
	private function parse_url_state(): array {
		$raw_cat    = isset( $_GET['wf_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['wf_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$categories = $raw_cat ? array_filter( array_map( 'intval', explode( ',', $raw_cat ) ) ) : array();
		return array(
			'categories' => array_values( $categories ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'priceMin'   => (float) ( isset( $_GET['wf_price_min'] ) ? sanitize_text_field( wp_unslash( $_GET['wf_price_min'] ) ) : 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'priceMax'   => (float) ( isset( $_GET['wf_price_max'] ) ? sanitize_text_field( wp_unslash( $_GET['wf_price_max'] ) ) : 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'onSale'     => ! empty( $_GET['wf_sale'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'inStock'    => ! empty( $_GET['wf_stock'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'attributes' => $this->parse_url_attributes(),
			'rating'     => (int) ( isset( $_GET['wf_rating'] ) ? sanitize_text_field( wp_unslash( $_GET['wf_rating'] ) ) : 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}

	/**
	 * Parses `wf_attr_*` URL params into an attributes array.
	 *
	 * @return array
	 */
	private function parse_url_attributes(): array {
		$attributes = array();
		foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( str_starts_with( $key, 'wf_attr_' ) ) {
				$taxonomy                = sanitize_key( substr( $key, 8 ) );
				$attributes[ $taxonomy ] = array_map( 'sanitize_text_field', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) );
			}
		}
		return $attributes;
	}

	/**
	 * Builds filter args from the current URL for the initial SSR query.
	 *
	 * @return array
	 */
	private function build_query_from_url(): array {
		return $this->parse_url_state();
	}

	/**
	 * Returns published product categories, optionally filtered by parent.
	 *
	 * @param array $attributes Block attributes (may include parentCategory).
	 * @return WP_Term[]
	 */
	private function get_categories( array $attributes ): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => (int) ( $attributes['parentCategory'] ?? 0 ),
			)
		);
		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Returns the min and max published product prices.
	 *
	 * @return array{ min: float, max: float }
	 */
	private function get_price_range(): array {
		global $wpdb;
		$min = (float) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT MIN(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
				'_price'
			)
		);
		$max = (float) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT MAX(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
				'_price'
			)
		);
		return compact( 'min', 'max' );
	}
}
