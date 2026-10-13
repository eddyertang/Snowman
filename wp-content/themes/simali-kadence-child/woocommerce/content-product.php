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
$spec = simali_spec_line( $product );
?>
<article class="card">
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<?php
		if ( $product->get_image_id() ) {
			echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'class' => 'ratio-1', 'alt' => $product->get_name() ) );
		} else {
			simali_placeholder( 'TODO(client): photograph of ' . $product->get_name(), '1' );
		}
		?>
	</a>
	<div class="card-body">
		<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<?php if ( $spec ) : ?>
			<p class="spec"><?php echo esc_html( $spec ); ?></p>
		<?php endif; ?>
		<p class="price"><?php echo esc_html( simali_price_label( $product ) ); ?></p>
		<button class="btn" type="button" data-add-sample="<?php echo esc_attr( (string) $product->get_id() ); ?>">Order sample</button>
	</div>
</article>
