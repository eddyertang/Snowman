<?php
/**
 * Simali Kadence child theme.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIMALI_THEME_VERSION', '1.0.0' );

require_once get_stylesheet_directory() . '/inc/template-tags.php';
require_once get_stylesheet_directory() . '/inc/woocommerce.php';
require_once get_stylesheet_directory() . '/inc/pages.php';

add_action( 'after_setup_theme', 'simali_setup' );
function simali_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_false' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus(
		array(
			'primary' => 'Primary',
			'footer'  => 'Footer',
		)
	);
}

add_action( 'after_setup_theme', 'simali_disable_gallery_scripts', 100 );
function simali_disable_gallery_scripts() {
	remove_theme_support( 'wc-product-gallery-zoom' );
	remove_theme_support( 'wc-product-gallery-lightbox' );
	remove_theme_support( 'wc-product-gallery-slider' );
}

add_action( 'wp_enqueue_scripts', 'simali_assets', 20 );
function simali_assets() {
	wp_enqueue_style(
		'simali',
		get_stylesheet_directory_uri() . '/assets/css/main.css',
		array(),
		SIMALI_THEME_VERSION
	);
	wp_enqueue_script(
		'simali-nav',
		get_stylesheet_directory_uri() . '/assets/js/nav.js',
		array(),
		SIMALI_THEME_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
	wp_enqueue_script(
		'simali-sticky',
		get_stylesheet_directory_uri() . '/assets/js/sticky.js',
		array(),
		SIMALI_THEME_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
	wp_enqueue_script(
		'simali-filters',
		get_stylesheet_directory_uri() . '/assets/js/filters.js',
		array(),
		SIMALI_THEME_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
	wp_enqueue_script(
		'simali-gallery',
		get_stylesheet_directory_uri() . '/assets/js/gallery.js',
		array(),
		SIMALI_THEME_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	$handles = array(
		'kadence-fonts',
		'kadence-google-fonts',
		'kadence-global',
		'kadence-header',
		'kadence-content',
		'kadence-woocommerce',
		'kadence-footer',
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'elementor-frontend',
		'elementor-icons',
		'elementor-common',
	);
	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		wp_dequeue_script( $handle );
	}
	if ( ! ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) ) {
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_dequeue_style( 'woocommerce-inline' );
	}

	if ( ! simali_needs_jquery() ) {
		wp_dequeue_script( 'jquery' );
		wp_deregister_script( 'jquery' );
	}
	if ( ! is_cart() && ! is_checkout() ) {
		wp_dequeue_script( 'wc-cart-fragments' );
	}
}

add_action( 'wp_enqueue_scripts', 'simali_strip_front_assets', 100 );
function simali_strip_front_assets() {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	$keep = array( 'simali', 'simali-nav', 'simali-sticky', 'simali-filters', 'simali-gallery', 'simali-calc-math', 'simali-calc', 'simali-samples' );
	foreach ( array( 'scripts' => wp_scripts(), 'styles' => wp_styles() ) as $kind => $registry ) {
		if ( ! $registry ) {
			continue;
		}
		foreach ( (array) $registry->queue as $handle ) {
			if ( in_array( $handle, $keep, true ) ) {
				continue;
			}
			$src = isset( $registry->registered[ $handle ] ) ? (string) $registry->registered[ $handle ]->src : '';
			if ( ! simali_is_vendor_asset( $handle . ' ' . $src ) ) {
				continue;
			}
			if ( 'scripts' === $kind ) {
				wp_dequeue_script( $handle );
			} else {
				wp_dequeue_style( $handle );
			}
		}
	}
}

function simali_is_vendor_asset( $haystack ) {
	foreach ( array( 'jquery', 'woocommerce', 'kadence', 'photoswipe', 'flexslider', 'comment-reply', 'sourcebuster', 'blockui', 'splide' ) as $needle ) {
		if ( false !== stripos( $haystack, $needle ) ) {
			return true;
		}
	}
	return false;
}

function simali_needs_jquery() {
	return function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
}

add_action( 'wp_head', 'simali_preload_fonts', 1 );
function simali_preload_fonts() {
	$fonts = array(
		'inter-5.2.8-latin-400-normal.woff2',
		'cormorant-garamond-5.2.8-latin-600-normal.woff2',
	);
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_stylesheet_directory_uri() . '/assets/fonts/' . $font )
		);
	}
	$hero = get_option( 'simali_hero_image' );
	if ( $hero ) {
		printf( '<link rel="preload" as="image" href="%s">' . "\n", esc_url( $hero ) );
	}
}

add_filter( 'body_class', 'simali_body_class' );
function simali_body_class( $classes ) {
	$classes[] = 'has-sticky';
	return $classes;
}

add_action( 'wp_enqueue_scripts', 'simali_emoji_off', 1 );
function simali_emoji_off() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}

add_filter( 'nav_menu_link_attributes', 'simali_menu_attrs', 10, 2 );
function simali_menu_attrs( $atts, $item ) {
	$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' nav-link' );
	return $atts;
}
