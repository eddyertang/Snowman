<?php
/**
 * Single product.
 *
 * @package Simali
 */

defined( 'ABSPATH' ) || exit;
global $product;
$priced   = simali_is_priced( $product );
$virtual  = $product->is_virtual();
$gallery  = $product->get_gallery_image_ids();
$images   = array_values( array_filter( array_merge( array( $product->get_image_id() ), $gallery ) ) );
$spec     = simali_spec_line( $product );
$material = simali_attr_list( $product, 'pa_material' );
$short    = $product->get_short_description();
?>
<article class="section product-page">
	<div class="wrap product">
		<div class="product-media">
			<?php if ( $images ) : ?>
				<?php echo wp_get_attachment_image( $images[0], 'large', false, array( 'class' => 'ratio-1 product-main', 'id' => 'gallery-main', 'alt' => get_post_meta( $images[0], '_wp_attachment_image_alt', true ) ?: $product->get_name(), 'fetchpriority' => 'high' ) ); ?>
				<?php if ( count( $images ) > 1 ) : ?>
					<div class="thumbs" role="group" aria-label="Product photographs">
						<?php foreach ( $images as $index => $image_id ) : ?>
							<button type="button" data-gallery="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'large' ) ); ?>" aria-label="<?php echo esc_attr( 'Show photograph ' . ( $index + 1 ) . ' of ' . count( $images ) ); ?>" <?php echo 0 === $index ? 'aria-current="true"' : ''; ?>>
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<p><button class="btn btn-secondary btn-small" type="button" id="zoom-open">View full size</button></p>
				<dialog class="gallery-dialog" id="zoom-dialog">
					<img alt="<?php echo esc_attr( $product->get_name() ); ?>" id="zoom-image">
					<button class="btn" type="button" id="zoom-close">Close</button>
				</dialog>
			<?php else : ?>
				<?php simali_placeholder( $product->get_name(), '1' ); ?>
			<?php endif; ?>
		</div>
		<div class="product-summary">
			<?php if ( $material && ! $virtual ) : ?><p class="eyebrow"><?php echo esc_html( $material ); ?></p><?php endif; ?>
			<h1><?php echo esc_html( $product->get_name() ); ?></h1>
			<?php if ( $spec ) : ?><p class="spec"><?php echo esc_html( $spec ); ?></p><?php endif; ?>
			<?php if ( $short ) : ?><div class="summary-copy"><?php echo wp_kses_post( wpautop( $short ) ); ?></div><?php endif; ?>
			<p class="price">
				<?php
				if ( $priced ) {
					echo esc_html( simali_price_label( $product ) );
				} else {
					echo 'Price on request';
				}
				?>
			</p>
			<?php if ( ! $priced ) : ?>
				<p>Every project is different, so we confirm the price for this stone with you before anything is ordered. Send the form below or call and we will come back to you by phone or email.</p>
			<?php endif; ?>
			<?php if ( ! $virtual ) : ?>
				<?php echo do_shortcode( '[simali_calculator]' ); ?>
				<p class="fine">Quantity is in m². Use the calculator to include wastage. Pallet delivery is £70+VAT per pallet of about 15 m², in 3–5 working days.</p>
			<?php endif; ?>
			<div class="buy-actions">
				<?php if ( $priced ) : ?>
					<?php woocommerce_template_single_add_to_cart(); ?>
				<?php else : ?>
					<a class="btn" href="#price-request">Request a price</a>
				<?php endif; ?>
				<?php if ( ! $virtual ) : ?>
					<button class="btn btn-secondary" type="button" data-add-sample="<?php echo esc_attr( (string) $product->get_id() ); ?>">Order sample</button>
				<?php endif; ?>
			</div>
			<p id="sample-status" aria-live="polite"></p>
			<p class="fine">Questions? Call <a href="<?php echo esc_url( simali_phone_href() ); ?>">01747 852557</a> or <a href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">see it in the showroom</a>.</p>
		</div>
	</div>
	<div class="wrap product-detail">
		<?php
		$about = simali_product_about( $product );
		if ( $about ) :
			?>
			<section class="prose">
				<h2>About <?php echo esc_html( preg_replace( '/\s+[–-]\s+.*$/u', '', $product->get_name() ) ); ?></h2>
				<?php echo $about; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>
		<?php endif; ?>
		<?php
		$rows = array(
			'Material'                        => simali_attr_list( $product, 'pa_material' ),
			'Collection'                      => simali_attr_list( $product, 'pa_collection' ),
			'Colour'                          => simali_attr_list( $product, 'pa_colour' ),
			'Finish'                          => simali_attr_list( $product, 'pa_finish' ),
			'Edge'                            => simali_attr_list( $product, 'pa_edge' ),
			'Thickness'                       => simali_attr_list( $product, 'pa_thickness' ),
			'Sizes'                           => simali_attr_list( $product, 'pa_size' ),
			'Origin'                          => get_post_meta( $product->get_id(), '_simali_origin', true ),
			'Suitable for underfloor heating' => simali_attr_list( $product, 'pa_ufh' ) ? 'Yes' : '',
			'Rooms'                           => simali_attr_list( $product, 'pa_rooms' ),
			'Indoor / outdoor'                => simali_attr_list( $product, 'pa_location' ),
		);
		$rows = array_filter( $rows );
		if ( $rows && ! $virtual ) :
			?>
			<section>
				<h2>Specifications</h2>
				<table class="specs">
					<tbody>
						<?php foreach ( $rows as $label => $value ) : ?>
							<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="fine">Natural stone varies in colour and texture. Samples and photographs are a guide, not an exact match; the showroom has full-size pieces.</p>
			</section>
		<?php endif; ?>
		<?php if ( ! $virtual ) : ?>
			<section>
				<h2>Care and sealing</h2>
				<?php echo wp_kses_post( simali_care_copy( $product ) ); ?>
			</section>
			<section>
				<h2>Delivery and returns</h2>
				<p>Kerbside pallet delivery is £70+VAT per pallet. A pallet is up to 1000 kg, about 15 m². Allow 3–5 working days. The lorry needs a level, driveable surface. <a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>">Delivery details</a>.</p>
				<p>Returns follow the supplier’s conditions: undamaged stone, packed as delivered, with collection at £70 per pallet and a restocking fee. <a href="<?php echo esc_url( home_url( '/returns/' ) ); ?>">Read the returns summary</a>.</p>
			</section>
			<section>
				<h2>Installation</h2>
				<p>We install within 40 miles of Shaftesbury and supply nationwide. If we are laying the floor, delivery is arranged as part of the job. <a href="<?php echo esc_url( home_url( '/installation/' ) ); ?>">How an installation runs</a>.</p>
			</section>
		<?php endif; ?>
		<?php if ( ! $priced ) : ?>
			<section id="price-request">
				<h2>Request a price for <?php echo esc_html( preg_replace( '/\s+[–-]\s+.*$/u', '', $product->get_name() ) ); ?></h2>
				<p>Tell us roughly how many square metres and where the floor is going. We will call or email you back with the price, availability and, if you want it, an installation quotation.</p>
				<?php echo do_shortcode( '[simali_quote_form product="' . esc_attr( $product->get_name() ) . '"]' ); ?>
			</section>
		<?php endif; ?>
		<?php
		$match = (int) get_post_meta( $product->get_id(), '_simali_matching_outdoor', true );
		if ( $match ) {
			$outdoor = wc_get_product( $match );
			if ( $outdoor ) {
				echo '<section><h2>Matching outdoor tile</h2><p><a href="' . esc_url( $outdoor->get_permalink() ) . '">' . esc_html( $outdoor->get_name() ) . '</a></p></section>';
			}
		}
		$work = do_shortcode( '[simali_work limit="3" material="' . esc_attr( sanitize_title( $material ) ) . '"]' );
		if ( $material && ! $virtual && false !== strpos( $work, 'work-grid' ) ) {
			echo '<section><h2>' . esc_html( $material ) . ' floors we have laid</h2>' . $work . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		$related = wc_get_related_products( $product->get_id(), 4 );
		if ( $related ) {
			$current_post    = $GLOBALS['post'];
			$current_product = $product;
			echo '<section><h2>You might also like</h2><div class="product-grid">';
			foreach ( $related as $related_id ) {
				$related_post    = get_post( $related_id );
				$related_product = wc_get_product( $related_id );
				if ( $related_post && $related_product ) {
					$GLOBALS['post']    = $related_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['product'] = $related_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					wc_get_template_part( 'content', 'product' );
				}
			}
			$GLOBALS['post']    = $current_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['product'] = $current_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			echo '</div></section>';
		}
		?>
	</div>
</article>
