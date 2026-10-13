<?php
/**
 * Product card.
 *
 * @package Simali
 */

defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}
$spec     = simali_spec_line( $product );
$material = simali_attr_list( $product, 'pa_material' );
?>
<article class="card">
	<a class="card-media" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $product->get_image_id() ) {
			echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'class' => 'ratio-1', 'alt' => '', 'loading' => 'lazy' ) );
		} else {
			simali_placeholder( $product->get_name(), '1' );
		}
		?>
	</a>
	<div class="card-body">
		<?php if ( $material && ! $product->is_virtual() ) : ?>
			<p class="eyebrow"><?php echo esc_html( $material ); ?></p>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<?php if ( $spec ) : ?>
			<p class="spec"><?php echo esc_html( $spec ); ?></p>
		<?php endif; ?>
		<p class="price"><?php echo esc_html( simali_price_label( $product ) ); ?></p>
		<?php if ( $product->is_virtual() ) : ?>
			<a class="btn btn-secondary btn-small" href="<?php echo esc_url( $product->get_permalink() ); ?>">About the guide</a>
		<?php else : ?>
			<button class="btn btn-secondary btn-small" type="button" data-add-sample="<?php echo esc_attr( (string) $product->get_id() ); ?>">Order sample</button>
		<?php endif; ?>
	</div>
</article>
