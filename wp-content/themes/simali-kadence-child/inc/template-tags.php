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

function simali_placeholder( $label, $ratio = '1' ) {
	$class = 'ratio-1';
	if ( '4x3' === $ratio ) {
		$class = 'ratio-4x3';
	} elseif ( '3x2' === $ratio ) {
		$class = 'ratio-3x2';
	}
	printf(
		'<div class="%1$s todo">%2$s</div>',
		esc_attr( $class ),
		esc_html( $label )
	);
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
		echo wp_kses_post( wpautop( $item['text'] ) );
		echo '<footer>' . esc_html( $item['name'] );
		if ( ! empty( $item['rating'] ) ) {
			echo ' <span aria-label="' . esc_attr( $item['rating'] . ' out of 5' ) . '">' . esc_html( str_repeat( '★', (int) $item['rating'] ) ) . '</span>';
		}
		echo '<br><span class="todo">TODO(client): source and date</span>';
		echo '</footer></blockquote>';
	}
	echo '</div>';
	echo '<p><a href="https://www.houzz.co.uk/hznb/projects/cathedral-limestone-floor-installation-dorset-pj-vj~7828329">You can also view more of our recent installations on our Houzz profile</a></p>';
}
