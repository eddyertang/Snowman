<?php
/**
 * Site footer.
 *
 * @package Simali
 */
?>
</main>
<footer class="site-footer">
	<div class="wrap footer-grid">
		<div>
			<p><strong>Simali Stone Ltd</strong><br>
			40d–40f Wincome Business Park<br>
			Shaftesbury SP7 9QJ</p>
			<p><a href="<?php echo esc_url( simali_phone_href() ); ?>">01747 852557</a><br>
			<a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a></p>
			<p class="todo">TODO(client): opening hours</p>
			<p><a href="https://www.openstreetmap.org/search?query=40d-40f%20Wincome%20Business%20Park%20Shaftesbury%20SP7%209QJ">Map to the showroom</a></p>
			<p class="fine">Company No. 07158016<br>VAT 711839046</p>
		</div>
		<nav aria-label="Footer">
			<strong>Shop</strong>
			<?php foreach ( array_slice( simali_primary_links(), 0, 4, true ) as $url => $label ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><br>
			<?php endforeach; ?>
		</nav>
		<nav aria-label="Company">
			<strong>Visit</strong>
			<?php foreach ( array_slice( simali_primary_links(), 4, 3, true ) as $url => $label ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><br>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
		</nav>
		<nav aria-label="Policies">
			<strong>Help</strong><br>
			<a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>">Delivery</a><br>
			<a href="<?php echo esc_url( home_url( '/returns/' ) ); ?>">Returns</a><br>
			<a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>">Terms</a><br>
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a><br>
			<a href="<?php echo esc_url( home_url( '/faqs/' ) ); ?>">FAQs</a><br>
			<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Guides</a>
			<p><strong>Areas we cover</strong></p>
			<a href="<?php echo esc_url( home_url( '/tiles-shaftesbury/' ) ); ?>">Tiles in Shaftesbury</a><br>
			<a href="<?php echo esc_url( home_url( '/stone-flooring-by-simali-stone/' ) ); ?>">Stone flooring</a><br>
			<a href="<?php echo esc_url( home_url( '/limestone-tiles-in-dorset-simali-stone/' ) ); ?>">Limestone tiles in Dorset</a><br>
			<a href="<?php echo esc_url( home_url( '/stone-flooring-installation-across-the-south-west/' ) ); ?>">Installation across the South West</a>
		</nav>
	</div>
</footer>
<nav class="sticky-cta" aria-label="Quick actions">
	<a href="<?php echo esc_url( simali_phone_href() ); ?>">Call</a>
	<a href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Samples</a>
	<a href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Quote</a>
</nav>
<div class="cookie" id="cookie-banner" role="region" aria-label="Sample basket cookie" hidden>
	<p>We store a sample-basket cookie for 30 days so your choices are still here when you come back. No advertising cookies.</p>
	<button class="btn" type="button" id="cookie-ok">OK</button>
</div>
<?php wp_footer(); ?>
</body>
</html>
