<?php
/**
 * Our work archive.
 *
 * @package Simali
 */

get_header();
$room_filter     = isset( $_GET['room'] ) ? sanitize_title( wp_unslash( $_GET['room'] ) ) : '';
$material_filter = isset( $_GET['material'] ) ? sanitize_title( wp_unslash( $_GET['material'] ) ) : '';
$rooms           = array( 'kitchen' => 'Kitchen', 'living' => 'Living room', 'hall' => 'Hallway', 'bathroom' => 'Bathroom', 'outdoor' => 'Outdoor' );
$materials       = array( 'limestone' => 'Limestone', 'porcelain' => 'Porcelain', 'travertine' => 'Travertine', 'sandstone' => 'Sandstone' );
?>
<article class="section page">
	<div class="wrap">
		<header class="page-head">
			<h1>Our work</h1>
			<p class="lede">Real floors in real homes across Dorset, Wiltshire and Somerset, all supplied and installed by Simali Stone. More are on our <a href="<?php echo esc_url( simali_houzz_url() ); ?>" rel="noopener">Houzz profile</a>.</p>
		</header>
		<form class="filters inline" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'simali_case' ) ); ?>">
			<label class="field">Room
				<select name="room">
					<option value="">Any room</option>
					<?php foreach ( $rooms as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $room_filter, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="field">Material
				<select name="material">
					<option value="">Any material</option>
					<?php foreach ( $materials as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $material_filter, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="btn" type="submit">Filter</button>
			<?php if ( $room_filter || $material_filter ) : ?>
				<a class="text-link" href="<?php echo esc_url( get_post_type_archive_link( 'simali_case' ) ); ?>">Clear</a>
			<?php endif; ?>
		</form>
		<?php if ( have_posts() ) : ?>
			<div class="case-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$town     = get_post_meta( get_the_ID(), '_simali_town', true );
					$material = get_post_meta( get_the_ID(), '_simali_material', true );
					$alt      = get_post_meta( get_the_ID(), '_simali_alt', true );
					?>
					<article class="case-card">
						<a class="tile" href="<?php the_permalink(); ?>">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'medium_large', array( 'class' => 'ratio-3x2', 'alt' => $alt ? $alt : get_the_title(), 'loading' => 'lazy' ) );
							} else {
								$image = get_post_meta( get_the_ID(), '_simali_image', true );
								if ( $image ) {
									printf( '<img class="ratio-3x2" src="%s" alt="%s" width="768" height="512" loading="lazy">', esc_url( $image ), esc_attr( $alt ) );
								} else {
									simali_placeholder( get_the_title(), '3x2' );
								}
							}
							?>
							<span class="card-body">
								<?php if ( $material || $town ) : ?>
									<span class="eyebrow"><?php echo esc_html( implode( ' · ', array_filter( array( ucfirst( $material ), $town ) ) ) ); ?></span>
								<?php endif; ?>
								<span class="tile-title"><?php the_title(); ?></span>
							</span>
						</a>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => 'Previous', 'next_text' => 'Next' ) ); ?>
		<?php else : ?>
			<p>No projects match that filter yet. <a href="<?php echo esc_url( get_post_type_archive_link( 'simali_case' ) ); ?>">See all the projects</a>.</p>
		<?php endif; ?>
		<section class="band inset">
			<div class="band-inner">
				<div>
					<h2>Planning a floor?</h2>
					<p>Tell us the postcode and a rough area and we will quote for the stone and the fitting.</p>
				</div>
				<p class="actions"><a class="btn btn-light" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a></p>
			</div>
		</section>
	</div>
</article>
<?php
get_footer();
