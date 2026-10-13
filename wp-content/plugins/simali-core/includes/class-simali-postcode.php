<?php
/**
 * Outward-code distance from the Shaftesbury showroom.
 *
 * Centroids: FreeMapTools outcode list, September 2026 refresh.
 * Contains Royal Mail data © Royal Mail copyright and database right 2021.
 * Source: Office for National Statistics licensed under the Open Government Licence v.3.0.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'SIMALI_TESTING' ) ) {
	exit;
}

class Simali_Postcode {
	const ORIGIN_OUTWARD = 'SP7';
	const RADIUS_MILES   = 40;
	const EARTH_MILES    = 3958.7613;

	private static $centroids = null;

	public static function check( $postcode ) {
		$outward = self::outward( $postcode );
		if ( null === $outward ) {
			return array(
				'ok'      => false,
				'error'   => 'Enter a UK postcode, for example SP7 9QJ.',
				'in_area' => null,
			);
		}
		$centroids = self::centroids();
		if ( ! isset( $centroids[ $outward ] ) || ! isset( $centroids[ self::ORIGIN_OUTWARD ] ) ) {
			return array(
				'ok'       => false,
				'error'    => 'We could not match that postcode. Call 01747 852557 and we will check.',
				'outward'  => $outward,
				'in_area'  => null,
			);
		}
		$miles = self::haversine( $centroids[ self::ORIGIN_OUTWARD ], $centroids[ $outward ] );
		$in    = $miles <= self::RADIUS_MILES;
		return array(
			'ok'      => true,
			'outward' => $outward,
			'miles'   => round( $miles, 2 ),
			'in_area' => $in,
			'message' => $in
				? 'In our installation area'
				: 'Outside our 40-mile installation area. We can still deliver nationwide.',
		);
	}

	public static function outward( $postcode ) {
		$compact = strtoupper( preg_replace( '/\s+/', '', (string) $postcode ) );
		if ( '' === $compact ) {
			return null;
		}
		if ( preg_match( '/^(GIR)0AA$/', $compact, $gir ) ) {
			return $gir[1];
		}
		if ( preg_match( '/^([A-Z]{1,2}\d[A-Z\d]?)(\d[A-Z]{2})$/', $compact, $full ) ) {
			return $full[1];
		}
		if ( preg_match( '/^[A-Z]{1,2}\d[A-Z\d]?$/', $compact ) ) {
			return $compact;
		}
		return null;
	}

	public static function haversine( array $from, array $to ) {
		$lat1 = deg2rad( $from[0] );
		$lon1 = deg2rad( $from[1] );
		$lat2 = deg2rad( $to[0] );
		$lon2 = deg2rad( $to[1] );
		$dlat = $lat2 - $lat1;
		$dlon = $lon2 - $lon1;
		$h    = sin( $dlat / 2 ) ** 2 + cos( $lat1 ) * cos( $lat2 ) * sin( $dlon / 2 ) ** 2;
		return 2 * self::EARTH_MILES * asin( min( 1, sqrt( $h ) ) );
	}

	public static function centroids() {
		if ( null !== self::$centroids ) {
			return self::$centroids;
		}
		$path = dirname( __DIR__ ) . '/data/outward-centroids.json';
		$raw  = file_get_contents( $path );
		$data = json_decode( $raw, true );
		self::$centroids = is_array( $data ) ? $data : array();
		return self::$centroids;
	}
}
