<?php
/**
 * Verbatim testimonials and shortcodes that print them.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function simali_verbatim_testimonials() {
	return array(
		array(
			'name'   => 'Sarah',
			'rating' => 5,
			'text'   => 'A wonderful job laying our downstairs flooring. Knowledgeable and meticulous in the finish, and incredibly considerate of working around our young family. We appreciated having the time to look through all the different stone options and not being pressured to come to a decision, when it took us an age! The final result is fantastic! Would thoroughly recommend.',
		),
		array(
			'name'   => 'Michelle',
			'rating' => 5,
			'text'   => 'Visited Simali a few months ago as was looking for some stone for our new kitchen and hallway floor I had seen something I liked online but we prefer to use a local business. Stu was very helpful and supplied a similar style colour to what we were looking for. Simali are very knowledgeable in their products and provided an excellent service from order to delivery.',
		),
		array(
			'name'   => 'Annette',
			'rating' => 5,
			'text'   => "We would definitely recommend Stu for stone flooring.\nHe was a great help in helping to choose the stone that was best for the room and giving advice as to the colour, size, pattern and grout to obtain what we had envisaged.\nThe finished job is lovely and just perfect for what we wanted.\nHe worked hard and finished the job within the time set. Really pleased with the whole experience.",
		),
	);
}

class Simali_Content {
	public static function register() {
		add_shortcode( 'simali_calculator', array( __CLASS__, 'calculator' ) );
		add_shortcode( 'simali_postcode', array( __CLASS__, 'postcode' ) );
		add_shortcode( 'simali_products', array( __CLASS__, 'products' ) );
		add_shortcode( 'simali_work', array( __CLASS__, 'work' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_head', array( __CLASS__, 'schema' ) );
	}

	public static function assets() {
		if ( function_exists( 'is_product' ) && is_product() ) {
			wp_enqueue_script( 'simali-calc-math', plugins_url( 'assets/js/calculator-math.js', SIMALI_CORE_FILE ), array(), '1.0.0', true );
			wp_enqueue_script( 'simali-calc', plugins_url( 'assets/js/calculator.js', SIMALI_CORE_FILE ), array( 'simali-calc-math' ), '1.0.0', true );
		}
		wp_enqueue_script( 'simali-samples', plugins_url( 'assets/js/samples.js', SIMALI_CORE_FILE ), array(), '1.0.0', true );
		wp_localize_script(
			'simali-samples',
			'simaliData',
			array(
				'rest'  => esc_url_raw( rest_url( 'simali/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'max'   => get_option( 'simali_sample_max' ),
			)
		);
	}

	public static function calculator() {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;
		$price   = ( $product && '' !== $product->get_price() && (float) $product->get_price() > 0 ) ? $product->get_price() : '';
		$pack    = $product ? get_post_meta( $product->get_id(), '_simali_pack_m2', true ) : '';
		$weight  = $product ? get_post_meta( $product->get_id(), '_simali_weight_per_m2', true ) : '';
		$pallet  = $product ? get_post_meta( $product->get_id(), '_simali_m2_per_pallet', true ) : '';
		ob_start();
		?>
		<form class="calc" id="simali-calc" data-price="<?php echo esc_attr( $price ); ?>" data-pack="<?php echo esc_attr( $pack ); ?>" data-weight="<?php echo esc_attr( $weight ); ?>" data-pallet="<?php echo esc_attr( $pallet ); ?>">
			<fieldset>
				<legend>Area</legend>
				<label class="field">Square metres
					<input type="text" inputmode="decimal" name="area" data-area>
				</label>
				<p>Or measure each room in metres.</p>
				<div data-rooms>
					<div class="room-row">
						<label>Length <input type="text" inputmode="decimal" data-length></label>
						<label>Width <input type="text" inputmode="decimal" data-width></label>
						<button class="btn btn-secondary" type="button" data-remove-room>Remove</button>
					</div>
				</div>
				<button class="btn btn-secondary" type="button" data-add-room>Add a room</button>
			</fieldset>
			<fieldset>
				<legend>Wastage</legend>
				<label><input type="radio" name="wastage" value="0.10" checked> 10% straight lay</label>
				<label><input type="radio" name="wastage" value="0.15"> 15% herringbone or random-length flagstones</label>
				<label><input type="radio" name="wastage" value="0.05"> 5% simple large format</label>
			</fieldset>
			<p class="result" id="calc-result" aria-live="polite">Enter an area to calculate the order.</p>
		</form>
		<?php
		return ob_get_clean();
	}

	public static function postcode() {
		$result = '';
		if ( isset( $_GET['postcode_check'] ) ) {
			$check  = Simali_Postcode::check( sanitize_text_field( wp_unslash( $_GET['postcode_check'] ) ) );
			$result = $check['message'] ?? $check['error'];
		}
		ob_start();
		?>
		<form method="get" id="postcode-form">
			<label class="field">Postcode
				<input name="postcode_check" autocomplete="postal-code" required value="<?php echo isset( $_GET['postcode_check'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['postcode_check'] ) ) ) : ''; ?>">
			</label>
			<button class="btn" type="submit">Check installation area</button>
		</form>
		<p id="postcode-result" aria-live="polite"><?php echo esc_html( $result ); ?></p>
		<?php
		return ob_get_clean();
	}

	public static function products( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 8 ), $atts );
		$limit = min( 8, max( 1, (int) $atts['limit'] ) );
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array( 'stone-flooring-guides' ),
						'operator' => 'NOT IN',
					),
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => array( 'featured' ),
					),
				),
			)
		);
		if ( ! $query->have_posts() ) {
			$query = new WP_Query(
				array(
					'post_type'      => 'product',
					'posts_per_page' => $limit,
					'post_status'    => 'publish',
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => array( 'stone-flooring-guides' ),
							'operator' => 'NOT IN',
						),
					),
				)
			);
		}
		ob_start();
		echo '<div class="product-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Grid of installation photographs. [simali_work limit="6" material="limestone" room="kitchen"]
	 */
	public static function work( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'    => 6,
				'material' => '',
				'room'     => '',
				'exclude'  => '',
			),
			$atts
		);
		$args = array(
			'post_type'      => 'simali_case',
			'posts_per_page' => max( 1, (int) $atts['limit'] ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'meta_query'     => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		);
		if ( '' !== $atts['exclude'] ) {
			$args['post__not_in'] = array_map( 'intval', explode( ',', $atts['exclude'] ) );
		}
		foreach ( array( 'material' => '_simali_material', 'room' => '_simali_room' ) as $att => $key ) {
			if ( '' !== $atts[ $att ] ) {
				$args['meta_query'][] = array( 'key' => $key, 'value' => sanitize_title( $atts[ $att ] ) );
			}
		}
		$query = new WP_Query( $args );
		if ( ! $query->have_posts() && $args['meta_query'] ) {
			unset( $args['meta_query'] );
			$query = new WP_Query( $args );
		}
		if ( ! $query->have_posts() ) {
			return '<p>Photographs of recent installations are on our <a href="https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329">Houzz profile</a>.</p>';
		}
		ob_start();
		echo '<div class="work-grid teaser">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$id   = get_the_ID();
			$alt  = get_post_meta( $id, '_simali_alt', true );
			$town = get_post_meta( $id, '_simali_town', true );
			echo '<a class="tile" href="' . esc_url( get_permalink() ) . '">';
			if ( has_post_thumbnail( $id ) ) {
				echo get_the_post_thumbnail( $id, 'medium_large', array( 'class' => 'ratio-3x2', 'alt' => $alt ? $alt : get_the_title(), 'loading' => 'lazy' ) );
			} else {
				$image = get_post_meta( $id, '_simali_image', true );
				if ( $image ) {
					echo '<img class="ratio-3x2" alt="' . esc_attr( $alt ) . '" src="' . esc_url( $image ) . '" width="768" height="512" loading="lazy">';
				} else {
					echo '<span class="ratio-3x2 photo-pending" aria-hidden="true"></span>';
				}
			}
			echo '<span class="card-body"><span class="tile-title">' . esc_html( get_the_title() ) . '</span>';
			if ( $town ) {
				echo '<span class="spec">' . esc_html( $town ) . '</span>';
			}
			echo '</span></a>';
		}
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}

	public static function schema() {
		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'HomeAndConstructionBusiness',
			'name'     => 'Simali Stone Ltd',
			'url'      => home_url( '/' ),
			'telephone'=> '+441747852557',
			'email'    => 'sales@simalistone.co.uk',
			'address'  => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '40d–40f Wincombe Business Park',
				'addressLocality' => 'Shaftesbury',
				'postalCode'      => 'SP7 9QJ',
				'addressCountry'  => 'GB',
			),
			'vatID'    => 'GB711839046',
			'areaServed' => array( 'Dorset', 'Wiltshire', 'Somerset' ),
			'sameAs'   => array(
				'https://www.facebook.com/SimaliStoneLtd',
				'https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329',
			),
		);
		$hero = Simali_Media::url( 'ludlow-limestone-flooring-supplied-by-simali-stone-shaftesbury-1.jpg' );
		if ( $hero ) {
			$data['image'] = $hero;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>';
		if ( function_exists( 'is_product' ) && is_product() ) {
			$product = wc_get_product( get_the_ID() );
			if ( $product ) {
				$schema = array(
					'@context' => 'https://schema.org',
					'@type'    => 'Product',
					'name'     => $product->get_name(),
					'url'      => $product->get_permalink(),
					'brand'    => array( '@type' => 'Brand', 'name' => 'Simali Stone' ),
				);
				$short = wp_strip_all_tags( $product->get_short_description() );
				if ( $short ) {
					$schema['description'] = $short;
				}
				if ( $product->get_image_id() ) {
					$schema['image'] = wp_get_attachment_image_url( $product->get_image_id(), 'large' );
				}
				if ( '' !== $product->get_price() && (float) $product->get_price() > 0 ) {
					$schema['offers'] = array(
						'@type'         => 'Offer',
						'priceCurrency' => 'GBP',
						'price'         => $product->get_price(),
						'availability'  => 'https://schema.org/InStock',
					);
				}
				echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
			}
		}
	}
}
