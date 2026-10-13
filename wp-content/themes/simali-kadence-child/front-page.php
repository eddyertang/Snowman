<?php
/**
 * Home.
 *
 * @package Simali
 */

get_header();
$hero = get_option( 'simali_hero_image' );
?>
<section class="hero" <?php echo $hero ? 'style="background-image:url(' . esc_url( $hero ) . ')"' : ''; ?>>
	<div class="hero-scrim"></div>
	<div class="hero-copy wrap">
		<?php if ( ! $hero ) : ?>
			<p class="placeholder-note todo">TODO(client): full-width room photograph</p>
		<?php endif; ?>
		<h1>Natural stone &amp; porcelain floors, supplied and expertly installed</h1>
		<p>From our Shaftesbury showroom. Nationwide delivery, installation within 40 miles.</p>
		<div class="actions">
			<a class="btn" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
			<a class="btn btn-on-dark" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a>
			<a class="text-link" href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">Visit the showroom</a>
		</div>
	</div>
</section>

<section class="section trust">
	<div class="wrap">
		<ul>
			<li class="todo">TODO(client): confirm 30 or 35 years</li>
			<li>Real installs across Dorset &amp; the South West</li>
			<li>CA' Pietra stockist</li>
			<li class="todo">TODO(client): review score, only if a real source exists</li>
		</ul>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>Shop by material</h2>
		<div class="material-grid">
			<?php
			$materials = array(
				array( 'Limestone', '/limestone/', 'TODO(client): limestone room photograph' ),
				array( 'Porcelain', '/porcelain/', 'TODO(client): porcelain room photograph' ),
				array( 'Outdoor', '/outdoor/', 'TODO(client): patio photograph' ),
			);
			foreach ( $materials as $material ) :
				?>
				<a class="tile" href="<?php echo esc_url( home_url( $material[1] ) ); ?>">
					<?php simali_placeholder( $material[2], '4x3' ); ?>
					<span class="card-body"><h3><?php echo esc_html( $material[0] ); ?></h3></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>Featured floors</h2>
		<?php echo do_shortcode( '[simali_products limit="8"]' ); ?>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>Take your time, get it right</h2>
		<p>Choosing flooring isn’t something to rush.</p>
		<p>Most of our customers take samples home, live with them, and come back once they’re sure. That’s exactly how it should be.</p>
		<p>We’d rather you choose the right floor slowly than the wrong one quickly — because once it’s down, you’ll see it every day.</p>
		<ol class="steps">
			<li class="step"><span>1</span><h3>Order samples</h3></li>
			<li class="step"><span>2</span><h3>Live with them</h3></li>
			<li class="step"><span>3</span><h3>Order or book installation</h3></li>
		</ol>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>What our customers say</h2>
		<?php simali_render_testimonials(); ?>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>Our work</h2>
		<?php echo do_shortcode( '[simali_work limit="6"]' ); ?>
		<p><a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/our-work/' ) ); ?>">See the projects</a></p>
	</div>
</section>

<section class="section">
	<div class="wrap" style="display:grid;gap:24px">
		<div>
			<h2>Visit the Shaftesbury showroom</h2>
			<p>40d–40f Wincome Business Park, Shaftesbury SP7 9QJ</p>
			<p class="todo">TODO(client): opening hours</p>
			<p><a href="https://www.openstreetmap.org/search?query=40d-40f%20Wincome%20Business%20Park%20Shaftesbury%20SP7%209QJ">Map to the showroom</a></p>
			<p><a class="btn" href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">Plan your visit</a></p>
		</div>
		<?php simali_placeholder( 'TODO(client): showroom photograph', '3x2' ); ?>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<h2>Stone and porcelain patios</h2>
		<p>We supply and install porcelain and natural stone patios across Dorset and the surrounding areas.</p>
		<p>From modern outdoor porcelain slabs to traditional limestone terraces, we help create outdoor spaces that last.</p>
		<p>Matching indoor and outdoor tiles available for a seamless finish.</p>
		<p><a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/outdoor/' ) ); ?>">Outdoor &amp; patios</a></p>
	</div>
</section>

<section class="section band">
	<div class="wrap">
		<h2>Choose the right floor slowly</h2>
		<p>Order samples, ask for a price, or come and see the stone in Shaftesbury.</p>
		<p>
			<a class="btn" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>" style="background:#fff;color:#0b501b">Order samples</a>
			<a class="btn btn-on-dark" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a>
		</p>
	</div>
</section>
<?php
get_footer();
