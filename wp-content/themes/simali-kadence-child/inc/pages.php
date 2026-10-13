<?php
/**
 * Static page bodies.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function simali_page_shell( $title, $callback ) {
	get_header();
	echo '<article class="section"><div class="wrap">';
	echo '<h1>' . esc_html( $title ) . '</h1>';
	call_user_func( $callback );
	echo '</div></article>';
	get_footer();
}

function simali_page_installation() {
	simali_page_shell(
		'Installation',
		function () {
			echo '<p>Within 40 miles of Shaftesbury. Supply is nationwide.</p>';
			echo do_shortcode( '[simali_postcode]' );
			echo '<h2>How an installation runs</h2><ol class="steps">';
			foreach ( array( 'Survey', 'Preparation', 'Install', 'Aftercare' ) as $step ) {
				echo '<li class="step"><h3>' . esc_html( $step ) . '</h3><p class="todo">TODO(client): what ' . esc_html( strtolower( $step ) ) . ' includes.</p></li>';
			}
			echo '</ol>';
			echo '<h2>Recent installs</h2>' . do_shortcode( '[simali_work limit="6"]' );
			echo '<h2>What our customers say</h2>';
			simali_render_testimonials();
			echo '<h2 id="quote">Request an installation quote</h2>';
			echo '<p>Most of this form takes a minute. We only need a way to reach you, the postcode, and a rough area.</p>';
			echo do_shortcode( '[simali_quote_form]' );
			echo '<h2>Questions</h2><p>Installation is within 40 miles of Shaftesbury, covering Dorset, Wiltshire and Somerset. Outside that area we can still deliver nationwide.</p>';
		}
	);
}

function simali_page_samples() {
	simali_page_shell(
		'Order samples',
		function () {
			echo '<p class="todo">TODO(client): free or paid, maximum per order, and dispatch time.</p>';
			echo '<p id="sample-status" aria-live="polite"></p>';
			echo do_shortcode( '[simali_sample_form]' );
		}
	);
}

function simali_page_showroom() {
	simali_page_shell(
		'Shaftesbury showroom',
		function () {
			simali_placeholder( 'TODO(client): showroom photograph', '3x2' );
			echo '<p>40d–40f Wincome Business Park, Shaftesbury SP7 9QJ</p>';
			echo '<p class="todo">TODO(client): opening hours</p>';
			echo '<p class="todo">TODO(client): parking</p>';
			echo '<p class="todo">TODO(client): appointment policy</p>';
			echo '<h2>What to bring</h2><ul><li>Room measurements</li><li>Photographs of the room</li></ul>';
			echo '<h2>Map</h2><button class="btn btn-secondary" type="button" id="map-load">Load the map</button>';
			echo '<div id="map-slot"></div>';
			echo '<p><a href="https://www.openstreetmap.org/search?query=40d-40f%20Wincome%20Business%20Park%20Shaftesbury%20SP7%209QJ">Open the map in a new tab</a></p>';
			echo '<h2>Book a visit</h2>';
			echo do_shortcode( '[simali_contact_form subject="Showroom visit"]' );
		}
	);
}

function simali_page_about() {
	simali_page_shell(
		'About Simali Stone',
		function () {
			echo '<p>At Simali Stone, we don’t just sell tiles — we help you choose the right floor for your home.</p>';
			echo '<p class="todo">TODO(client): confirm 30 or 35 years. The previous site said both. Use one figure everywhere.</p>';
			echo '<p>We supply and install natural stone, including limestone and flagstones, and porcelain floors. We also tile bathrooms, kitchens and walls, and lay stone and porcelain patios. Matching indoor and outdoor tiles are available.</p>';
			echo '<h2>The team</h2><p>Stu is named in customer reviews.</p><p class="todo">TODO(client): full names, roles and photographs.</p>';
			echo '<h2>How we work</h2><ul><li>Proper preparation</li><li>Honest advice</li><li>Quality materials</li><li>Skilled installation</li></ul>';
			echo '<p>No shortcuts. No guesswork. Just floors done properly.</p>';
			echo '<h2>Accreditations</h2><p class="todo">TODO(client): accreditations, insurance and workmanship guarantee. Nothing is shown until you confirm it.</p>';
		}
	);
}

function simali_page_contact() {
	simali_page_shell(
		'Contact',
		function () {
			echo '<p><a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a><br><a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a></p>';
			echo '<p>40d–40f Wincome Business Park, Shaftesbury SP7 9QJ</p>';
			echo '<p class="todo">TODO(client): opening hours</p>';
			echo do_shortcode( '[simali_contact_form]' );
		}
	);
}

function simali_page_delivery() {
	simali_page_shell(
		'Delivery',
		function () {
			echo '<p>Stone is sold by the square metre. The quantity you enter is the number of square metres you want, including wastage if you have used the calculator.</p>';
			echo '<p>We deliver kerbside, by the pallet. The address needs access for a lorry and a level, driveable surface. If access is awkward, contact us before you order.</p>';
			echo '<p>Delivery is charged by the pallet at £70+VAT. A pallet holds a maximum of 1000 kg, which is roughly 15 square metres of stone, and takes 3–5 working days.</p>';
			echo '<h2>Worked example</h2><p>30 square metres ordered is about two pallets, so delivery is £140+VAT. If you add 10% wastage the order becomes 33.0 m², which is three pallets and £210+VAT. The product page calculator does this for the stone you are buying.</p>';
		}
	);
}

function simali_page_returns() {
	simali_page_shell(
		'Returns',
		function () {
			echo '<p>Returns are governed by our supplier. Contact us before you send anything back.</p>';
			echo '<table><thead><tr><th>Condition</th><th>What happens</th></tr></thead><tbody>';
			echo '<tr><td>Undamaged and re-palletised as delivered</td><td>We can arrange collection</td></tr>';
			echo '<tr><td>Breakages</td><td>A breakage fee applies. <span class="todo">TODO(client): fee</span></td></tr>';
			echo '<tr><td>Collection</td><td>£70 per pallet, paid by the customer</td></tr>';
			echo '<tr><td>Restocking</td><td><span class="todo">TODO(client): restocking fee %</span></td></tr>';
			echo '</tbody></table>';
			echo '<p class="todo">TODO(client/legal): statutory distance-selling wording.</p>';
		}
	);
}

function simali_page_faqs() {
	simali_page_shell(
		'FAQs',
		function () {
			$faqs = array(
				'How far do you install?' => 'Within 40 miles of Shaftesbury, across Dorset, Wiltshire and Somerset. We supply nationwide.',
				'How does delivery work?' => 'Kerbside by pallet, £70+VAT per pallet, up to 1000 kg (about 15 m²), in 3–5 working days. The lorry needs a level, driveable surface.',
				'Can I return stone?' => 'Only by the supplier’s rules: undamaged, packed as delivered, a breakage fee if pieces are damaged, £70 per pallet for collection, and a restocking fee. Contact us first. The restocking percentage is still to be confirmed.',
				'What does the quantity mean?' => 'Quantity is in square metres.',
			);
			foreach ( $faqs as $q => $a ) {
				echo '<h2>' . esc_html( $q ) . '</h2><p>' . esc_html( $a ) . '</p>';
			}
		}
	);
}

function simali_page_guides() {
	simali_page_shell(
		'Guides',
		function () {
			echo '<p>The £10 guide is a digital product, sold separately from the floors.</p>';
			echo '<p><a class="btn" href="' . esc_url( home_url( '/product/everything-i-wish-somebody-had-told-me-before-i-bought-my-stone-floor/' ) ) . '">Everything I Wish Somebody Had Told Me Before I Bought My stone Floor, £10 inc VAT</a></p>';
		}
	);
}

function simali_page_landing() {
	get_header();
	while ( have_posts() ) {
		the_post();
		echo '<article class="section"><div class="wrap">';
		echo '<h1>' . esc_html( get_the_title() ) . '</h1>';
		the_content();
		echo '</div></article>';
	}
	get_footer();
}

function simali_page_thanks() {
	simali_page_shell(
		'Thank you',
		function () {
			echo '<p>We have your message and have emailed a copy to you and to sales@simalistone.co.uk.</p>';
			echo '<p class="todo">TODO(client): how quickly we reply.</p>';
			echo '<p>Next, we read the postcode, the area and any photographs, then call or email the way you asked.</p>';
		}
	);
}
