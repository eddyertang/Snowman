<?php
/**
 * Sample basket stored in a 30-day cookie, separate from the WooCommerce cart.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simali_Samples {
	const COOKIE = 'simali_samples';

	public static function register() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route(
			'simali/v1',
			'/samples',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'index' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'add' ),
					'permission_callback' => array( __CLASS__, 'can_write' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'remove' ),
					'permission_callback' => array( __CLASS__, 'can_write' ),
				),
			)
		);
		register_rest_route(
			'simali/v1',
			'/postcode',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'postcode' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function can_write() {
		return (bool) wp_verify_nonce( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) ) : '', 'wp_rest' );
	}

	public static function items() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return array();
		}
		$data = json_decode( wp_unslash( $_COOKIE[ self::COOKIE ] ), true );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$clean = array();
		foreach ( $data as $id ) {
			$id = (int) $id;
			if ( $id > 0 ) {
				$clean[] = $id;
			}
		}
		return array_values( array_unique( $clean ) );
	}

	public static function save( array $ids ) {
		$value = wp_json_encode( array_values( $ids ) );
		setcookie( self::COOKIE, $value, time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), false );
		$_COOKIE[ self::COOKIE ] = $value;
	}

	public static function index() {
		return array(
			'ids'   => self::items(),
			'count' => count( self::items() ),
			'max'   => get_option( 'simali_sample_max' ),
		);
	}

	public static function add( WP_REST_Request $request ) {
		$id  = (int) $request->get_param( 'product_id' );
		$max = (int) get_option( 'simali_sample_max' );
		$ids = self::items();
		if ( ! $id || ! function_exists( 'wc_get_product' ) || ! wc_get_product( $id ) ) {
			return new WP_Error( 'simali_sample', 'Choose a product to sample.', array( 'status' => 400 ) );
		}
		if ( $max && ! in_array( $id, $ids, true ) && count( $ids ) >= $max ) {
			return new WP_Error( 'simali_sample_max', 'That is the maximum number of samples.', array( 'status' => 400 ) );
		}
		if ( ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
		self::save( $ids );
		$remaining = $max ? max( 0, $max - count( $ids ) ) : null;
		$message   = null === $remaining
			? 'Sample added.'
			: 'Sample added. You can add ' . $remaining . ' more.';
		return array(
			'ids'     => $ids,
			'count'   => count( $ids ),
			'message' => $message,
		);
	}

	public static function remove( WP_REST_Request $request ) {
		$id  = (int) $request->get_param( 'product_id' );
		$ids = array_values( array_filter( self::items(), function ( $item ) use ( $id ) {
			return (int) $item !== $id;
		} ) );
		self::save( $ids );
		return array(
			'ids'     => $ids,
			'count'   => count( $ids ),
			'message' => 'Sample removed.',
		);
	}

	public static function postcode( WP_REST_Request $request ) {
		return Simali_Postcode::check( (string) $request->get_param( 'postcode' ) );
	}
}

function simali_samples_count() {
	return count( Simali_Samples::items() );
}
