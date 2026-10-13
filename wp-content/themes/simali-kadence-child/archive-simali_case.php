<?php
/**
 * Our work archive.
 *
 * @package Simali
 */

get_header();
?>
<article class="section">
	<div class="wrap">
		<h1>Our work</h1>
		<p>Installations across Dorset and the South West. Measurements and some locations are still to be confirmed.</p>
		<form class="filters" method="get">
			<label class="field">Room
				<select name="room">
					<option value="">Any room</option>
					<?php foreach ( array( 'kitchen', 'hall', 'bathroom', 'living', 'outdoor' ) as $room ) : ?>
						<option value="<?php echo esc_attr( $room ); ?>" <?php selected( isset( $_GET['room'] ) ? sanitize_title( wp_unslash( $_GET['room'] ) ) : '', $room ); ?>><?php echo esc_html( ucfirst( $room ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="field">Material
				<select name="material">
					<option value="">Any material</option>
					<?php foreach ( array( 'limestone', 'porcelain', 'travertine', 'sandstone' ) as $material ) : ?>
						<option value="<?php echo esc_attr( $material ); ?>" <?php selected( isset( $_GET['material'] ) ? sanitize_title( wp_unslash( $_GET['material'] ) ) : '', $material ); ?>><?php echo esc_html( ucfirst( $material ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="btn" type="submit">Filter</button>
		</form>
		<div class="case-grid">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="case-card">
						<a href="<?php the_permalink(); ?>">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'medium_large', array( 'class' => 'ratio-3x2', 'alt' => get_the_title() ) );
							} else {
								$image = get_post_meta( get_the_ID(), '_simali_image', true );
								if ( $image ) {
									printf( '<img class="ratio-3x2" src="%s" alt="" width="768" height="512" loading="lazy">', esc_url( $image ) );
								} else {
									simali_placeholder( 'TODO(client): project photograph', '3x2' );
								}
							}
							?>
							<div class="card-body"><h2><?php the_title(); ?></h2></div>
						</a>
					</article>
				<?php endwhile; ?>
			<?php else : ?>
				<p>No projects in this filter yet.</p>
			<?php endif; ?>
		</div>
	</div>
</article>
<?php
get_footer();
