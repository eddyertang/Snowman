<?php
/**
 * Template helpers.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function simali_phone_href() {
	return 'tel:01747852557';
}

function simali_primary_links() {
	return array(
		home_url( '/limestone/' )  => 'Limestone',
		home_url( '/porcelain/' )  => 'Porcelain',
		home_url( '/outdoor/' )    => 'Outdoor & Patios',
		home_url( '/installation/' ) => 'Installation',
		home_url( '/our-work/' )   => 'Our Work',
		home_url( '/showroom/' )   => 'Showroom',
		home_url( '/about/' )      => 'About',
	);
}

function simali_nav_fallback() {
	echo '<ul class="menu">';
	foreach ( simali_primary_links() as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

function simali_icon( $name ) {
	$icons = array(
		'search'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="11" cy="11" r="6" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 16l5 5" stroke="currentColor" stroke-width="2"/></svg>',
		'basket'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 8V7a3 3 0 0 1 6 0v1" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
		'sample'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><rect x="4" y="4" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="13" y="4" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="4" y="13" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="13" y="13" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
		'account' => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="12" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M5 19c1.5-3 3.5-4 7-4s5.5 1 7 4" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
		'menu'    => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2"/></svg>',
		'close'   => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2"/></svg>',
	);
	return $icons[ $name ] ?? '';
}

function simali_sample_count() {
	if ( function_exists( 'simali_samples_count' ) ) {
		return simali_samples_count();
	}
	return 0;
}

function simali_basket_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}
	return 0;
}

function simali_ratio_class( $ratio ) {
	if ( '4x3' === $ratio ) {
		return 'ratio-4x3';
	}
	if ( '3x2' === $ratio ) {
		return 'ratio-3x2';
	}
	if ( '16x9' === $ratio ) {
		return 'ratio-16x9';
	}
	return 'ratio-1';
}

/**
 * Quiet stone-coloured block used where a photograph has not been supplied yet.
 */
function simali_placeholder( $label, $ratio = '1' ) {
	printf(
		'<div class="%1$s photo-pending" role="img" aria-label="%2$s"></div>',
		esc_attr( simali_ratio_class( $ratio ) ),
		esc_attr( $label )
	);
}

/**
 * Public URL for a photograph imported from the previous site, or '' when it is missing.
 */
function simali_image_url( $file ) {
	return class_exists( 'Simali_Media' ) ? Simali_Media::url( $file ) : '';
}

/**
 * Print an imported photograph, falling back to the quiet placeholder.
 *
 * @param string $file  File name inside uploads/simali.
 * @param string $alt   Alt text.
 * @param string $ratio 1, 4x3, 3x2 or 16x9.
 * @param array  $args  Extra attributes: class, loading, fetchpriority, sizes.
 */
function simali_image( $file, $alt, $ratio = '3x2', $args = array() ) {
	$url = simali_image_url( $file );
	if ( ! $url ) {
		simali_placeholder( $alt, $ratio );
		return;
	}
	list( $w, $h ) = Simali_Media::size( $file );
	$attrs = array(
		'class'    => trim( simali_ratio_class( $ratio ) . ' ' . ( $args['class'] ?? '' ) ),
		'src'      => $url,
		'alt'      => $alt,
		'loading'  => $args['loading'] ?? 'lazy',
		'decoding' => 'async',
	);
	if ( $w && $h ) {
		$attrs['width']  = $w;
		$attrs['height'] = $h;
	}
	if ( ! empty( $args['fetchpriority'] ) ) {
		$attrs['fetchpriority'] = $args['fetchpriority'];
	}
	$html = '<img';
	foreach ( $attrs as $key => $value ) {
		$html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
	}
	echo $html . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Home-page hero photograph: the option set in the dashboard, else the Ludlow cottage.
 */
function simali_hero_url() {
	$hero = get_option( 'simali_hero_image' );
	if ( $hero ) {
		return $hero;
	}
	return simali_image_url( 'ludlow-limestone-flooring-supplied-by-simali-stone-shaftesbury-1.jpg' );
}

function simali_google_reviews_url() {
	return 'https://www.google.com/search?q=simali+stone+ltd+shaftesbury+reviews';
}

function simali_houzz_url() {
	return 'https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329';
}

function simali_facebook_url() {
	return 'https://www.facebook.com/SimaliStoneLtd';
}

/**
 * Opening hours copy. The previous site never published hours, so until the client
 * confirms them we ask people to call ahead.
 */
function simali_hours_copy() {
	$hours = get_option( 'simali_opening_hours' );
	if ( $hours ) {
		return $hours;
	}
	return 'Showroom visits by appointment: call 01747 852557 before you set off and we will make sure someone is here.';
}

function simali_testimonials() {
	$posts = get_posts(
		array(
			'post_type'      => 'simali_testimonial',
			'posts_per_page' => 12,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	if ( ! $posts && function_exists( 'simali_verbatim_testimonials' ) ) {
		return simali_verbatim_testimonials();
	}
	$items = array();
	foreach ( $posts as $post ) {
		$items[] = array(
			'name'   => get_the_title( $post ),
			'text'   => $post->post_content,
			'rating' => (int) get_post_meta( $post->ID, '_simali_rating', true ),
			'source' => get_post_meta( $post->ID, '_simali_source', true ),
			'date'   => get_post_meta( $post->ID, '_simali_date', true ),
		);
	}
	return $items;
}

function simali_render_testimonials() {
	echo '<div class="quote-grid">';
	foreach ( simali_testimonials() as $item ) {
		echo '<blockquote class="quote-card">';
		if ( ! empty( $item['rating'] ) ) {
			echo '<p class="stars" aria-label="' . esc_attr( $item['rating'] . ' out of 5 stars' ) . '">' . esc_html( str_repeat( '★', (int) $item['rating'] ) ) . '</p>';
		}
		echo wp_kses_post( wpautop( $item['text'] ) );
		echo '<footer><strong>' . esc_html( $item['name'] ) . '</strong>';
		$meta = array_filter( array( $item['source'] ?? '', $item['date'] ?? '' ) );
		if ( $meta ) {
			echo '<span class="spec">' . esc_html( implode( ' · ', $meta ) ) . '</span>';
		} else {
			echo '<span class="spec">Simali Stone customer</span>';
		}
		echo '</footer></blockquote>';
	}
	echo '</div>';
	echo '<p class="review-links">Read more reviews on <a href="' . esc_url( simali_google_reviews_url() ) . '" rel="noopener">Google</a>, or browse recent installations on our <a href="' . esc_url( simali_houzz_url() ) . '" rel="noopener">Houzz profile</a>.</p>';
}
