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
					array( 'name' => 'Years trading', 'confirmed' => false ),
					array( 'name' => 'Insurance', 'confirmed' => false ),
					array( 'name' => 'Workmanship guarantee', 'confirmed' => false ),
				)
			);
		}
	}

	public static function seed() {
		self::open_store();
		self::pages();
		self::testimonials();
		self::cases();
		self::products();
		self::refresh_specs();
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
		);
		$ids = array();
		foreach ( $pages as $slug => $config ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$ids[ $slug ] = $existing->ID;
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
		}
		self::legal_page( $ids['privacy-policy'] ?? 0, self::privacy_copy() );
		self::legal_page( $ids['terms-conditions'] ?? 0, self::terms_copy() );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	private static function landing_copy( $slug ) {
		$houzz = '<a href="https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329">Houzz profile</a>';
		$years = '<span class="todo">TODO(client): confirm 30 or 35 years</span>';
		if ( 'tiles-shaftesbury' === $slug ) {
			return '<p>If you are looking for tiles in Shaftesbury, Simali Stone supplies and installs natural stone and porcelain floor tiles for homes across Dorset.</p><ul><li>traditional flagstone tiles</li><li>farmhouse stone flooring</li><li>natural stone kitchen floors</li></ul><p>For advice, call <a href="tel:01747852557">01747 852557</a>.</p>';
		}
		if ( 'stone-flooring-by-simali-stone' === $slug ) {
			return '<p>Stone flooring by Simali Stone. ' . $years . '. We supply and install limestone, flagstone, sandstone and natural stone floors for homes across Dorset and the South West, and we supply nationwide.</p><ul><li>Exceptional durability</li><li>Compatibility with underfloor heating</li><li>Natural slip resistance</li><li>Low maintenance when sealed correctly</li><li>Unique tonal variation in every tile</li></ul><p>Installation is within 40 miles of Shaftesbury. See recent work on our ' . $houzz . '.</p>';
		}
		if ( 'limestone-tiles-in-dorset-simali-stone' === $slug ) {
			return '<p>Limestone tiles in Dorset, supplied nationwide and installed within 40 miles of Shaftesbury, covering Dorset, Wiltshire and Somerset. ' . $years . '.</p><ul><li>Excellent compatibility with underfloor heating</li><li>Durable 15mm and 22mm thickness options, where the range offers them</li><li>Suitable for kitchens, hallways and open-plan areas</li></ul>';
		}
		if ( 'stone-flooring-installation-across-the-south-west' === $slug ) {
			return '<p>Simali Stone Limited install stone flooring across the South West for homeowners, builders and property developers. Installation is focused within a realistic travel radius. We also supply nationwide.</p><p>Call <a href="tel:01747852557">01747 852557</a> or visit the Shaftesbury showroom. More projects are on our ' . $houzz . '.</p>';
		}
		return '';
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
			update_post_meta( $id, '_simali_source', 'TODO(client)' );
			update_post_meta( $id, '_simali_date', 'TODO(client)' );
		}
	}

	private static function cases() {
		if ( get_posts( array( 'post_type' => 'simali_case', 'posts_per_page' => 1 ) ) ) {
			return;
		}
		$path = SIMALI_CORE_DIR . 'data/case-seeds.json';
		$cases = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $cases ) ) {
			return;
		}
		foreach ( $cases as $case ) {
			$alt = $case['alt'] ?? '';
			if ( '' === $alt || strlen( $alt ) < 12 ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'simali_case',
					'post_status'  => 'publish',
					'post_title'   => $alt,
					'post_content' => '<p class="todo">TODO(client): short project story, m² and a testimonial only if you have one for this job.</p>',
				)
			);
			update_post_meta( $id, '_simali_image', $case['image'] ?? '' );
			update_post_meta( $id, '_simali_town', self::town_from_caption( $alt ) );
			update_post_meta( $id, '_simali_room', self::room_from_caption( $alt ) );
			update_post_meta( $id, '_simali_material', self::material_from_caption( $alt ) );
		}
	}

	private static function town_from_caption( $alt ) {
		foreach ( array( 'Charlton Marshall', 'Shaftesbury', 'Bruton', 'Tisbury', 'Salisbury', 'Marnhull', 'Dorset', 'Somerset' ) as $town ) {
			if ( false !== stripos( $alt, $town ) ) {
				return $town;
			}
		}
		return '';
	}

	private static function room_from_caption( $alt ) {
		$map = array( 'patio' => 'outdoor', 'hallway' => 'hall', 'conservatory' => 'living', 'open plan' => 'living', 'kitchen' => 'kitchen', 'bathroom' => 'bathroom' );
		foreach ( $map as $needle => $room ) {
			if ( false !== stripos( $alt, $needle ) ) {
				return $room;
			}
		}
		return '';
	}

	private static function material_from_caption( $alt ) {
		foreach ( array( 'porcelain', 'travertine', 'sandstone', 'limestone' ) as $material ) {
			if ( false !== stripos( $alt, $material ) ) {
				return $material;
			}
		}
		return '';
	}

	private static function products() {
		if ( ! class_exists( 'WC_Product_Simple' ) ) {
			return;
		}
		if ( wp_count_posts( 'product' )->publish > 0 ) {
			return;
		}
		foreach ( array( 'Limestone' => 'limestone', 'Porcelain' => 'porcelain', 'Outdoor & Patios' => 'outdoor', 'Stone Flooring Guides' => 'stone-flooring-guides' ) as $name => $slug ) {
			if ( ! term_exists( $slug, 'product_cat' ) ) {
				wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			}
		}
		foreach ( array( 'Colour' => 'colour', 'Finish' => 'finish', 'Thickness' => 'thickness', 'Size' => 'size', 'Collection' => 'collection', 'Underfloor heating' => 'ufh', 'Indoor / outdoor' => 'location', 'Edge' => 'edge', 'Material' => 'material', 'Rooms' => 'rooms' ) as $label => $slug ) {
			Simali_Catalog::ensure_attribute( $label, $slug );
		}
		$snapshot = json_decode( file_get_contents( SIMALI_CORE_DIR . 'data/catalog-snapshot.json' ), true );
		$featured = array( 'bellissimo-porcelain-tile-600x1200mm', 'barcelona-porcelain-feature-tile-223mm', 'athens-marble-effect-porcelain-tile', 'arbia-porcelain-tile', 'alba-ivory-porcelain-tile-600x400mm', 'alba-silver-porcelain-tile-600x400mm', 'moleanus-blue', 'worcester-limestone-flooring' );
		foreach ( $snapshot as $item ) {
			$product = new WC_Product_Simple();
			$product->set_name( $item['name'] );
			$product->set_slug( $item['slug'] );
			$product->set_description( $item['short'] );
			$product->set_short_description( $item['short'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_stock_status( 'instock' );
			if ( (float) $item['price'] > 0 ) {
				$product->set_regular_price( (string) $item['price'] );
			}
			if ( in_array( 'stone-flooring-guides', $item['categories'], true ) ) {
				$product->set_virtual( true );
			}
			if ( in_array( $item['slug'], $featured, true ) ) {
				$product->set_featured( true );
			}
			$id = $product->save();
			if ( ! empty( $item['max_price'] ) && (float) $item['max_price'] > (float) $item['price'] ) {
				update_post_meta( $id, '_simali_price_max', $item['max_price'] );
			}
			$cats = array();
			foreach ( $item['categories'] as $slug ) {
				if ( 'timeless-stone-collection' === $slug ) {
					$limestone = get_term_by( 'slug', 'limestone', 'product_cat' );
					if ( $limestone ) {
						$cats[] = (int) $limestone->term_id;
					}
					wp_set_object_terms( $id, 'Timeless Stone Collection', 'pa_collection', true );
				} else {
					$term = get_term_by( 'slug', $slug, 'product_cat' );
					if ( $term ) {
						$cats[] = (int) $term->term_id;
					}
				}
			}
			if ( $cats ) {
				wp_set_object_terms( $id, $cats, 'product_cat' );
			}
			self::terms_from_copy( $id, $item['short'] );
		}
	}

	private static function open_store() {
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
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
		return '<p>Simali Stone Ltd, 40d–40f Wincome Business Park, Shaftesbury SP7 9QJ. Company No. 07158016. VAT 711839046. Email <a href="mailto:sales@simalistone.co.uk">sales@simalistone.co.uk</a>. Telephone <a href="tel:01747852557">01747 852557</a>.</p><p>Details sent on an enquiry or sample form are used to reply to that enquiry. A sample-basket cookie keeps the samples you chose for 30 days.</p><p class="todo">TODO(client/legal): privacy policy wording, including how long enquiries are kept and any cookies the host adds.</p>';
	}

	private static function terms_copy() {
		return '<p>The current website terms say that using simalistone.co.uk means you agree to them, that the materials are for personal viewing, and that they are provided as they are. They also say a link is not an endorsement, and that the materials and these terms may change.</p><p>Orders are measured in square metres. Delivery and returns are on those pages, and they do not add promises beyond what is written there.</p><p class="todo">TODO(client/legal): replace the published “laws of gb” clause with proper governing-law wording. The old page was a generator template and is not reproduced here.</p>';
	}

	private static function refresh_specs() {
		$posts = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
			)
		);
		foreach ( $posts as $post ) {
			foreach ( array( 'pa_thickness', 'pa_finish', 'pa_size', 'pa_ufh' ) as $taxonomy ) {
				wp_set_object_terms( $post->ID, array(), $taxonomy );
			}
			$copy = get_post_field( 'post_excerpt', $post->ID );
			if ( ! $copy ) {
				$copy = get_post_field( 'post_content', $post->ID );
			}
			self::terms_from_copy( $post->ID, (string) $copy );
		}
	}

	private static function terms_from_copy( $id, $copy ) {
		if ( preg_match_all( '/(?<!\d)(\d{2}\s*[–-]\s*\d{2}|\d{2})\s*mm\b/u', $copy, $matches ) ) {
			$thickness = array();
			foreach ( $matches[1] as $value ) {
				$thickness[] = preg_replace( '/\s+/', '', str_replace( '-', '–', $value ) ) . 'mm';
			}
			wp_set_object_terms( $id, array_unique( $thickness ), 'pa_thickness', false );
		}
		foreach ( array( 'sandblasted', 'antiqued', 'honed', 'riven', 'brushed' ) as $finish ) {
			if ( false !== stripos( $copy, $finish ) ) {
				wp_set_object_terms( $id, ucfirst( $finish ), 'pa_finish', true );
			}
		}
		if ( false !== stripos( $copy, 'underfloor heating' ) ) {
			wp_set_object_terms( $id, 'Suitable', 'pa_ufh', true );
		}
		if ( preg_match_all( '/\d{3,4}\s*x\s*\d{3,4}/', $copy, $sizes ) ) {
			$labels = array();
			foreach ( $sizes[0] as $size ) {
				$labels[] = preg_replace( '/\s+/', '', $size ) . 'mm';
			}
			wp_set_object_terms( $id, array_unique( $labels ), 'pa_size', false );
		}
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
