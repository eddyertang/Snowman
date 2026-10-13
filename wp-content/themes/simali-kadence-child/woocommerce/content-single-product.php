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
$images   = array_filter( array_merge( array( $product->get_image_id() ), $gallery ) );
$spec     = simali_spec_line( $product );
?>
<article class="section">
	<div class="wrap product">
		<div>
			<?php if ( $images ) : ?>
				<?php echo wp_get_attachment_image( $images[0], 'large', false, array( 'class' => 'ratio-1', 'id' => 'gallery-main', 'alt' => $product->get_name() ) ); ?>
				<div class="thumbs">
					<?php foreach ( $images as $index => $image_id ) : ?>
						<button type="button" data-gallery="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'large' ) ); ?>" aria-label="<?php echo esc_attr( 'Show image ' . ( $index + 1 ) ); ?>">
							<?php echo wp_get_attachment_image( $image_id, 'thumbnail', false, array( 'alt' => '' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<button class="btn btn-secondary" type="button" id="zoom-open">Zoom</button>
				<dialog class="gallery-dialog" id="zoom-dialog">
					<img src="" alt="<?php echo esc_attr( $product->get_name() ); ?>" id="zoom-image">
					<button class="btn" type="button" id="zoom-close">Close</button>
				</dialog>
			<?php else : ?>
				<?php simali_placeholder( 'TODO(client): texture close-up of ' . $product->get_name(), '1' ); ?>
			<?php endif; ?>
		</div>
		<div>
			<h1><?php echo esc_html( $product->get_name() ); ?></h1>
			<?php if ( $spec ) : ?><p class="spec"><?php echo esc_html( $spec ); ?></p><?php endif; ?>
			<p class="price">
				<?php
				if ( $priced ) {
					echo esc_html( simali_price_label( $product ) );
				} else {
					echo 'Price on request: we\'ll reply within <span class="todo">TODO(client): hours</span>';
				}
				?>
			</p>
			<?php if ( ! $virtual ) : ?>
				<?php echo do_shortcode( '[simali_calculator]' ); ?>
				<p>Quantity is in m². Use the calculator to include wastage.</p>
				<p>Pallet delivery £70+VAT per ~15 m², 3–5 working days</p>
			<?php endif; ?>
			<?php if ( $priced ) : ?>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php else : ?>
				<p><a class="btn" href="#price-request">Request price/quote</a></p>
			<?php endif; ?>
			<p><button class="btn btn-secondary" type="button" data-add-sample="<?php echo esc_attr( (string) $product->get_id() ); ?>">Order sample</button></p>
			<p><a href="<?php echo esc_url( simali_phone_href() ); ?>">Call 01747 852557</a></p>
			<p id="sample-status" aria-live="polite"></p>
		</div>
	</div>
	<div class="wrap">
		<section>
			<h2>Description</h2>
			<?php the_content(); ?>
		</section>
		<section>
			<h2>Specifications</h2>
			<table>
				<tbody>
					<?php
					$rows = array(
						'Material'                 => simali_attr_list( $product, 'pa_material' ),
						'Finish'                   => simali_attr_list( $product, 'pa_finish' ),
						'Edge'                     => simali_attr_list( $product, 'pa_edge' ),
						'Thickness'                => simali_attr_list( $product, 'pa_thickness' ),
						'Sizes'                    => simali_attr_list( $product, 'pa_size' ),
						'Origin'                   => '',
						'Suitable for underfloor heating' => simali_attr_list( $product, 'pa_ufh' ),
						'Rooms'                    => simali_attr_list( $product, 'pa_rooms' ),
						'Indoor / outdoor'         => simali_attr_list( $product, 'pa_location' ),
					);
					foreach ( $rows as $label => $value ) {
						echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
						echo $value ? esc_html( $value ) : '<span class="todo">TODO(client): ' . esc_html( strtolower( $label ) ) . '</span>';
						echo '</td></tr>';
					}
					?>
				</tbody>
			</table>
		</section>
		<section>
			<h2>Care and sealing</h2>
			<p class="todo">TODO(client): care and sealing copy</p>
		</section>
		<section>
			<h2>Delivery and returns</h2>
			<p>Kerbside pallet delivery is £70+VAT per pallet. A pallet is up to 1000 kg, about 15 m². Allow 3–5 working days. The lorry needs a level, driveable surface.</p>
			<p>Returns follow the supplier’s conditions. <a href="<?php echo esc_url( home_url( '/returns/' ) ); ?>">Read the returns summary</a>.</p>
		</section>
		<section>
			<h2>Questions</h2>
			<p>Quantity is the number of square metres. Call <a href="<?php echo esc_url( simali_phone_href() ); ?>">01747 852557</a> if the delivery access is tight.</p>
		</section>
		<?php if ( ! $priced ) : ?>
			<section id="price-request">
				<h2>Request a price</h2>
				<p>Price on request: we'll call or email you back.</p>
				<?php echo do_shortcode( '[simali_quote_form product="' . esc_attr( $product->get_slug() ) . '"]' ); ?>
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
		$related = wc_get_related_products( $product->get_id(), 4 );
		if ( $related ) {
			echo '<section><h2>Related</h2><div class="product-grid">';
			foreach ( $related as $related_id ) {
				$related_product = wc_get_product( $related_id );
				if ( $related_product ) {
					echo '<article class="card"><div class="card-body"><h3><a href="' . esc_url( $related_product->get_permalink() ) . '">' . esc_html( $related_product->get_name() ) . '</a></h3><p class="price">' . esc_html( simali_price_label( $related_product ) ) . '</p><button class="btn btn-secondary" type="button" data-add-sample="' . esc_attr( (string) $related_product->get_id() ) . '">Order sample</button></div></article>';
				}
			}
			echo '</div></section>';
		}
		?>
	</div>
</article>
