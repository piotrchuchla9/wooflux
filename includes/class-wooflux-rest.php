<?php
/**
 * REST API endpoint for filtered product queries.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the GET /wooflux/v1/products REST endpoint.
 */
class WooFlux_REST {

	/**
	 * Registers the REST route and cache invalidation hooks.
	 */
	public function register_routes(): void {
		register_rest_route(
			'wooflux/v1',
			'/products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_products' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_request_args(),
			)
		);

		add_action( 'save_post_product', array( $this, 'invalidate_cache' ) );
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'invalidate_cache' ) );
		add_action( 'wooflux_flush_cache', array( $this, 'invalidate_cache' ) );
	}

	/**
	 * Deletes all WooFlux transients from the options table.
	 * Hooked to product save, stock status change, and manual flush.
	 */
	public function invalidate_cache(): void {
		global $wpdb;
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_wooflux_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_wooflux_' ) . '%'
			)
		);
	}

	/**
	 * Handles the REST request: runs the filter query and returns rendered HTML.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response
	 */
	public function get_products( WP_REST_Request $request ): WP_REST_Response {
		$cache_key = 'wooflux_' . md5( serialize( $request->get_params() ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return new WP_REST_Response( $cached, 200 );
		}

		$raw_cats = $request->get_param( 'categories' );
		if ( is_string( $raw_cats ) && str_contains( $raw_cats, ',' ) ) {
			$raw_cats = explode( ',', $raw_cats );
		}
		$filters = array(
			'categories' => array_filter( array_map( 'absint', (array) $raw_cats ) ),
			'priceMin'   => abs( (float) $request->get_param( 'price_min' ) ),
			'priceMax'   => abs( (float) $request->get_param( 'price_max' ) ),
			'onSale'     => (bool) $request->get_param( 'on_sale' ),
			'inStock'    => (bool) $request->get_param( 'in_stock' ),
			'attributes' => (array) $request->get_param( 'attributes' ),
			'rating'     => wooflux_is_pro() ? (int) $request->get_param( 'rating' ) : 0,
			'page'       => (int) $request->get_param( 'page' ),
		);

		$safe_attributes = array();
		foreach ( $filters['attributes'] as $taxonomy => $values ) {
			$safe_attributes[ sanitize_key( $taxonomy ) ] = array_map( 'sanitize_text_field', (array) $values );
		}
		$filters['attributes'] = $safe_attributes;

		$result = ( new WooFlux_Query() )->run( $filters );
		$html   = $this->render_products( $result['products'] );

		$response = array(
			'html'  => $html,
			'total' => $result['total'],
			'pages' => $result['pages'],
		);

		set_transient( $cache_key, $response, (int) get_option( 'wooflux_cache_ttl', 300 ) );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Renders an array of WC_Product objects to HTML using the product-card template.
	 *
	 * @param WC_Product[] $products Array of product objects.
	 * @return string Rendered HTML.
	 */
	private function render_products( array $products ): string {
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
	 * Returns the schema for REST endpoint query parameters.
	 *
	 * @return array[]
	 */
	private function get_request_args(): array {
		return array(
			'categories' => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'integer' ),
				'default' => array(),
			),
			'price_min'  => array(
				'type'    => 'number',
				'default' => 0,
				'minimum' => 0,
			),
			'price_max'  => array(
				'type'    => 'number',
				'default' => 0,
				'minimum' => 0,
			),
			'on_sale'    => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'in_stock'   => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'attributes' => array(
				'type'    => 'object',
				'default' => array(),
			),
			'rating'     => array(
				'type'    => 'integer',
				'default' => 0,
				'minimum' => 0,
				'maximum' => 5,
			),
			'page'       => array(
				'type'    => 'integer',
				'default' => 1,
				'minimum' => 1,
			),
		);
	}
}
