<?php
/**
 * Home.
 *
 * @package Simali
 */

get_header();
$hero = simali_hero_url();
?>
<section class="hero">
	<?php if ( $hero ) : ?>
		<img class="hero-image" src="<?php echo esc_url( $hero ); ?>" alt="Ludlow limestone flagstones laid through a cottage living room with an inglenook fireplace, supplied and installed by Simali Stone" width="1400" height="1050" fetchpriority="high" decoding="async">
	<?php endif; ?>
	<div class="hero-scrim"></div>
	<div class="hero-copy wrap">
		<p class="eyebrow">Shaftesbury, Dorset</p>
		<h1>Natural stone &amp; porcelain floors, supplied and expertly installed</h1>
		<p class="lede">Limestone, flagstones and porcelain chosen in our Shaftesbury showroom. Delivered nationwide, installed by our own team within 40 miles.</p>
		<div class="actions">
			<a class="btn" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
			<a class="btn btn-on-dark" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a>
			<a class="text-link" href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">Visit the showroom</a>
		</div>
	</div>
</section>

<section class="trust" aria-label="Why Simali Stone">
	<div class="wrap">
		<ul>
			<li><strong>Over 30 years</strong><span>supplying, laying and caring for stone floors</span></li>
			<li><strong>Supply and install</strong><span>one team from showroom to sealed floor, within 40 miles of Shaftesbury</span></li>
			<li><strong>CA' Pietra stockist</strong><span>plus Cathedral, Heritage and the Timeless Stone Collection</span></li>
			<li><strong>5-star reviews</strong><span><a href="<?php echo esc_url( simali_google_reviews_url() ); ?>" rel="noopener">Read them on Google</a> or see our work on <a href="<?php echo esc_url( simali_houzz_url() ); ?>" rel="noopener">Houzz</a></span></li>
		</ul>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section-head">
			<h2>Shop by material</h2>
			<p>Everything is sold by the square metre and every stone can be sampled first.</p>
		</div>
		<div class="material-grid">
			<?php
			$materials = array(
				array( 'Limestone', 'Cathedral, Heritage, Ludlow and the Timeless Stone Collection flagstones.', '/limestone/', 'cathedral-limestone-cottage-living-room-floor-teffont.jpg', 'Cathedral limestone floor in a cottage living room in Teffont' ),
				array( 'Porcelain', 'Large formats, stone and wood effects, floors and walls.', '/porcelain/', 'large-format-full-room-installation-dorset.jpg', 'Large-format grey porcelain tiles across an open-plan room in Dorset' ),
				array( 'Outdoor &amp; patios', 'Limestone and porcelain patios, steps and matching indoor-outdoor tiles.', '/outdoor/', 'cathedral-grey-patio-and-step-installed-dorset.jpg', 'Limestone patio and step installed in Dorset' ),
			);
			foreach ( $materials as $material ) :
				?>
				<a class="tile" href="<?php echo esc_url( home_url( $material[2] ) ); ?>">
					<?php simali_image( $material[3], $material[4], '4x3' ); ?>
					<span class="card-body">
						<span class="tile-title"><?php echo wp_kses_post( $material[0] ); ?></span>
						<span class="spec"><?php echo esc_html( $material[1] ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section alt">
	<div class="wrap">
		<div class="section-head">
			<h2>Featured floors</h2>
			<p>A few of the stones people ask for most. <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">See the whole range</a>.</p>
		</div>
		<?php echo do_shortcode( '[simali_products limit="8"]' ); ?>
	</div>
</section>

<section class="section">
	<div class="wrap split">
		<div>
			<h2>Take your time, get it right</h2>
			<p>Choosing flooring isn’t something to rush.</p>
			<p>Most of our customers take samples home, live with them, and come back once they’re sure. That’s exactly how it should be.</p>
			<p>We’d rather you choose the right floor slowly than the wrong one quickly — because once it’s down, you’ll see it every day.</p>
			<ol class="steps compact">
				<li class="step"><span>1</span><h3>Order samples</h3><p>Pick a few stones and we post them out.</p></li>
				<li class="step"><span>2</span><h3>Live with them</h3><p>See them in your light, with your kitchen, in the morning and at night.</p></li>
				<li class="step"><span>3</span><h3>Order or book installation</h3><p>We deliver nationwide, and install within 40 miles of Shaftesbury.</p></li>
			</ol>
		</div>
		<?php simali_image( 'molly-on-stone-floor-with-toy.jpg', 'Molly the dog lying on a limestone floor with a toy', '4x3', array( 'class' => 'rounded' ) ); ?>
	</div>
</section>

<section class="section alt">
	<div class="wrap">
		<div class="section-head">
			<h2>What our customers say</h2>
		</div>
		<?php simali_render_testimonials(); ?>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section-head">
			<h2>Our work</h2>
			<p>Real floors in real homes across Dorset, Wiltshire and Somerset.</p>
		</div>
		<?php echo do_shortcode( '[simali_work limit="6"]' ); ?>
		<p class="section-foot"><a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/our-work/' ) ); ?>">See all the projects</a></p>
	</div>
</section>

<section class="section alt">
	<div class="wrap split">
		<div>
			<h2>Visit the Shaftesbury showroom</h2>
			<p>Full-size stones in natural light, so you can see the real colour and texture before you decide.</p>
			<p>40d–40f Wincombe Business Park, Shaftesbury SP7 9QJ</p>
			<p><?php echo esc_html( simali_hours_copy() ); ?></p>
			<p>
				<a class="btn" href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">Plan your visit</a>
				<a class="text-link" href="https://www.openstreetmap.org/search?query=Wincombe%20Business%20Park%20Shaftesbury%20SP7%209QJ" rel="noopener">Map to the showroom</a>
			</p>
		</div>
		<?php simali_image( 'fieldhoue3.jpg', 'Field House limestone flooring, one of the stones on display in the Shaftesbury showroom', '3x2', array( 'class' => 'rounded' ) ); ?>
	</div>
</section>

<section class="section">
	<div class="wrap split reverse">
		<div>
			<h2>Stone and porcelain patios</h2>
			<p>We supply and install porcelain and natural stone patios across Dorset and the surrounding areas.</p>
			<p>From modern outdoor porcelain slabs to traditional limestone terraces, we help create outdoor spaces that last.</p>
			<p>Matching indoor and outdoor tiles available for a seamless finish.</p>
			<p><a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/outdoor/' ) ); ?>">Outdoor &amp; patios</a></p>
		</div>
		<?php simali_image( 'porcelain-outdoor-patio-installed-gillingham-dorset.jpg', 'Outdoor porcelain patio installed in Gillingham, Dorset', '3x2', array( 'class' => 'rounded' ) ); ?>
	</div>
</section>

<section class="section band">
	<div class="wrap band-inner">
		<div>
			<h2>Choose the right floor slowly</h2>
			<p>Order samples, ask for a price, or come and see the stone in Shaftesbury.</p>
		</div>
		<p class="actions">
			<a class="btn btn-light" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
			<a class="btn btn-on-dark" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a>
		</p>
	</div>
</section>
<?php
get_footer();
