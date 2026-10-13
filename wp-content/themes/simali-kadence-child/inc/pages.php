<?php
/**
 * Static page bodies.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared wrapper for the static pages.
 *
 * @param string   $title    Page heading.
 * @param callable $callback Prints the body.
 * @param string   $intro    Optional standfirst under the heading.
 */
function simali_page_shell( $title, $callback, $intro = '' ) {
	get_header();
	echo '<article class="section page"><div class="wrap">';
	echo '<header class="page-head"><h1>' . esc_html( $title ) . '</h1>';
	if ( $intro ) {
		echo '<p class="lede">' . esc_html( $intro ) . '</p>';
	}
	echo '</header>';
	call_user_func( $callback );
	echo '</div></article>';
	get_footer();
}

function simali_page_installation() {
	simali_page_shell(
		'Stone floor installation',
		function () {
			echo '<div class="split"><div>';
			echo '<p>We install natural stone and porcelain floors within a 40-mile radius of Shaftesbury, covering Dorset, Wiltshire and Somerset. Preparation, layout, fitting, grouting and sealing are all done by our own team, so the person who sold you the stone is the person who lays it.</p>';
			echo '<p>Every project is different. We don’t publish fixed installation prices because the right answer depends on the room, the subfloor and the stone. Call <a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a> or send the form below and we will come back with realistic guidance, availability and lead times. Quotations are free.</p>';
			echo '<p>Outside the installation area we still supply nationwide, and we are happy to talk your installer through the preparation a stone floor needs.</p>';
			echo '<h2>Is your postcode in the installation area?</h2>';
			echo do_shortcode( '[simali_postcode]' );
			echo '</div>';
			simali_image( 'bare-floor-with-anti-fracture-mat-installed-part-tiled-shaftesbury.jpg', 'Anti-fracture matting laid over a prepared subfloor, half tiled, during a bathroom installation in Shaftesbury', '4x3', array( 'class' => 'rounded' ) );
			echo '</div>';

			echo '<h2>How an installation runs</h2><ol class="steps process">';
			$steps = array(
				array( 'Survey', 'We visit, measure the rooms and look at what is underneath: screed, boards, levels, damp and any underfloor heating. You get a written quotation for the stone and the fitting, and honest advice if a different stone would suit the house better.', 'bare-floor-prior-to-tile-installation-shaftesbury.jpg', 'Bare floor before tiling in Shaftesbury' ),
				array( 'Preparation', 'A stone floor is only as good as the floor beneath it. We level and prime the subfloor and, where movement or heating calls for it, lay an anti-fracture mat so the stone and grout stay sound for decades.', 'bare-floor-showing-anti-fracture-mat-installed-shaftesbury.jpg', 'Anti-fracture mat installed across a bare floor in Shaftesbury' ),
				array( 'Install', 'The layout is planned before the first stone goes down so cuts fall where they look right. The stone is laid flat and true, sealed, then grouted, with steps, upstands and thresholds finished to match.', 'ludlow-limestone-flooring-before-grout-step-detail.jpg', 'Ludlow limestone laid and sealed before grouting, showing a step detail' ),
				array( 'Aftercare', 'We leave the floor clean and sealed and tell you how to look after it: which cleaner to use, what to avoid and when to re-seal. Years later, if a tile is damaged, we can repair or restore it.', 'bare-floor-fully-tiled-simali-stone.jpg', 'The finished, fully tiled floor' ),
			);
			foreach ( $steps as $index => $step ) {
				echo '<li class="step">';
				simali_image( $step[2], $step[3], '4x3' );
				echo '<div class="step-body"><span>' . esc_html( (string) ( $index + 1 ) ) . '</span><h3>' . esc_html( $step[0] ) . '</h3><p>' . esc_html( $step[1] ) . '</p></div></li>';
			}
			echo '</ol>';

			echo '<h2>Where we install</h2><p>Shaftesbury, Gillingham, Sherborne, Blandford Forum, Sturminster Newton, Salisbury, Tisbury, Frome, Bruton and the villages between them. Recent work stretches from Templecombe and Motcombe to Chilmark and Charlton Marshall. Further afield across the South West, call and we will tell you straight away whether it is realistic.</p>';

			echo '<h2>Recent installs</h2>' . do_shortcode( '[simali_work limit="6"]' );
			echo '<p class="section-foot"><a class="btn btn-secondary" href="' . esc_url( home_url( '/our-work/' ) ) . '">See all the projects</a></p>';
			echo '<h2>What our customers say</h2>';
			simali_render_testimonials();
			echo '<h2 id="quote">Request an installation quote</h2>';
			echo '<p>Most of this form takes a minute. We only need a way to reach you, the postcode and a rough area. Photographs of the room help if you have them.</p>';
			echo do_shortcode( '[simali_quote_form]' );
			echo '<h2>Questions</h2>';
			$faqs = array(
				'Do you install stone you did not supply?' => 'Yes, within the installation area. Tell us what the stone is on the quote form and we will advise on preparation.',
				'Can stone go over underfloor heating?' => 'Yes. Limestone and porcelain both work well with underfloor heating when the subfloor is prepared properly and the heating is commissioned before tiling.',
				'How long does a floor take?' => 'It depends on the area, the preparation and the stone. Random-length flagstones take longer than large-format porcelain. We give a realistic timescale with the quotation.',
			);
			foreach ( $faqs as $q => $a ) {
				echo '<h3>' . esc_html( $q ) . '</h3><p>' . esc_html( $a ) . '</p>';
			}
		},
		'Supplied nationwide, installed properly within 40 miles of Shaftesbury.'
	);
}

function simali_page_samples() {
	simali_page_shell(
		'Order samples',
		function () {
			echo '<div class="split"><div>';
			echo '<p>Choosing flooring isn’t something to rush. Most of our customers take samples home, live with them, and come back once they’re sure. That’s exactly how it should be.</p>';
			echo '<p>Add the stones you are considering from any product page, then send us your details below. Natural stone varies, so a sample shows the character of the stone rather than an exact match; if you want to see a wider spread of tones, the showroom has full-size pieces in natural light.</p>';
			echo '<p>If you have a question about a particular stone, or need samples quickly for a build, call <a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a>.</p>';
			echo '</div>';
			simali_image( 'cathedral2.jpg', 'Cathedral limestone flooring samples showing natural colour variation in olive green and brown tones', '4x3', array( 'class' => 'rounded' ) );
			echo '</div>';
			echo '<h2>Your samples</h2>';
			echo '<p id="sample-status" aria-live="polite"></p>';
			echo do_shortcode( '[simali_sample_form]' );
		},
		'Take them home, see them in your light, and decide slowly.'
	);
}

function simali_page_showroom() {
	simali_page_shell(
		'Shaftesbury showroom',
		function () {
			echo '<div class="split"><div>';
			echo '<p>Our showroom displays a carefully curated range of stone floor samples, so you can see textures, finishes and tones in natural light, and talk through layouts with the person who will supply and lay the floor.</p>';
			echo '<address><strong>Simali Stone Ltd</strong><br>40d–40f Wincombe Business Park<br>Shaftesbury, Dorset SP7 9QJ</address>';
			echo '<p>' . esc_html( simali_hours_copy() ) . '</p>';
			echo '<p>There is parking on the business park outside the unit, with room to load samples into the car.</p>';
			echo '<p><a class="btn" href="' . esc_url( simali_phone_href() ) . '">Call 01747 852557</a></p>';
			echo '</div>';
			simali_image( 'fieldhoue3.jpg', 'Field House limestone flooring, one of the stones on display in the showroom', '4x3', array( 'class' => 'rounded' ) );
			echo '</div>';
			echo '<h2>What to bring</h2><ul class="ticks"><li>Room measurements, or a sketch with the dimensions</li><li>Photographs of the room, the kitchen and any samples you already like</li><li>Paint, worktop or cabinet samples if you have them</li><li>Details of underfloor heating or a new extension, if either is planned</li></ul>';
			echo '<h2>Map</h2><button class="btn btn-secondary" type="button" id="map-load">Load the map</button>';
			echo '<div id="map-slot"></div>';
			echo '<p><a href="https://www.openstreetmap.org/search?query=Wincombe%20Business%20Park%20Shaftesbury%20SP7%209QJ" rel="noopener">Open the map in a new tab</a></p>';
			echo '<h2>Book a visit</h2>';
			echo '<p>Tell us when you would like to come and roughly what you are looking for, and we will confirm by phone or email.</p>';
			echo do_shortcode( '[simali_contact_form subject="Showroom visit"]' );
		},
		'Full-size stones in natural light, 40d–40f Wincombe Business Park.'
	);
}

function simali_page_about() {
	simali_page_shell(
		'About Simali Stone',
		function () {
			echo '<div class="split"><div>';
			echo '<p>At Simali Stone, we don’t just sell tiles — we help you choose the right floor for your home.</p>';
			echo '<p>With over 30 years of hands-on experience in stone and tile installation, every recommendation we make is based on real-world knowledge, not sales targets. From traditional limestone flagstones in period properties to large-format porcelain in modern homes, we guide you through the options properly so you can make a decision you’ll be happy with for years to come.</p>';
			echo '<p>We supply and install stone and porcelain flooring, as well as tiling bathrooms, kitchens and walls across Dorset and the surrounding areas. We also supply nationwide, with a forty-mile fitting distance from our Shaftesbury showroom.</p>';
			echo '</div>';
			simali_image( 'cathedral-limestone-cottage-living-room-floor-teffont.jpg', 'Cathedral limestone floor in a cottage living room in Teffont', '4x3', array( 'class' => 'rounded' ) );
			echo '</div>';
			echo '<h2>Who you will deal with</h2><p>Simali Stone is an independent, family-run business. Stu, who customers mention by name in their reviews, chooses the stone, advises in the showroom and lays the floors, so the advice you get before you buy is the same advice that goes into the fitting.</p>';
			echo '<h2>How we work</h2><div class="pillars">';
			$pillars = array(
				array( 'Proper preparation', 'The floor underneath decides how long the stone lasts. We level, prime and, where it is needed, lay anti-fracture matting before a single tile goes down.' ),
				array( 'Honest advice', 'If a stone will not suit the room, the house or the budget, we say so. Samples go home with you and nobody is rushed into a decision.' ),
				array( 'Quality materials', 'Cathedral, Heritage and the Timeless Stone Collection limestones, CA’ Pietra, and porcelain chosen for how it wears, not just how it photographs.' ),
				array( 'Skilled installation', 'Laid flat, sealed before grouting and finished with the steps, thresholds and upstands that make a floor look like it belongs.' ),
			);
			foreach ( $pillars as $pillar ) {
				echo '<div class="pillar"><h3>' . esc_html( $pillar[0] ) . '</h3><p>' . esc_html( $pillar[1] ) . '</p></div>';
			}
			echo '</div>';
			echo '<p class="pull">No shortcuts. No guesswork. Just floors done properly.</p>';
			echo '<h2>Stockists and partners</h2><p>We are CA’ Pietra stockists and supply Cathedral, Heritage and Timeless Stone Collection limestone. Call <a href="tel:07785795596">07785 795596</a> for the best CA’ Pietra price.</p>';
			echo '<h2>The guide</h2><p>Stu has written down the advice he gives in the showroom: <a href="' . esc_url( home_url( '/guides/' ) ) . '">Everything I Wish Somebody Had Told Me Before I Bought My Stone Floor</a>, a 30-page PDF for £10.</p>';
			echo '<p class="fine">Simali Stone Ltd, Company No. 07158016, VAT 711839046.</p>';
		},
		'Natural stone and porcelain flooring in Dorset, supplied and installed properly.'
	);
}

function simali_page_contact() {
	simali_page_shell(
		'Contact',
		function () {
			echo '<div class="split"><div>';
			echo '<p class="contact-big"><a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a><br><a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a></p>';
			echo '<address>Simali Stone Ltd<br>40d–40f Wincombe Business Park<br>Shaftesbury, Dorset SP7 9QJ</address>';
			echo '<p>' . esc_html( simali_hours_copy() ) . '</p>';
			echo '<p>For CA’ Pietra prices call <a href="tel:07785795596">07785 795596</a>.</p>';
			echo '<p><a href="' . esc_url( simali_facebook_url() ) . '" rel="noopener">Facebook</a> · <a href="' . esc_url( simali_houzz_url() ) . '" rel="noopener">Houzz</a></p>';
			echo '</div><div>';
			echo do_shortcode( '[simali_contact_form]' );
			echo '</div></div>';
		},
		'Call, email or send the form. We reply by phone or email, whichever you prefer.'
	);
}

function simali_page_delivery() {
	simali_page_shell(
		'Delivery',
		function () {
			echo '<p>Stone is sold by the square metre. The quantity you enter is the number of square metres you want, including wastage if you have used the calculator.</p>';
			echo '<p>Our products are delivered by the pallet, to the kerbside. The delivery location must have good access for a lorry and a level, driveable surface. If access is awkward, contact us before you place an order to discuss your options.</p>';
			echo '<p>Pallet delivery is charged at £70+VAT per pallet. A pallet holds a maximum of 1000 kg, which is roughly 15 square metres of stone, and takes 3–5 working days.</p>';
			echo '<h2>Worked example</h2><p>30 square metres is about two pallets, so delivery is £140+VAT. If you add 10% wastage the order becomes 33 m², which is three pallets and £210+VAT. The calculator on each product page works this out for the stone you are buying.</p>';
			echo '<h2>On the day</h2><p>The driver unloads to the kerb with a pallet truck. Have somewhere flat and dry for the pallets to sit, and check the stone before you sign if you can. Any breakages should be reported to us straight away.</p>';
			echo '<h2>Installation customers</h2><p>If we are fitting the floor we arrange delivery as part of the job, so you do not need to order online.</p>';
		},
		'Kerbside pallet delivery, nationwide, in 3–5 working days.'
	);
}

function simali_page_returns() {
	simali_page_shell(
		'Returns',
		function () {
			echo '<p>Our returns policy is governed by our supplier. Please contact us in the first instance should you wish to return any orders or items, and we will confirm the conditions and the costs before anything is collected.</p>';
			echo '<table><thead><tr><th>Condition</th><th>What happens</th></tr></thead><tbody>';
			echo '<tr><td>Returned stone must be undamaged and packed on pallets as it was delivered</td><td>We can arrange collection</td></tr>';
			echo '<tr><td>Breakages</td><td>Any breakages are subject to a breakage fee, set by the supplier and confirmed before collection</td></tr>';
			echo '<tr><td>Collection</td><td>£70 per pallet, paid by the customer</td></tr>';
			echo '<tr><td>Restocking</td><td>A restocking fee set by the supplier, confirmed before collection</td></tr>';
			echo '</tbody></table>';
			echo '<h2>Your statutory rights</h2><p>Nothing here affects your rights under the Consumer Rights Act 2015 or, for orders placed online, the Consumer Contracts Regulations 2013. If the stone arrives damaged or is not as described, tell us within a few days of delivery and we will put it right.</p>';
			echo '<h2>Samples and the guide</h2><p>Samples are not returnable. The digital guide is delivered instantly as a PDF, so it cannot be returned once downloaded.</p>';
		},
		'Contact us first; the supplier’s conditions apply.'
	);
}

function simali_page_faqs() {
	simali_page_shell(
		'FAQs',
		function () {
			$groups = array(
				'Buying' => array(
					'Is the stone priced per square metre?' => 'Yes. Every floor is sold by the square metre and prices include VAT. Where a stone is “price on request” we confirm the price before you commit to anything.',
					'How many samples can I order?' => 'Order the stones you are genuinely choosing between. If an order is unusually large we will call before we send it.',
					'Why do you say choose slowly?' => 'Because once a floor is down you see it every day. Samples go home, you live with them, and you come back when you are sure.',
					'Can I see the stone before I buy?' => 'Yes. The Shaftesbury showroom has full-size pieces in natural light. Call ahead so someone is here to help.',
				),
				'Delivery and returns' => array(
					'How does delivery work?' => 'Kerbside by pallet, £70+VAT per pallet, up to 1000 kg (about 15 m²), in 3–5 working days. The lorry needs a level, driveable surface.',
					'Can I return stone?' => 'Only under the supplier’s conditions: undamaged, packed as delivered, a breakage fee if pieces are damaged, £70 per pallet for collection and a restocking fee. Contact us first.',
					'What does the quantity mean?' => 'Quantity is in square metres, including any wastage you have allowed for.',
				),
				'Installation' => array(
					'How far do you install?' => 'Within 40 miles of Shaftesbury, across Dorset, Wiltshire and Somerset. We supply nationwide.',
					'Do you publish installation prices?' => 'No. Every project is different, so we quote after a survey. Quotations are free.',
					'Does limestone work with underfloor heating?' => 'Yes. Limestone and porcelain both perform well over underfloor heating when the subfloor is prepared properly.',
					'How thick is the stone?' => 'Most limestone is 15 mm or 20–23 mm; traditional flagstones are the thicker option. Porcelain is typically 9–10 mm indoors and 20 mm for patios. Each product page lists the thickness.',
				),
				'Care' => array(
					'Does natural stone need sealing?' => 'Yes. We seal before grouting and again once the floor is finished. Re-seal every few years depending on wear; we will tell you what to use.',
					'How do I clean a stone floor?' => 'A pH-neutral stone cleaner and warm water. Avoid acidic or bleach-based cleaners on limestone and travertine.',
					'Can a damaged floor be repaired?' => 'Usually. We repair and restore existing stone floors as well as laying new ones.',
				),
			);
			foreach ( $groups as $group => $faqs ) {
				echo '<h2>' . esc_html( $group ) . '</h2>';
				foreach ( $faqs as $q => $a ) {
					echo '<details class="faq"><summary>' . esc_html( $q ) . '</summary><p>' . esc_html( $a ) . '</p></details>';
				}
			}
			echo '<p class="section-foot">Something else? Call <a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a> or <a href="' . esc_url( home_url( '/contact/' ) ) . '">send a message</a>.</p>';
		},
		'Straight answers about buying, delivery, installation and care.'
	);
}

function simali_page_guides() {
	simali_page_shell(
		'Stone flooring guides',
		function () {
			$url = home_url( '/product/everything-i-wish-somebody-had-told-me-before-i-bought-my-stone-floor/' );
			echo '<div class="split guide">';
			echo '<a href="' . esc_url( $url ) . '">';
			simali_image( 'molly-on-stone-floor-with-toy.jpg', 'Molly the dog lying on a limestone floor with a toy, the cover photograph of the guide', '4x3', array( 'class' => 'rounded' ) );
			echo '</a><div>';
			echo '<h2>Everything I Wish Somebody Had Told Me Before I Bought My Stone Floor</h2>';
			echo '<p>Thinking about natural stone but don’t know where to start? I’ve spent years supplying, laying, repairing and looking after stone floors, and this is the practical advice I wish somebody had given me before I started.</p>';
			echo '<p>30 pages of straight-talking advice about choosing your stone, measuring, preparation, underfloor heating, laying, grouting, sealing, cleaning, repairs, choosing an installer and avoiding expensive mistakes.</p>';
			echo '<p>I’ve kept it simple. I’m not here to bamboozle you or frighten you into buying anything. Just the things that actually matter when you’re spending your money on a floor you’ll probably live with for decades.</p>';
			echo '<p class="price">£10 inc VAT · instant PDF download</p>';
			echo '<p><a class="btn" href="' . esc_url( $url ) . '">Buy the guide</a></p>';
			echo '</div></div>';
			echo '<h2>What it covers</h2><ul class="ticks two-col"><li>Choosing the right stone for your house</li><li>Limestone, porcelain, terracotta and marble</li><li>Measuring and allowing for waste</li><li>Getting the floor underneath properly prepared</li><li>Underfloor heating</li><li>Planning the layout before you start</li><li>Laying and grouting</li><li>Sealing and cleaning</li><li>Repairing and renovating existing stone</li><li>Choosing the right installer</li><li>Avoiding common mistakes and expensive surprises</li></ul>';
			echo '<p>Read it before you order your stone or start your floor. It could save you a great deal more than a tenner.</p>';
		},
		'The advice we give in the showroom, written down.'
	);
}

function simali_page_landing() {
	get_header();
	while ( have_posts() ) {
		the_post();
		echo '<article class="section page landing"><div class="wrap">';
		echo '<header class="page-head"><h1>' . esc_html( get_the_title() ) . '</h1>';
		if ( has_excerpt() ) {
			echo '<p class="lede">' . esc_html( get_the_excerpt() ) . '</p>';
		}
		echo '</header><div class="prose">';
		the_content();
		echo '</div></div></article>';
	}
	get_footer();
}

function simali_page_thanks() {
	simali_page_shell(
		'Thank you',
		function () {
			echo '<p>We have your message and have emailed a copy to you and to sales@simalistone.co.uk.</p>';
			echo '<p>Next, we read the postcode, the area and any photographs, then call or email the way you asked. If it is urgent, call <a href="' . esc_url( simali_phone_href() ) . '">01747 852557</a>.</p>';
			echo '<p>In the meantime you might like to <a href="' . esc_url( home_url( '/our-work/' ) ) . '">look at recent installations</a> or <a href="' . esc_url( home_url( '/samples/' ) ) . '">order samples</a>.</p>';
		}
	);
}
