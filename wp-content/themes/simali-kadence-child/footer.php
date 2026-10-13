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
		<div class="footer-about">
			<p class="footer-logo"><span class="logo-mark">Simali</span> <span class="logo-sub">Stone</span></p>
			<p>Natural stone and porcelain floors, supplied nationwide and installed within 40 miles of Shaftesbury. Over 30 years of hands-on experience.</p>
			<address>
				40d–40f Wincombe Business Park<br>
				Shaftesbury, Dorset SP7 9QJ<br>
				<a href="<?php echo esc_url( simali_phone_href() ); ?>">01747 852557</a><br>
				<a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a>
			</address>
			<p class="fine"><?php echo esc_html( simali_hours_copy() ); ?></p>
			<p class="social">
				<a href="<?php echo esc_url( simali_facebook_url() ); ?>" rel="noopener">Facebook</a>
				<a href="<?php echo esc_url( simali_houzz_url() ); ?>" rel="noopener">Houzz</a>
				<a href="<?php echo esc_url( simali_google_reviews_url() ); ?>" rel="noopener">Google reviews</a>
			</p>
			<?php
			if ( class_exists( 'Simali_Forms' ) ) {
				echo Simali_Forms::badges_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<nav aria-label="Footer">
			<strong>Shop</strong>
			<?php foreach ( array_slice( simali_primary_links(), 0, 3, true ) as $url => $label ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( home_url( '/timeless-stone-collection-simali-stone/' ) ); ?>">Timeless Stone Collection</a>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">All floors</a>
			<a href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
			<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Guides</a>
		</nav>
		<nav aria-label="Company">
			<strong>Visit</strong>
			<?php foreach ( array_slice( simali_primary_links(), 3, 4, true ) as $url => $label ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
			<strong class="gap">Areas we cover</strong>
			<a href="<?php echo esc_url( home_url( '/tiles-shaftesbury/' ) ); ?>">Tiles in Shaftesbury</a>
			<a href="<?php echo esc_url( home_url( '/stone-flooring-by-simali-stone/' ) ); ?>">Stone flooring</a>
			<a href="<?php echo esc_url( home_url( '/limestone-tiles-in-dorset-simali-stone/' ) ); ?>">Limestone tiles in Dorset</a>
			<a href="<?php echo esc_url( home_url( '/stone-flooring-installation-across-the-south-west/' ) ); ?>">Installation across the South West</a>
		</nav>
		<nav aria-label="Policies">
			<strong>Help</strong>
			<a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>">Delivery</a>
			<a href="<?php echo esc_url( home_url( '/returns/' ) ); ?>">Returns</a>
			<a href="<?php echo esc_url( home_url( '/faqs/' ) ); ?>">FAQs</a>
			<a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>">Terms</a>
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) ); ?>">My account</a>
		</nav>
	</div>
	<div class="wrap footer-base">
		<p class="fine">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Simali Stone Ltd · Company No. 07158016 · VAT 711839046</p>
	</div>
</footer>
<nav class="sticky-cta" aria-label="Quick actions">
	<a href="<?php echo esc_url( simali_phone_href() ); ?>">Call</a>
	<a href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Samples</a>
	<a href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Quote</a>
</nav>
<div class="cookie" id="cookie-banner" role="region" aria-label="Sample basket cookie" hidden>
	<p>We store a sample-basket cookie for 30 days so your choices are still here when you come back. No advertising cookies.</p>
	<button class="btn btn-small" type="button" id="cookie-ok">OK</button>
</div>
<?php wp_footer(); ?>
</body>
</html>
