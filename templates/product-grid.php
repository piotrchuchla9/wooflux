<?php
/**
 * Product grid frontend template.
 *
 * Variables: $products, $products_html, $attributes, $wrapper_attributes
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

$columns     = (int) ( $attributes['columns'] ?? 3 );
$total_pages = (int) $products['pages'];
$show_pag    = $total_pages > 1;
?>
<div
	<?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="wooflux/filters"
	style="--wooflux-columns: <?php echo esc_attr( $columns ); ?>;"
>
	<div
		class="wooflux-loading"
		data-wp-bind--hidden="!state.isLoading"
		aria-live="polite"
		aria-label="<?php esc_attr_e( 'Loading products', 'live-product-filter-wooflux' ); ?>"
		hidden
	>
		<span class="wooflux-spinner" role="status"></span>
	</div>

	<div class="wooflux-products-container">
		<?php echo $products_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<p class="wooflux-results-count">
		<?php
		printf(
			/* translators: %d: number of products */
			esc_html( _n( '%d product', '%d products', $products['total'], 'live-product-filter-wooflux' ) ),
			(int) $products['total']
		);
		?>
	</p>

	<nav
		class="wooflux-pagination"
		style="<?php echo $show_pag ? '' : 'display:none'; ?>"
		aria-label="<?php esc_attr_e( 'Products pagination', 'live-product-filter-wooflux' ); ?>"
	>
		<button
			class="wooflux-page-btn wooflux-page-prev"
			data-wp-on--click="actions.prevPage"
			aria-label="<?php esc_attr_e( 'Previous page', 'live-product-filter-wooflux' ); ?>"
			disabled
		>&#8592;</button>

		<span class="wooflux-page-info">
			<span class="wooflux-page-current">1</span>
			/
			<span class="wooflux-page-total"><?php echo absint( $total_pages ); ?></span>
		</span>

		<button
			class="wooflux-page-btn wooflux-page-next"
			data-wp-on--click="actions.nextPage"
			aria-label="<?php esc_attr_e( 'Next page', 'live-product-filter-wooflux' ); ?>"
		>&#8594;</button>
	</nav>
</div>
