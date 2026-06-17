<?php
/**
 * Filter panel frontend template.
 *
 * Variables available from render_filter_panel():
 *   $categories    WP_Term[] — product categories
 *   $price_range   array{ min: float, max: float }
 *   $initial_state array — parsed from URL
 *   $attributes    array — block attributes
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-wp-interactive="wooflux/filters">

	<?php if ( ! empty( $categories ) && ( $attributes['showCategories'] ?? true ) ) : ?>
	<div class="wooflux-filter-group">
		<h3 class="wooflux-filter-title"><?php esc_html_e( 'Categories', 'live-product-filter-wooflux' ); ?></h3>
		<ul class="wooflux-categories">
			<?php foreach ( $categories as $category_item ) : ?>
				<?php $is_checked = in_array( (int) $category_item->term_id, $initial_state['categories'], true ); ?>
			<li>
				<label>
					<input
						type="checkbox"
						value="<?php echo esc_attr( $category_item->term_id ); ?>"
						<?php checked( $is_checked ); ?>
						data-wp-on--change="actions.toggleCategory"
					/>
					<span class="wooflux-cat-name"><?php echo esc_html( $category_item->name ); ?></span>
					<span class="wooflux-count">(<?php echo (int) $category_item->count; ?>)</span>
				</label>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<?php if ( $attributes['showPrice'] ?? true ) : ?>
	<div class="wooflux-filter-group">
		<h3 class="wooflux-filter-title"><?php esc_html_e( 'Price', 'live-product-filter-wooflux' ); ?></h3>
		<div class="wooflux-price-range">
			<input
				type="number"
				placeholder="<?php esc_attr_e( 'Min', 'live-product-filter-wooflux' ); ?>"
				<?php
				if ( $initial_state['priceMin'] > 0 ) :
					?>
					value="<?php echo esc_attr( $initial_state['priceMin'] ); ?>"<?php endif; ?>
				data-wp-on--input="actions.setPriceMin"
				min="0"
				step="0.01"
				aria-label="<?php esc_attr_e( 'Minimum price', 'live-product-filter-wooflux' ); ?>"
			/>
			<span aria-hidden="true">—</span>
			<input
				type="number"
				placeholder="<?php esc_attr_e( 'Max', 'live-product-filter-wooflux' ); ?>"
				<?php
				if ( $initial_state['priceMax'] > 0 ) :
					?>
					value="<?php echo esc_attr( $initial_state['priceMax'] ); ?>"<?php endif; ?>
				data-wp-on--input="actions.setPriceMax"
				min="0"
				step="0.01"
				aria-label="<?php esc_attr_e( 'Maximum price', 'live-product-filter-wooflux' ); ?>"
			/>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ( $attributes['showOnSale'] ?? true ) || ( $attributes['showInStock'] ?? true ) ) : ?>
	<div class="wooflux-filter-group">
		<?php if ( $attributes['showOnSale'] ?? true ) : ?>
		<label>
			<input
				type="checkbox"
				<?php checked( $initial_state['onSale'] ); ?>
				data-wp-on--change="actions.toggleOnSale"
				data-wp-bind--checked="state.filters.onSale"
			/>
			<?php esc_html_e( 'On sale', 'live-product-filter-wooflux' ); ?>
		</label>
		<?php endif; ?>

		<?php if ( $attributes['showInStock'] ?? true ) : ?>
		<label>
			<input
				type="checkbox"
				<?php checked( $initial_state['inStock'] ); ?>
				data-wp-on--change="actions.toggleInStock"
				data-wp-bind--checked="state.filters.inStock"
			/>
			<?php esc_html_e( 'In stock only', 'live-product-filter-wooflux' ); ?>
		</label>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<button
		class="wooflux-reset"
		data-wp-on--click="actions.resetFilters"
		data-wp-bind--disabled="state.isLoading"
	>
		<?php esc_html_e( 'Reset filters', 'live-product-filter-wooflux' ); ?>
	</button>

</div>
