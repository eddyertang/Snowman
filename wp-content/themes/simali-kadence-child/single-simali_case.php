<?php
/**
 * Case study.
 *
 * @package Simali
 */

get_header();
while ( have_posts() ) :
	the_post();
	$town = get_post_meta( get_the_ID(), '_simali_town', true );
	$room = get_post_meta( get_the_ID(), '_simali_room', true );
	$m2   = get_post_meta( get_the_ID(), '_simali_m2', true );
	$product_id = (int) get_post_meta( get_the_ID(), '_simali_product', true );
	?>
	<article class="section">
		<div class="wrap">
			<h1><?php the_title(); ?></h1>
			<?php
			$image = get_post_meta( get_the_ID(), '_simali_image', true );
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', array( 'class' => 'ratio-3x2', 'alt' => get_the_title() ) );
			} elseif ( $image ) {
				printf( '<img class="ratio-3x2" src="%s" alt="" width="1024" height="683">', esc_url( $image ) );
			} else {
				simali_placeholder( 'TODO(client): before and after photographs', '3x2' );
			}
			?>
			<p class="todo">TODO(client): before photograph, if one exists</p>
			<ul>
				<li>Location: <?php echo $town ? esc_html( $town ) : '<span class="todo">TODO(client): town</span>'; ?></li>
				<li>Room: <?php echo $room ? esc_html( $room ) : '<span class="todo">TODO(client): room</span>'; ?></li>
				<li>Area: <?php echo $m2 ? esc_html( $m2 ) . ' m²' : '<span class="todo">TODO(client): m²</span>'; ?></li>
				<li>Stone:
					<?php
					if ( $product_id && function_exists( 'wc_get_product' ) && wc_get_product( $product_id ) ) {
						$linked = wc_get_product( $product_id );
						echo '<a href="' . esc_url( $linked->get_permalink() ) . '">' . esc_html( $linked->get_name() ) . '</a>';
					} else {
						echo '<span class="todo">TODO(client): linked product</span>';
					}
					?>
				</li>
			</ul>
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;
get_footer();
