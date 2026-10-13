<?php
/**
 * Activation, pages and the local catalogue seed.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simali_Setup {
	public static function init() {
		Simali_Content::register();
		Simali_Samples::register();
		Simali_Forms::register();
		Simali_Catalog::register();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'simali seed', array( __CLASS__, 'seed' ) );
			WP_CLI::add_command( 'simali remove-elementor', array( __CLASS__, 'remove_elementor' ) );
		}
	}

	public static function activate() {
		Simali_Forms::cpt();
		Simali_Catalog::rewrites();
		self::open_store();
		flush_rewrite_rules();
		if ( ! get_option( 'simali_badges' ) ) {
			update_option(
				'simali_badges',
				array(
					array( 'name' => "CA' Pietra logo", 'confirmed' => false ),
					array( 'name' => 'Insurance', 'confirmed' => false ),
					array( 'name' => 'Workmanship guarantee', 'confirmed' => false ),
				)
			);
		}
	}

	public static function seed() {
		self::open_store();
		self::attributes();
		self::pages();
		self::testimonials();
		self::products();
		self::cases();
		self::menu();
		flush_rewrite_rules();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::success( 'Simali content is in place.' );
		}
	}

	public static function remove_elementor() {
		$plugins = array( 'elementor/elementor.php', 'elementor-pro/elementor-pro.php' );
		deactivate_plugins( $plugins );
		delete_plugins( $plugins );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::success( 'Elementor plugins deactivated and deleted when they were installed.' );
		}
	}

	/**
	 * The catalogue copied from simalistone.co.uk, keyed by slug.
	 */
	public static function catalog() {
		static $items = null;
		if ( null === $items ) {
			$items = array();
			$data  = json_decode( (string) file_get_contents( SIMALI_CORE_DIR . 'data/catalog-snapshot.json' ), true );
			foreach ( is_array( $data ) ? $data : array() as $item ) {
				if ( ! empty( $item['slug'] ) ) {
					$items[ $item['slug'] ] = $item;
				}
			}
		}
		return $items;
	}

	public static function attribute_map() {
		return array(
			'Colour'             => 'colour',
			'Finish'             => 'finish',
			'Edge'               => 'edge',
			'Thickness'          => 'thickness',
			'Size'               => 'size',
			'Collection'         => 'collection',
			'Underfloor heating' => 'ufh',
			'Indoor / outdoor'   => 'location',
			'Material'           => 'material',
			'Rooms'              => 'rooms',
		);
	}

	private static function attributes() {
		foreach ( self::attribute_map() as $label => $slug ) {
			Simali_Catalog::ensure_attribute( $label, $slug );
		}
	}

	private static function pages() {
		$pages = array(
			'home'            => array( 'Home', '' ),
			'installation'    => array( 'Installation', 'page-templates/installation.php' ),
			'samples'         => array( 'Samples', 'page-templates/samples.php' ),
			'showroom'        => array( 'Showroom', 'page-templates/showroom.php' ),
			'about'           => array( 'About', 'page-templates/about.php' ),
			'contact'         => array( 'Contact', 'page-templates/contact.php' ),
			'delivery'        => array( 'Delivery', 'page-templates/delivery.php' ),
			'returns'         => array( 'Returns', 'page-templates/returns.php' ),
			'faqs'            => array( 'FAQs', 'page-templates/faqs.php' ),
			'guides'          => array( 'Guides', 'page-templates/guides.php' ),
			'thank-you'       => array( 'Thank you', 'page-templates/thanks.php' ),
			'privacy-policy'  => array( 'Privacy', '' ),
			'terms-conditions'=> array( 'Terms', '' ),
			'tiles-shaftesbury' => array( 'Tiles in Shaftesbury', 'page-templates/landing.php' ),
			'stone-flooring-by-simali-stone' => array( 'Stone flooring by Simali Stone', 'page-templates/landing.php' ),
			'limestone-tiles-in-dorset-simali-stone' => array( 'Limestone tiles in Dorset', 'page-templates/landing.php' ),
			'stone-flooring-installation-across-the-south-west' => array( 'Stone flooring installation across the South West', 'page-templates/landing.php' ),
			'timeless-stone-collection-simali-stone' => array( 'Timeless Stone Collection', 'page-templates/landing.php' ),
		);
		$ids = array();
		foreach ( $pages as $slug => $config ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$ids[ $slug ] = $existing->ID;
				if ( '' === trim( (string) $existing->post_content ) && self::landing_copy( $slug ) ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_content' => self::landing_copy( $slug ) ) );
				}
			} else {
				$ids[ $slug ] = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_name'    => $slug,
						'post_title'   => $config[0],
						'post_status'  => 'publish',
						'post_content' => self::landing_copy( $slug ),
					)
				);
			}
			if ( $config[1] ) {
				update_post_meta( $ids[ $slug ], '_wp_page_template', $config[1] );
			}
			$excerpt = self::landing_intro( $slug );
			if ( $excerpt ) {
				wp_update_post( array( 'ID' => $ids[ $slug ], 'post_excerpt' => $excerpt ) );
			}
		}
		self::legal_page( $ids['privacy-policy'] ?? 0, self::privacy_copy() );
		self::legal_page( $ids['terms-conditions'] ?? 0, self::terms_copy() );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	/**
	 * One-line standfirst shown under a landing page title.
	 */
	private static function landing_intro( $slug ) {
		$intros = array(
			'tiles-shaftesbury'      => 'Natural stone and porcelain floor tiles, supplied and installed from our Shaftesbury showroom.',
			'stone-flooring-by-simali-stone' => 'Limestone, flagstone and natural stone floors for homes across Dorset and the South West.',
			'limestone-tiles-in-dorset-simali-stone' => 'Premium limestone tiles, supplied nationwide and installed within 40 miles of Shaftesbury.',
			'stone-flooring-installation-across-the-south-west' => 'Professional stone floor installation for homeowners, builders and developers.',
			'timeless-stone-collection-simali-stone' => 'A curated range of natural limestone flooring with calm tones, natural texture and enduring appeal.',
		);
		return $intros[ $slug ] ?? '';
	}

	private static function landing_copy( $slug ) {
		$houzz = '<a href="https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329">Houzz profile</a>';
		if ( 'tiles-shaftesbury' === $slug ) {
			return '<p>If you are looking for tiles in Shaftesbury, Simali Stone supplies and installs a wide range of natural stone and porcelain floor tiles for homes across Dorset. From traditional limestone floors in farmhouses to modern porcelain tiles in kitchens and hallways, we help homeowners choose tiles that suit the character of their property.</p>'
				. '<p>Many customers visit us after searching for tiles locally, and are surprised to discover the beauty and durability of natural stone flooring.</p>'
				. '<h2>Natural stone floor tiles</h2><p>Natural stone tiles remain one of the most attractive flooring choices for Dorset homes. We regularly supply and install:</p><ul><li>limestone floor tiles</li><li>traditional flagstone tiles</li><li>farmhouse stone flooring</li><li>natural stone kitchen floors</li></ul><p>Stone tiles add warmth and character to both traditional cottages and modern homes. With decades of installation experience, we make sure stone tiles are laid correctly so they last for many years.</p>'
				. '<h2>Porcelain floor tiles</h2><p>Porcelain tiles are a popular choice for kitchens, hallways and open-plan spaces. Modern porcelain can replicate the appearance of natural stone while offering excellent durability, low maintenance, consistent sizing and modern design options.</p>'
				. '<h2>Kitchen and hallway floor tiles</h2><p>Floor tiles work particularly well in areas that receive heavy foot traffic. We regularly install tiles in kitchens, hallways, utility rooms and open-plan living spaces.</p>'
				. '<h2>Supply and installation</h2><p>Unlike many tile shops, Simali Stone offers both tile supply and professional installation, so you get advice on choosing the correct tile for your home, layout and room design, and preparation and installation methods.</p>'
				. '<h2>Visit our Shaftesbury showroom</h2><p>Customers searching for tiles in Shaftesbury are welcome to visit our showroom to see a selection of natural stone flooring and porcelain tiles. We supply and install across Shaftesbury and the surrounding Dorset countryside, including farmhouses, cottages and modern homes.</p>'
				. '<h2>Floor tiles installed in Shaftesbury and Dorset homes</h2><p>The photographs below show real tile floors supplied and installed by Simali Stone in homes across Shaftesbury and Dorset.</p>[simali_work limit="9" material="porcelain"]'
				. '<p>For advice on porcelain tiles in Shaftesbury or stone flooring installations, call <a href="tel:01747852557">01747 852557</a>.</p>';
		}
		if ( 'stone-flooring-by-simali-stone' === $slug ) {
			return '<p>Stone flooring by Simali Stone combines natural beauty, long-term durability and professional installation expertise. With over 30 years of hands-on experience, we supply and install premium limestone flooring, flagstone flooring, sandstone and natural stone floors for homes across Dorset and the South West. Whether you are renovating a period cottage or designing a contemporary open-plan home, our stone flooring collections offer timeless character and exceptional performance.</p>'
				. '<h2>Why choose stone flooring?</h2><p>Stone flooring is one of the most durable and visually striking flooring options available. Unlike manufactured surfaces, natural stone develops character over time while maintaining structural strength and longevity.</p><ul><li>Exceptional durability</li><li>Compatibility with underfloor heating</li><li>Natural slip resistance</li><li>Low maintenance when sealed correctly</li><li>Unique tonal variation in every tile</li></ul><p>Stone floors work beautifully in kitchens, hallways, living rooms, conservatories and open-plan spaces.</p>'
				. '<h2>Our limestone flooring collections</h2><p>Limestone flooring remains one of the most popular choices for homeowners seeking warmth and refinement. Explore our collections:</p><ul><li><a href="/product/cathedral/">Cathedral limestone flooring</a></li><li><a href="/product/heritage/">Heritage limestone flooring</a></li><li><a href="/timeless-stone-collection-simali-stone/">Timeless Stone Collection flagstones</a></li></ul><p>Each range offers different tones, finishes and formats, from classic random-length flagstones to large-format contemporary tiles.</p>'
				. '<h2>Flagstone flooring and traditional stone floors</h2><p>Flagstone flooring provides a classic, traditional appearance suited to period homes and country properties. With gently tumbled edges and natural variation, flagstones deliver authenticity and depth. Our flagstone options include brushed, honed and textured finishes suitable for both indoor and outdoor use.</p>'
				. '<h2>Installation across the South West</h2><p>Simali Stone provides professional stone floor installation within a 40-mile radius of Shaftesbury, covering Dorset, Somerset, Wiltshire and the surrounding areas. We also supply stone flooring nationwide. Every floor is laid flat, correctly prepared and professionally sealed.</p>'
				. '<h2>Visit our Shaftesbury showroom</h2><p>Our showroom displays a carefully curated range of stone floor samples, so you can see textures, finishes and tones in natural light. For advice, samples or quotations call <a href="tel:01747852557">01747 852557</a>.</p>'
				. '<h2>Stone flooring installations by Simali Stone</h2><p>Real examples of stone floors completed across Dorset and the South West. More are on our ' . $houzz . '.</p>[simali_work limit="9" material="limestone"]';
		}
		if ( 'limestone-tiles-in-dorset-simali-stone' === $slug ) {
			return '<p>If you are looking for high-quality limestone tiles in Dorset, Simali Stone supplies and installs carefully selected natural stone flooring across Shaftesbury, Dorset and the surrounding counties. With over 30 years of experience in natural stone, we provide durable, elegant limestone tiles suitable for traditional homes, modern extensions and open-plan living spaces.</p><p>Our limestone tiles are available for nationwide supply, with professional installation within a 40-mile radius of Shaftesbury covering Dorset, Wiltshire and Somerset.</p>'
				. '<h2>Why choose limestone tiles in Dorset homes</h2><p>Limestone offers a timeless, natural aesthetic that complements both period cottages and contemporary builds. Dorset homes often feature warm brick, rendered walls and local green stone, making limestone flooring an ideal interior partner.</p><ul><li>Natural tonal variation</li><li>Excellent compatibility with underfloor heating</li><li>Durable 15mm and 22mm thickness options</li><li>Suitable for kitchens, hallways and open-plan areas</li><li>Long lifespan when properly installed and sealed</li></ul>'
				. '<h2>Our limestone tile ranges</h2><p>We offer several established ranges, including <a href="/product/cathedral/">Cathedral limestone</a>, <a href="/product/heritage/">Heritage limestone</a> and the <a href="/timeless-stone-collection-simali-stone/">Timeless Stone Collection</a>, in carefully selected sizes and finishes, from refined honed surfaces to gently brushed traditional flagstones.</p>'
				. '<h2>Limestone tile installation across Dorset</h2><p>We install within a 40-mile radius of Shaftesbury, covering Shaftesbury, Sherborne, Blandford Forum, Gillingham, Sturminster Newton and the surrounding Dorset villages. Our fitting team takes care of substrate preparation, underfloor heating compatibility and long-term performance.</p>'
				. '<h2>Visit our Dorset stone flooring showroom</h2><p>You are welcome to visit our Shaftesbury showroom to view full-size samples and discuss layout options. Call <a href="tel:01747852557">01747 852557</a> for advice, samples or quotations.</p>'
				. '<h2>Limestone tiles installed in Dorset homes</h2>[simali_work limit="9" material="limestone"]';
		}
		if ( 'stone-flooring-installation-across-the-south-west' === $slug ) {
			return '<p>Simali Stone Limited provide professional stone flooring installation across the South West, working with homeowners, builders and property developers who want a high-quality, long-lasting finish. From traditional limestone to contemporary stone tiles, our experienced team installs stone flooring with care, precision and attention to detail.</p>'
				. '<h2>Stone flooring installers you can trust</h2><p>With decades of experience supplying and installing stone flooring, we take pride in workmanship that stands the test of time. Every installation is carefully planned and carried out using proven methods, so your floor looks beautiful and performs exactly as it should for years to come.</p>'
				. '<h2>Where we install</h2><p>We regularly carry out stone flooring installations across Dorset, Somerset, Wiltshire, Gloucestershire, Worcestershire, Bath, Bristol, Swindon, Exeter and the surrounding areas. Installation work is focused within a realistic travel radius of Shaftesbury; we also supply stone flooring nationwide.</p>'
				. '<p>Many of our installations feature premium limestone ranges such as <a href="/product/cathedral/">Cathedral stone</a>, chosen for its timeless character and suitability for both period and contemporary homes. We are happy to advise on the most appropriate stone for your project.</p>'
				. '<h2>Recent stone flooring installations</h2>[simali_work limit="12"]'
				. '<h2>Why choose Simali Stone</h2><p>We are an independent stone flooring specialist based in Dorset, combining hands-on installation experience with carefully selected stone to deliver durable, timeless floors for residential and commercial projects. Call <a href="tel:01747852557">01747 852557</a> for expert advice and a free quotation, or visit the Shaftesbury showroom. More projects are on our ' . $houzz . '.</p>';
		}
		if ( 'timeless-stone-collection-simali-stone' === $slug ) {
			return self::timeless_copy();
		}
		return '';
	}

	/**
	 * The Timeless Stone Collection page, built from the catalogue so every stone links to its product.
	 */
	private static function timeless_copy() {
		$html = '<p>The Timeless Stone Collection is a carefully curated range of natural limestone flooring, chosen for its classic appearance, understated finishes and long-lasting appeal. These stones sit comfortably in both traditional and contemporary interiors, offering calm tones, natural texture and timeless character.</p>'
			. '<p>Supplied by Simali Stone, the collection brings together some of the most popular and recognisable limestone floors available, trusted by homeowners, designers and installers across the UK. Many are supplied in traditional random-length formats, ideal for classic flagstone layouts, and several are used outdoors as well as in.</p>'
			. '<h2>The stones</h2>';
		foreach ( self::catalog() as $item ) {
			if ( empty( $item['collection'] ) ) {
				continue;
			}
			$image = $item['images'][0] ?? null;
			$url   = home_url( '/product/' . $item['slug'] . '/' );
			$html .= '<div class="stone-row">';
			if ( $image && Simali_Media::url( $image['file'] ) ) {
				list( $w, $h ) = Simali_Media::size( $image['file'] );
				$html .= sprintf( '<figure class="stone-media"><a href="%s"><img class="ratio-4x3" src="%s" alt="%s" width="%d" height="%d" loading="lazy"></a></figure>', esc_url( $url ), esc_url( Simali_Media::url( $image['file'] ) ), esc_attr( $image['alt'] ), $w, $h );
			}
			$html .= '<div><h3><a href="' . esc_url( $url ) . '">' . esc_html( preg_replace( '/\s+[–-]\s+Timeless Stone Collection$/u', '', $item['name'] ) ) . '</a></h3>';
			$html .= '<p>' . esc_html( $item['short'] ) . '</p>';
			$bits = array();
			if ( ! empty( $item['thickness'] ) ) {
				$bits[] = implode( ', ', $item['thickness'] );
			}
			if ( ! empty( $item['finish'] ) ) {
				$bits[] = implode( ', ', $item['finish'] );
			}
			if ( $bits ) {
				$html .= '<p class="spec">' . esc_html( implode( ' · ', $bits ) ) . '</p>';
			}
			$html .= '<p><a class="btn btn-secondary" href="' . esc_url( $url ) . '">View ' . esc_html( preg_replace( '/\s+[–-].*$/u', '', $item['name'] ) ) . '</a></p></div></div>';
		}
		$html .= '<h2>Samples and advice</h2><p>Every stone in the collection is price on request. Order samples, call <a href="tel:01747852557">01747 852557</a> or visit the Shaftesbury showroom to see the stones in natural light.</p>';
		return $html;
	}

	private static function testimonials() {
		if ( get_posts( array( 'post_type' => 'simali_testimonial', 'posts_per_page' => 1 ) ) ) {
			return;
		}
		foreach ( simali_verbatim_testimonials() as $index => $item ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'simali_testimonial',
					'post_status'  => 'publish',
					'post_title'   => $item['name'],
					'post_content' => $item['text'],
					'menu_order'   => $index,
				)
			);
			update_post_meta( $id, '_simali_rating', $item['rating'] );
		}
	}

	private static function cases() {
		$path  = SIMALI_CORE_DIR . 'data/case-seeds.json';
		$cases = json_decode( (string) file_get_contents( $path ), true );
		if ( ! is_array( $cases ) ) {
			return;
		}
		$existing = array();
		foreach ( get_posts( array( 'post_type' => 'simali_case', 'posts_per_page' => 200, 'post_status' => 'any' ) ) as $post ) {
			$existing[ $post->post_title ] = $post->ID;
		}
		foreach ( $cases as $index => $case ) {
			$title = trim( (string) ( $case['title'] ?? '' ) );
			if ( '' === $title ) {
				continue;
			}
			$story = trim( (string) ( $case['story'] ?? '' ) );
			if ( isset( $existing[ $title ] ) ) {
				$id = $existing[ $title ];
			} else {
				$id = wp_insert_post(
					array(
						'post_type'    => 'simali_case',
						'post_status'  => 'publish',
						'post_title'   => $title,
						'post_content' => $story ? '<p>' . esc_html( $story ) . '</p>' : '',
						'menu_order'   => $index,
						'post_date'    => gmdate( 'Y-m-d H:i:s', time() - $index * DAY_IN_SECONDS ),
					)
				);
			}
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, '_simali_town', $case['town'] ?? '' );
			update_post_meta( $id, '_simali_room', $case['room'] ?? '' );
			update_post_meta( $id, '_simali_material', $case['material'] ?? '' );
			update_post_meta( $id, '_simali_alt', $case['alt'] ?? $title );
			if ( ! empty( $case['product'] ) ) {
				$product = get_page_by_path( $case['product'], OBJECT, 'product' );
				if ( $product ) {
					update_post_meta( $id, '_simali_product', $product->ID );
				}
			}
			if ( ! has_post_thumbnail( $id ) && ! empty( $case['image'] ) ) {
				$thumb = Simali_Media::attach( array( 'file' => $case['image'], 'alt' => $case['alt'] ?? $title, 'source' => $case['source'] ?? '' ), $id );
				if ( $thumb ) {
					set_post_thumbnail( $id, $thumb );
				} else {
					update_post_meta( $id, '_simali_image', $case['source'] ?? '' );
				}
			}
			if ( ! empty( $case['gallery'] ) && ! get_post_meta( $id, '_simali_gallery', true ) ) {
				$ids = Simali_Media::attach_all( (array) $case['gallery'], $id );
				if ( $ids ) {
					update_post_meta( $id, '_simali_gallery', $ids );
				}
			}
			if ( ! empty( $case['before'] ) && ! get_post_meta( $id, '_simali_before', true ) ) {
				$before = Simali_Media::attach( array( 'file' => $case['before'], 'alt' => 'Before: ' . $title ), $id );
				if ( $before ) {
					update_post_meta( $id, '_simali_before', $before );
				}
			}
		}
	}

	private static function products() {
		if ( ! class_exists( 'WC_Product_Simple' ) ) {
			return;
		}
		foreach ( array( 'Limestone' => 'limestone', 'Porcelain' => 'porcelain', 'Outdoor & Patios' => 'outdoor', 'Stone Flooring Guides' => 'stone-flooring-guides' ) as $name => $slug ) {
			if ( ! term_exists( $slug, 'product_cat' ) ) {
				wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			}
		}
		foreach ( self::catalog() as $item ) {
			$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );
			if ( $existing ) {
				$product = wc_get_product( $existing->ID );
			} else {
				$product = new WC_Product_Simple();
				$product->set_name( $item['name'] );
				$product->set_slug( $item['slug'] );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$product->set_stock_status( 'instock' );
				if ( (float) $item['price'] > 0 ) {
					$product->set_regular_price( (string) $item['price'] );
				}
				if ( ! empty( $item['virtual'] ) ) {
					$product->set_virtual( true );
				}
				$product->set_featured( ! empty( $item['featured'] ) );
			}
			if ( ! $product ) {
				continue;
			}
			self::apply_catalog_item( $product, $item );
		}
	}

	/**
	 * Bring a product in line with the catalogue: copy, photographs, categories and specification terms.
	 * Prices are only set when a product is created, so live pricing is never overwritten.
	 */
	private static function apply_catalog_item( WC_Product $product, array $item ) {
		$short = (string) ( $item['short'] ?? '' );
		$long  = (string) ( $item['description'] ?? '' );
		if ( '' !== $long && false === strpos( $long, '<p>' ) && '' !== $short ) {
			$long = '<p>' . esc_html( $short ) . '</p>' . $long;
		}
		if ( '' === trim( wp_strip_all_tags( $product->get_short_description() ) ) ) {
			$product->set_short_description( $short );
		}
		if ( '' === trim( wp_strip_all_tags( $product->get_description() ) ) ) {
			$product->set_description( $long ? $long : $short );
		}
		$id = $product->save();

		if ( ! empty( $item['max_price'] ) && (float) $item['max_price'] > (float) $item['price'] ) {
			update_post_meta( $id, '_simali_price_max', $item['max_price'] );
		}
		if ( ! empty( $item['origin'] ) ) {
			update_post_meta( $id, '_simali_origin', $item['origin'] );
		}
		if ( ! empty( $item['meta_description'] ) ) {
			update_post_meta( $id, '_simali_meta_description', $item['meta_description'] );
		}

		$default_cat = (int) get_option( 'default_product_cat' );
		$current     = array_diff( (array) wp_get_object_terms( $id, 'product_cat', array( 'fields' => 'ids' ) ), array( $default_cat ) );
		if ( ! $current ) {
			$cats = array();
			foreach ( (array) $item['categories'] as $slug ) {
				$slug = 'timeless-stone-collection' === $slug ? 'limestone' : $slug;
				$term = get_term_by( 'slug', $slug, 'product_cat' );
				if ( $term ) {
					$cats[] = (int) $term->term_id;
				}
			}
			if ( $cats ) {
				wp_set_object_terms( $id, array_unique( $cats ), 'product_cat' );
			}
		}

		$terms = array(
			'pa_colour'     => $item['colours'] ?? array(),
			'pa_finish'     => $item['finish'] ?? array(),
			'pa_edge'       => $item['edge'] ?? array(),
			'pa_thickness'  => $item['thickness'] ?? array(),
			'pa_size'       => $item['sizes'] ?? array(),
			'pa_collection' => ! empty( $item['collection'] ) ? array( $item['collection'] ) : array(),
			'pa_ufh'        => ! empty( $item['ufh'] ) ? array( 'Suitable' ) : array(),
			'pa_location'   => $item['location'] ?? array(),
			'pa_material'   => ! empty( $item['material'] ) ? array( $item['material'] ) : array(),
			'pa_rooms'      => $item['rooms'] ?? array(),
		);
		$attributes = array();
		$position   = 0;
		foreach ( $terms as $taxonomy => $values ) {
			$values = array_values( array_filter( array_map( 'strval', (array) $values ) ) );
			wp_set_object_terms( $id, $values, $taxonomy, false );
			if ( ! $values || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$term_ids = wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $term_ids ) || ! $term_ids ) {
				continue;
			}
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( array_map( 'intval', $term_ids ) );
			$attribute->set_position( $position++ );
			$attribute->set_visible( true );
			$attribute->set_variation( false );
			$attributes[] = $attribute;
		}
		if ( ! empty( $item['tags'] ) ) {
			wp_set_object_terms( $id, array_values( array_unique( (array) $item['tags'] ) ), 'product_tag', false );
		}
		$product = wc_get_product( $id );
		if ( $product ) {
			$product->set_attributes( $attributes );
			if ( ! $product->get_image_id() && ! empty( $item['images'] ) ) {
				$ids = Simali_Media::attach_all( (array) $item['images'], $id );
				if ( $ids ) {
					$product->set_image_id( array_shift( $ids ) );
					$product->set_gallery_image_ids( $ids );
				}
			}
			$product->save();
		}
	}

	private static function open_store() {
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
		update_option( 'woocommerce_currency', 'GBP' );
		update_option( 'woocommerce_default_country', 'GB' );
		update_option( 'woocommerce_price_decimal_sep', '.' );
		update_option( 'woocommerce_price_thousand_sep', ',' );
		update_option( 'woocommerce_dimension_unit', 'cm' );
		update_option( 'woocommerce_weight_unit', 'kg' );
	}

	private static function legal_page( $id, $content ) {
		if ( ! $id || get_post_meta( $id, '_simali_legal', true ) ) {
			return;
		}
		wp_update_post(
			array(
				'ID'           => $id,
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
		update_post_meta( $id, '_simali_legal', '1' );
	}

	private static function privacy_copy() {
		return '<p>Simali Stone Ltd, 40d–40f Wincombe Business Park, Shaftesbury SP7 9QJ. Company No. 07158016. VAT 711839046. Email <a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a>. Telephone <a href="tel:01747852557">01747 852557</a>.</p>'
			. '<h2>What we collect</h2><p>We collect personal information only when you give it to us: your name, email address, telephone number, address and postcode when you place an order, request samples, ask for a quote or send us a message. You can browse the site without giving us any of this.</p>'
			. '<h2>How we use it</h2><ul><li>To reply to your enquiry, prepare a quote or send samples.</li><li>To process and deliver an order, and to tell you about that order.</li><li>To keep a record of what we have agreed with you.</li></ul><p>We do not sell, trade or rent your personal information. We share it only with the people who need it to do the job you asked for, such as a delivery company, a payment processor or our stone suppliers.</p>'
			. '<h2>Cookies</h2><p>The sample basket uses one cookie so the samples you choose are still there when you come back within 30 days. The shop uses WooCommerce cookies to run the basket and checkout. We do not set advertising cookies. You can block cookies in your browser; parts of the site may then not work.</p>'
			. '<h2>How long we keep it</h2><p>Enquiries are kept for as long as we need them to deal with your project, and orders for as long as tax and accounting law require. Ask and we will delete anything we do not have to keep.</p>'
			. '<h2>Security</h2><p>The site is served over an encrypted connection. We take sensible steps to protect the information you give us from unauthorised access, alteration, disclosure or destruction.</p>'
			. '<h2>Third-party websites</h2><p>Links to other websites, such as our Houzz or Google pages, take you to sites with their own privacy policies which we do not control.</p>'
			. '<h2>Your rights</h2><p>You can ask to see the information we hold about you, have it corrected or deleted, or object to how we use it. Email <a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a>.</p>'
			. '<h2>Changes</h2><p>We may update this policy from time to time. The latest version is always on this page.</p>';
	}

	private static function terms_copy() {
		return '<h2>Using this website</h2><p>By using simalistone.co.uk you agree to these terms. The text, photographs and other material on the site belong to Simali Stone Ltd and are for your personal, non-commercial use. Please do not copy, modify or republish them without asking.</p>'
			. '<h2>Products and prices</h2><p>Stone and porcelain are sold by the square metre. Prices shown include VAT. Where a product is marked “price on request” we will confirm the price before you commit to anything. Natural stone varies in colour and texture; samples and photographs are a guide, not an exact match.</p>'
			. '<h2>Delivery and returns</h2><p>Delivery and returns are described on the <a href="/delivery/">delivery</a> and <a href="/returns/">returns</a> pages, and those pages form part of these terms.</p>'
			. '<h2>Accuracy</h2><p>We do our best to keep the site accurate and up to date, but it may contain technical, typographical or photographic errors, and we may change it at any time without notice.</p>'
			. '<h2>Links</h2><p>Links to other websites are provided for convenience. A link is not an endorsement, and we are not responsible for the content of linked sites.</p>'
			. '<h2>Liability</h2><p>Nothing in these terms limits your statutory rights as a consumer. Beyond those rights, we are not liable for loss arising from your use of, or inability to use, this website.</p>'
			. '<h2>Privacy</h2><p>How we handle your personal information is set out in our <a href="/privacy-policy/">privacy policy</a>.</p>'
			. '<h2>Governing law</h2><p>These terms are governed by the laws of England and Wales, and the courts of England and Wales have jurisdiction over any dispute relating to them.</p>';
	}

	private static function menu() {
		$menu = wp_get_nav_menu_object( 'Primary' );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Primary' );
		if ( ! wp_get_nav_menu_items( $menu_id ) ) {
			foreach ( array( 'Limestone' => '/limestone/', 'Porcelain' => '/porcelain/', 'Outdoor & Patios' => '/outdoor/', 'Installation' => '/installation/', 'Our Work' => '/our-work/', 'Showroom' => '/showroom/', 'About' => '/about/' ) as $label => $path ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $label,
						'menu-item-url'    => home_url( $path ),
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
					)
				);
			}
		}
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
