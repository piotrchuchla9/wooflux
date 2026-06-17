<?php
/**
 * WooCommerce product query builder.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds and executes WP_Query arguments from filter parameters.
 */
class WooFlux_Query {

	/**
	 * Runs the query and returns products plus pagination info.
	 *
	 * @param array $filters Normalised filter parameters.
	 * @return array{ products: WC_Product[], total: int, pages: int }
	 */
	public function run( array $filters ): array {
		$args  = $this->build_args( $filters );
		$query = new WP_Query( $args ); // phpcs:ignore WordPress.DB.SlowDBQuery

		$products = array_values( array_filter( array_map( 'wc_get_product', $query->posts ) ) );

		return array(
			'products' => $products,
			'total'    => (int) $query->found_posts,
			'pages'    => (int) ( $query->max_num_pages ? $query->max_num_pages : 1 ),
		);
	}

	/**
	 * Translates filter parameters into WP_Query arguments.
	 *
	 * @param array $filters Normalised filter parameters.
	 * @return array WP_Query args.
	 */
	public function build_args( array $filters ): array {
		$per_page = (int) get_option( 'wooflux_default_per_page', 12 );
		$page     = max( 1, (int) ( $filters['page'] ?? 1 ) );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'tax_query'      => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_query'     => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery
		);

		if ( ! empty( $filters['categories'] ) ) {
			$slugs = array_filter(
				array_map(
					function ( $id ) {
						$term = get_term( (int) $id, 'product_cat' );
						return ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
					},
					(array) $filters['categories']
				)
			);
			if ( ! empty( $slugs ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array_values( $slugs ),
					'operator' => 'IN',
				);
			}
		}

		if ( ! empty( $filters['priceMin'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => (float) $filters['priceMin'],
				'compare' => '>=',
				'type'    => 'DECIMAL(10,2)',
			);
		}

		if ( ! empty( $filters['priceMax'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => (float) $filters['priceMax'],
				'compare' => '<=',
				'type'    => 'DECIMAL(10,2)',
			);
		}

		if ( ! empty( $filters['onSale'] ) ) {
			$sale_ids         = wc_get_product_ids_on_sale();
			$args['post__in'] = ! empty( $sale_ids ) ? $sale_ids : array( 0 );
		}

		if ( ! empty( $filters['inStock'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_stock_status',
				'value' => 'instock',
			);
		}

		if ( ! empty( $filters['rating'] ) && wooflux_is_pro() ) {
			$args['meta_query'][] = array(
				'key'     => '_wc_average_rating',
				'value'   => (float) $filters['rating'],
				'compare' => '>=',
				'type'    => 'DECIMAL(10,2)',
			);
		}

		if ( ! empty( $filters['attributes'] ) && wooflux_is_pro() ) {
			foreach ( (array) $filters['attributes'] as $taxonomy => $values ) {
				if ( empty( $values ) ) {
					continue;
				}
				$args['tax_query'][] = array(
					'taxonomy' => sanitize_key( $taxonomy ),
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_text_field', (array) $values ),
					'operator' => 'IN',
				);
			}
		}

		return $args;
	}
}
