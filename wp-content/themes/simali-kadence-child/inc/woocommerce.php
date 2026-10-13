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

/**
 * Long description with the short description removed when the seed prepended it,
 * so the same paragraph is not shown twice on the page.
 */
function simali_product_about( $product ) {
	$long  = trim( (string) $product->get_description() );
	$short = trim( wp_strip_all_tags( (string) $product->get_short_description() ) );
	if ( '' === $long ) {
		return '';
	}
	if ( $short && preg_match( '/^\s*<p>(.*?)<\/p>/su', $long, $m ) ) {
		$first = trim( wp_strip_all_tags( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) ) );
		if ( $first === $short ) {
			$long = trim( substr( $long, strlen( $m[0] ) ) );
		}
	}
	if ( '' === trim( wp_strip_all_tags( $long ) ) ) {
		return '';
	}
	return apply_filters( 'the_content', $long );
}

/**
 * Care and sealing guidance, by material.
 */
function simali_care_copy( $product ) {
	$material = strtolower( simali_attr_list( $product, 'pa_material' ) );
	$outdoor  = false !== stripos( simali_attr_list( $product, 'pa_location' ), 'outdoor' );
	if ( false !== strpos( $material, 'porcelain' ) ) {
		$html = '<p>Porcelain is fired dense and does not need sealing. Sweep or vacuum grit, then mop with warm water and a pH-neutral tile cleaner. Avoid soap-based products, which leave a film that dulls the surface.</p>'
			. '<p>Grout joints benefit from a grout protector once the floor has cured. Matt and textured finishes hide marks best; polished porcelain shows footprints more and is slippery when wet, so keep it for walls and dry rooms.</p>';
		if ( $outdoor ) {
			$html .= '<p>Outdoors, 20 mm porcelain can be laid on pedestals, on a full mortar bed or on a prepared base. Brush off leaves and algae in autumn; a stiff brush and warm water is usually all it takes.</p>';
		}
		return $html;
	}
	if ( false !== strpos( $material, 'sandstone' ) ) {
		return '<p>Sandstone is porous and must be sealed. We seal before grouting and again once the floor is finished, then every two to three years depending on wear.</p>'
			. '<p>Clean with warm water and a pH-neutral stone cleaner. Never use acidic, bleach-based or “limescale” cleaners. Wipe spills promptly, especially wine, oil and anything acidic, and use felt pads under furniture.</p>'
			. ( $outdoor ? '<p>Outdoors, a breathable sealer protects against staining while letting the stone dry out. Brush off leaves in autumn and avoid pressure-washing at close range, which opens the surface.</p>' : '' );
	}
	$html = '<p>Limestone is a natural, slightly porous stone and must be sealed. We seal before grouting, so grout cannot stain the face of the stone, and again once the floor is finished. Depending on wear, plan to re-seal every few years; we will tell you which product to use.</p>'
		. '<p>Day to day, sweep or vacuum grit and mop with warm water and a pH-neutral stone cleaner. Never use vinegar, bleach, acidic bathroom cleaners or “limescale” products: they etch limestone. Wipe up wine, fruit juice and oil promptly, and use felt pads under chair legs.</p>'
		. '<p>Tumbled and brushed flagstones hide everyday marks well. Honed finishes show more, and reward a quick wipe. Over the years the stone mellows and develops a patina; most people see that as the point of a natural floor.</p>';
	if ( $outdoor ) {
		$html .= '<p>Outdoors, use a breathable impregnating sealer so the stone can dry out, brush off leaves and algae before winter, and avoid de-icing salt on the surface.</p>';
	}
	return $html;
}

add_action( 'wp_head', 'simali_meta_description', 2 );
function simali_meta_description() {
	$description = '';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$description = get_post_meta( get_queried_object_id(), '_simali_meta_description', true );
		if ( ! $description ) {
			$product     = wc_get_product( get_queried_object_id() );
			$description = $product ? wp_strip_all_tags( $product->get_short_description() ) : '';
		}
	} elseif ( is_front_page() ) {
		$description = 'Natural stone and porcelain floors supplied nationwide and installed within 40 miles of Shaftesbury. Limestone flagstones, porcelain and patios from the Simali Stone showroom in Dorset.';
	} elseif ( is_page() && has_excerpt() ) {
		$description = get_the_excerpt();
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$description = term_description();
	}
	$description = trim( wp_strip_all_tags( (string) $description ) );
	if ( $description ) {
		if ( mb_strlen( $description ) > 160 ) {
			$description = mb_substr( $description, 0, 157 ) . '…';
		}
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
}
