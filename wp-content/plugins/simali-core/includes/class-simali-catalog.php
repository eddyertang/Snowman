<?php
/**
 * Category URLs, filters and redirects.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simali_Catalog {
	public static function register() {
		add_action( 'init', array( __CLASS__, 'rewrites' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirects' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filters' ) );
		add_filter( 'upload_mimes', array( __CLASS__, 'mimes' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'block_unpriced' ), 10, 2 );
	}

	public static function rewrites() {
		add_rewrite_rule( '^(limestone|porcelain|outdoor)/?$', 'index.php?product_cat=$matches[1]', 'top' );
		add_rewrite_rule( '^(limestone|porcelain|outdoor)/page/([0-9]+)/?$', 'index.php?product_cat=$matches[1]&paged=$matches[2]', 'top' );
	}

	public static function redirects() {
		$path = trailingslashit( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) );
		$map  = array(
			'/contact-us/'                              => '/contact/',
			'/delivery-information/'                    => '/delivery/',
			'/refund_returns/'                          => '/returns/',
			'/product-category/timeless-stone-collection/' => '/limestone/?pa_collection=timeless-stone-collection',
			'/product-category/limestone/'              => '/limestone/',
			'/product-category/porcelain/'              => '/porcelain/',
			'/product-category/stone-flooring-guides/'  => '/guides/',
		);
		if ( isset( $map[ $path ] ) ) {
			$target = $map[ $path ];
			$query  = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY );
			if ( '' !== $query ) {
				$target .= ( false === strpos( $target, '?' ) ? '?' : '&' ) . $query;
			}
			wp_safe_redirect( home_url( $target ), 301 );
			exit;
		}
	}

	/**
	 * Canonical URL for a product category: short slugs for the three
	 * main categories, the default term link otherwise.
	 */
	public static function category_url( $term = null ) {
		if ( ! $term || is_wp_error( $term ) ) {
			return home_url( '/shop/' );
		}
		if ( in_array( $term->slug, array( 'limestone', 'porcelain', 'outdoor' ), true ) ) {
			return home_url( '/' . $term->slug . '/' );
		}
		$link = get_term_link( $term );
		return is_wp_error( $link ) ? home_url( '/shop/' ) : $link;
	}

	public static function filters( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->is_post_type_archive( 'simali_case' ) ) {
			$meta = array();
			foreach ( array( 'room' => '_simali_room', 'material' => '_simali_material' ) as $param => $key ) {
				if ( ! empty( $_GET[ $param ] ) ) {
					$meta[] = array(
						'key'   => $key,
						'value' => sanitize_title( wp_unslash( $_GET[ $param ] ) ),
					);
				}
			}
			if ( $meta ) {
				$query->set( 'meta_query', $meta );
			}
			return;
		}
		if ( ! $query->is_post_type_archive( 'product' ) && ! $query->is_tax( 'product_cat' ) ) {
			return;
		}
		$tax_query = (array) $query->get( 'tax_query' );
		foreach ( array( 'pa_colour', 'pa_finish', 'pa_thickness', 'pa_size', 'pa_collection', 'pa_ufh', 'pa_location' ) as $tax ) {
			if ( empty( $_GET[ $tax ] ) ) {
				continue;
			}
			$tax_query[] = array(
				'taxonomy' => $tax,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $tax ] ) ),
			);
		}
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$query->set( 'tax_query', $tax_query );

		$band = isset( $_GET['price_band'] ) ? sanitize_text_field( wp_unslash( $_GET['price_band'] ) ) : '';
		if ( 'under-40' === $band ) {
			$query->set( 'meta_query', array( array( 'key' => '_price', 'value' => array( 0.01, 39.99 ), 'compare' => 'BETWEEN', 'type' => 'DECIMAL' ) ) );
		} elseif ( '40-60' === $band ) {
			$query->set( 'meta_query', array( array( 'key' => '_price', 'value' => array( 40, 60 ), 'compare' => 'BETWEEN', 'type' => 'DECIMAL' ) ) );
		} elseif ( '60-plus' === $band ) {
			$query->set( 'meta_query', array( array( 'key' => '_price', 'value' => 60, 'compare' => '>=', 'type' => 'DECIMAL' ) ) );
		} elseif ( 'on-request' === $band ) {
			$query->set( 'meta_query', array( array( 'key' => '_price', 'value' => 0.01, 'compare' => '<', 'type' => 'DECIMAL' ) ) );
		}

		$order = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
		if ( 'price' === $order || 'price-desc' === $order ) {
			$query->set( 'meta_key', '_price' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'price' === $order ? 'ASC' : 'DESC' );
		} elseif ( 'date' === $order ) {
			$query->set( 'orderby', 'date' );
		}
	}

	public static function block_unpriced( $valid, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && ( '' === $product->get_price() || (float) $product->get_price() <= 0 ) ) {
			wc_add_notice( 'Price on request: we\'ll call or email you back.', 'notice' );
			return false;
		}
		return $valid;
	}

	public static function mimes( $mimes ) {
		$mimes['heic'] = 'image/heic';
		$mimes['pdf']  = 'application/pdf';
		return $mimes;
	}

	public static function ensure_attribute( $label, $slug ) {
		if ( ! function_exists( 'wc_create_attribute' ) ) {
			return;
		}
		$taxonomy = 'pa_' . $slug;
		if ( ! taxonomy_exists( $taxonomy ) ) {
			wc_create_attribute(
				array(
					'name'         => $label,
					'slug'         => $slug,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				)
			);
			register_taxonomy(
				$taxonomy,
				array( 'product' ),
				array(
					'label'        => $label,
					'hierarchical' => false,
					'rewrite'      => false,
				)
			);
		}
	}
}
