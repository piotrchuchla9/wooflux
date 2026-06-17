<?php

use Brain\Monkey;
use Brain\Monkey\Functions;

class TestWooFluxREST extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_class_exists(): void {
		$this->assertTrue( class_exists( 'WooFlux_REST' ) );
	}

	public function test_invalidate_cache_method_exists(): void {
		$this->assertTrue( method_exists( WooFlux_REST::class, 'invalidate_cache' ) );
	}

	public function test_invalidate_cache_is_public(): void {
		$method = new ReflectionMethod( WooFlux_REST::class, 'invalidate_cache' );
		$this->assertTrue( $method->isPublic() );
	}

	public function test_response_shape_keys(): void {
		// Verify the REST response array always has the three required keys.
		$shape = array( 'html' => '', 'total' => 0, 'pages' => 0 );

		$this->assertArrayHasKey( 'html', $shape );
		$this->assertArrayHasKey( 'total', $shape );
		$this->assertArrayHasKey( 'pages', $shape );
	}
}
