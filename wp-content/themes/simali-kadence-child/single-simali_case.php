<?php
/**
 * Case study.
 *
 * @package Simali
 */

get_header();
while ( have_posts() ) :
	the_post();
	$id         = get_the_ID();
	$town       = get_post_meta( $id, '_simali_town', true );
	$room       = get_post_meta( $id, '_simali_room', true );
	$material   = get_post_meta( $id, '_simali_material', true );
	$m2         = get_post_meta( $id, '_simali_m2', true );
	$alt        = get_post_meta( $id, '_simali_alt', true );
	$product_id = (int) get_post_meta( $id, '_simali_product', true );
	$gallery    = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_simali_gallery', true ) ) );
	$before     = (int) get_post_meta( $id, '_simali_before', true );
	$linked     = ( $product_id && function_exists( 'wc_get_product' ) ) ? wc_get_product( $product_id ) : null;
	$rooms      = array( 'kitchen' => 'Kitchen', 'living' => 'Living room', 'hall' => 'Hallway', 'bathroom' => 'Bathroom', 'outdoor' => 'Outdoor' );
	$facts      = array_filter(
		array(
			'Location' => $town,
			'Room'     => $room ? ( $rooms[ $room ] ?? ucfirst( $room ) ) : '',
			'Material' => $material ? ucfirst( $material ) : '',
			'Area'     => $m2 ? $m2 . ' m²' : '',
		)
	);
	?>
	<article class="section page case">
		<div class="wrap">
			<p class="crumbs"><a href="<?php echo esc_url( get_post_type_archive_link( 'simali_case' ) ); ?>">Our work</a></p>
			<header class="page-head">
				<h1><?php the_title(); ?></h1>
				<?php if ( $facts ) : ?>
					<dl class="facts">
						<?php foreach ( $facts as $label => $value ) : ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
						<?php endforeach; ?>
						<?php if ( $linked ) : ?>
							<div><dt>Stone</dt><dd><a href="<?php echo esc_url( $linked->get_permalink() ); ?>"><?php echo esc_html( $linked->get_name() ); ?></a></dd></div>
						<?php endif; ?>
					</dl>
				<?php endif; ?>
			</header>
			<?php
			if ( $before && has_post_thumbnail() ) {
				echo '<div class="before-after">';
				echo '<figure>' . wp_get_attachment_image( $before, 'large', false, array( 'class' => 'ratio-3x2 rounded', 'alt' => get_post_meta( $before, '_wp_attachment_image_alt', true ) ) ) . '<figcaption>Before</figcaption></figure>';
				echo '<figure>' . get_the_post_thumbnail( $id, 'large', array( 'class' => 'ratio-3x2 rounded', 'alt' => $alt ? $alt : get_the_title() ) ) . '<figcaption>After</figcaption></figure>';
				echo '</div>';
			} elseif ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', array( 'class' => 'ratio-3x2 rounded case-lead', 'alt' => $alt ? $alt : get_the_title(), 'fetchpriority' => 'high' ) );
			} else {
				$image = get_post_meta( $id, '_simali_image', true );
				if ( $image ) {
					printf( '<img class="ratio-3x2 rounded case-lead" src="%s" alt="%s" width="1024" height="683">', esc_url( $image ), esc_attr( $alt ) );
				}
			}
			?>
			<div class="split case-body">
				<div class="prose">
					<?php the_content(); ?>
					<?php if ( $linked ) : ?>
						<p><a class="btn" href="<?php echo esc_url( $linked->get_permalink() ); ?>">See <?php echo esc_html( preg_replace( '/\s+[–-]\s+.*$/u', '', $linked->get_name() ) ); ?></a>
						<button class="btn btn-secondary" type="button" data-add-sample="<?php echo esc_attr( (string) $product_id ); ?>">Order a sample</button></p>
						<p id="sample-status" aria-live="polite"></p>
					<?php endif; ?>
				</div>
				<aside class="note">
					<h2>Like this floor?</h2>
					<p>We install within 40 miles of Shaftesbury and deliver nationwide. Send the postcode and a rough area and we will quote for the stone and the fitting.</p>
					<p><a class="btn" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a></p>
					<p><a class="text-link" href="<?php echo esc_url( simali_phone_href() ); ?>">Call 01747 852557</a></p>
				</aside>
			</div>
			<?php if ( $gallery ) : ?>
				<h2>More photographs</h2>
				<div class="gallery-grid">
					<?php foreach ( $gallery as $image_id ) : ?>
						<figure>
							<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'class' => 'ratio-3x2 rounded', 'loading' => 'lazy', 'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) ); ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php
			$more = do_shortcode( '[simali_work limit="3" exclude="' . $id . '" material="' . esc_attr( $material ) . '"]' );
			if ( false !== strpos( $more, 'work-grid' ) ) {
				echo '<h2>More ' . esc_html( $material ? $material : '' ) . ' floors</h2>' . $more; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
	</article>
	<?php
endwhile;
get_footer();
