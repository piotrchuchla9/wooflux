<?php

use Brain\Monkey;
use Brain\Monkey\Functions;

class TestWooFluxQuery extends \PHPUnit\Framework\TestCase {

	private WooFlux_Query $query;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		// get_option returns the default (second arg) by default via Brain\Monkey.
		Functions\when( 'get_option' )->returnArg( 2 );
		Functions\when( 'wooflux_is_pro' )->justReturn( false );
		Functions\when( 'wc_get_product_ids_on_sale' )->justReturn( array( 1, 2, 3 ) );
		Functions\when( 'get_term' )->justReturn( (object) array( 'slug' => 'test-cat', 'term_id' => 5 ) );
		Functions\when( 'is_wp_error' )->justReturn( false );
		$this->query = new WooFlux_Query();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_category_filter_builds_tax_query(): void {
		$args = $this->query->build_args( array( 'categories' => array( 5, 12 ) ) );

		$this->assertArrayHasKey( 'tax_query', $args );
		$tax_clauses = array_filter(
			$args['tax_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['taxonomy'] ?? '' ) === 'product_cat'
		);
		$this->assertNotEmpty( $tax_clauses );
	}

	public function test_price_min_adds_meta_query(): void {
		$args = $this->query->build_args( array( 'priceMin' => 10 ) );

		$price_clauses = array_filter(
			$args['meta_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['key'] ?? '' ) === '_price' && ( $clause['compare'] ?? '' ) === '>='
		);
		$this->assertNotEmpty( $price_clauses );
		$clause = array_values( $price_clauses )[0];
		$this->assertEquals( 10.0, $clause['value'] );
		$this->assertEquals( 'DECIMAL(10,2)', $clause['type'] );
	}

	public function test_price_max_adds_meta_query(): void {
		$args = $this->query->build_args( array( 'priceMax' => 100 ) );

		$price_clauses = array_filter(
			$args['meta_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['key'] ?? '' ) === '_price' && ( $clause['compare'] ?? '' ) === '<='
		);
		$this->assertNotEmpty( $price_clauses );
		$clause = array_values( $price_clauses )[0];
		$this->assertEquals( 100.0, $clause['value'] );
	}

	public function test_price_zero_does_not_add_meta_query(): void {
		$args = $this->query->build_args( array( 'priceMin' => 0, 'priceMax' => 0 ) );

		$price_clauses = array_filter(
			$args['meta_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['key'] ?? '' ) === '_price'
		);
		$this->assertEmpty( $price_clauses );
	}

	public function test_on_sale_adds_post_in(): void {
		$args = $this->query->build_args( array( 'onSale' => true ) );

		$this->assertArrayHasKey( 'post__in', $args );
	}

	public function test_in_stock_adds_meta_query(): void {
		$args = $this->query->build_args( array( 'inStock' => true ) );

		$stock_clauses = array_filter(
			$args['meta_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['key'] ?? '' ) === '_stock_status'
		);
		$this->assertNotEmpty( $stock_clauses );
		$clause = array_values( $stock_clauses )[0];
		$this->assertEquals( 'instock', $clause['value'] );
	}

	public function test_pro_rating_filter_ignored_in_free(): void {
		$args = $this->query->build_args( array( 'rating' => 4 ) );

		$rating_clauses = array_filter(
			$args['meta_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['key'] ?? '' ) === '_wc_average_rating'
		);
		$this->assertEmpty( $rating_clauses );
	}

	public function test_pro_attributes_ignored_in_free(): void {
		$args = $this->query->build_args(
			array(
				'attributes' => array( 'pa_color' => array( 'red' ) ),
			)
		);

		$attr_clauses = array_filter(
			$args['tax_query'],
			fn( $clause ) => is_array( $clause ) && ( $clause['taxonomy'] ?? '' ) === 'pa_color'
		);
		$this->assertEmpty( $attr_clauses );
	}

	public function test_default_per_page_applied(): void {
		$args = $this->query->build_args( array() );

		$this->assertArrayHasKey( 'posts_per_page', $args );
		$this->assertIsInt( $args['posts_per_page'] );
	}

	public function test_page_defaults_to_one(): void {
		$args = $this->query->build_args( array() );

		$this->assertEquals( 1, $args['paged'] );
	}

	public function test_post_type_is_product(): void {
		$args = $this->query->build_args( array() );

		$this->assertEquals( 'product', $args['post_type'] );
		$this->assertEquals( 'publish', $args['post_status'] );
	}
}
