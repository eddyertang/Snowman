<?php
/**
 * WooCommerce presentation.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_get_rating_html', 'simali_hide_empty_rating', 10, 2 );
function simali_hide_empty_rating( $html, $rating ) {
	if ( (float) $rating <= 0 ) {
		return '';
	}
	return $html;
}

add_filter( 'woocommerce_product_tabs', 'simali_product_tabs' );
function simali_product_tabs( $tabs ) {
	unset( $tabs['reviews'], $tabs['additional_information'], $tabs['description'] );
	return $tabs;
}

add_action( 'init', 'simali_unhook_product_summary' );
function simali_unhook_product_summary() {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
}

add_filter( 'woocommerce_is_purchasable', 'simali_priced_only', 10, 2 );
function simali_priced_only( $purchasable, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $purchasable;
	}
	$price = $product->get_price();
	if ( '' === $price || (float) $price <= 0 ) {
		return false;
	}
	return $purchasable;
}

add_filter( 'woocommerce_quantity_input_args', 'simali_quantity_step', 10, 2 );
function simali_quantity_step( $args, $product ) {
	if ( $product instanceof WC_Product && $product->is_virtual() ) {
		return $args;
	}
	$args['min_value'] = 0.1;
	$args['step']      = 0.1;
	$args['inputmode'] = 'decimal';
	return $args;
}

add_filter( 'woocommerce_stock_amount', 'simali_decimal_qty' );
function simali_decimal_qty( $amount ) {
	return (float) $amount;
}

function simali_is_priced( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	$price = $product->get_price();
	return '' !== $price && (float) $price > 0;
}

function simali_price_label( $product ) {
	if ( ! simali_is_priced( $product ) ) {
		return 'Price on request';
	}
	$max = get_post_meta( $product->get_id(), '_simali_price_max', true );
	$prefix = ( $product->is_type( 'variable' ) || ( $max && (float) $max > (float) $product->get_price() ) ) ? 'from ' : '';
	$unit   = $product->is_virtual() ? ' inc VAT' : '/m² inc VAT';
	return $prefix . wp_strip_all_tags( wc_price( $product->get_price() ) ) . $unit;
}

function simali_spec_line( $product ) {
	$bits = array();
	foreach ( array( 'pa_thickness' => '', 'pa_finish' => '' ) as $tax => $unused ) {
		$names = wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) );
		if ( $names && ! is_wp_error( $names ) ) {
			$bits[] = implode( ', ', $names );
		}
	}
	return $bits ? implode( ' · ', $bits ) : '';
}

function simali_product_loop() {
	if ( woocommerce_product_loop() ) {
		echo '<div class="product-grid">';
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'product' );
		}
		echo '</div>';
		woocommerce_pagination();
	} else {
		echo '<p>No floors match these filters.</p>';
	}
}

function simali_attr_list( $product, $taxonomy ) {
	$names = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'names' ) );
	if ( ! $names || is_wp_error( $names ) ) {
		return '';
	}
	return implode( ', ', $names );
}
