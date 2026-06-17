<?php
/**
 * Single product card template.
 *
 * Variable: $product WC_Product
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $product ) || ! $product instanceof WC_Product ) {
	return;
}

$product_id    = $product->get_id();
$permalink     = get_permalink( $product_id );
$thumbnail_id  = $product->get_image_id();
$thumbnail_url = $thumbnail_id
	? wp_get_attachment_image_url( $thumbnail_id, 'woocommerce_thumbnail' )
	: wc_placeholder_img_src( 'woocommerce_thumbnail' );
$thumbnail_alt = $thumbnail_id
	? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true )
	: $product->get_name();
?>
<article
	class="wooflux-product-card product type-product"
	data-product-id="<?php echo esc_attr( $product_id ); ?>"
>
	<a href="<?php echo esc_url( $permalink ); ?>" class="wooflux-product-image" tabindex="-1" aria-hidden="true">
		<img
			src="<?php echo esc_url( $thumbnail_url ); ?>"
			alt="<?php echo esc_attr( $thumbnail_alt ); ?>"
			loading="lazy"
		/>
	</a>

	<h2 class="wooflux-product-title">
		<a href="<?php echo esc_url( $permalink ); ?>">
			<?php echo esc_html( $product->get_name() ); ?>
		</a>
	</h2>

	<div class="wooflux-product-price">
		<?php echo wp_kses_post( $product->get_price_html() ); ?>
	</div>

	<div class="wooflux-add-to-cart">
		<?php
		woocommerce_template_loop_add_to_cart(
			array(
				'quantity' => 1,
			)
		);
		?>
	</div>
</article>
