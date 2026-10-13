<?php
/**
 * Server-side twin of assets/js/calculator-math.js.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'SIMALI_TESTING' ) ) {
	exit;
}

class Simali_Calculator {
	const DELIVERY_EX_VAT       = 70;
	const VAT_RATE              = 1.2;
	const DEFAULT_M2_PER_PALLET = 15;

	public static function quote( array $input ) {
		$wastage = self::finite( $input['wastage'] ?? 0.1, 'wastage' );
		if ( ! $wastage['ok'] ) {
			return $wastage;
		}
		if ( $wastage['value'] < 0 || $wastage['value'] > 0.5 ) {
			return self::fail( 'Choose a wastage of 5%, 10% or 15%.' );
		}

		$area = self::resolve_area( $input );
		if ( ! $area['ok'] ) {
			return $area;
		}
		if ( $area['area'] < 0 ) {
			return self::fail( 'Area cannot be negative.' );
		}

		$order = 0.0 === (float) $area['area'] ? 0.0 : self::ceil_to_tenth( $area['area'] * ( 1 + $wastage['value'] ) );
		$packs = null;
		$pack  = self::finite( $input['pack_m2'] ?? null, 'pack size' );
		if ( ! $pack['ok'] && empty( $pack['empty'] ) ) {
			return $pack;
		}
		if ( $pack['ok'] ) {
			if ( $pack['value'] <= 0 ) {
				return self::fail( 'Pack size must be greater than zero.' );
			}
			if ( $order > 0 ) {
				$packs = self::ceil_units( $order / $pack['value'] );
				$order = round( $packs * $pack['value'], 2 );
			} else {
				$packs = 0;
			}
		}

		$per = self::m2_per_pallet( $input );
		if ( ! $per['ok'] ) {
			return $per;
		}
		$pallets     = 0.0 === (float) $order ? 0 : self::ceil_units( $order / $per['value'] );
		$delivery_ex = $pallets * self::DELIVERY_EX_VAT;
		$delivery_in = round( $delivery_ex * self::VAT_RATE, 2 );

		$price = self::finite( $input['price_m2'] ?? null, 'price' );
		if ( ! $price['ok'] && empty( $price['empty'] ) ) {
			return $price;
		}
		$price_value = $price['ok'] ? $price['value'] : null;
		if ( null !== $price_value && $price_value < 0 ) {
			return self::fail( 'Price cannot be negative.' );
		}
		$total = ( null === $price_value || $price_value <= 0 ) ? null : round( $order * $price_value + $delivery_in, 2 );

		$result = array(
			'ok'               => true,
			'area'             => round( $area['area'], 2 ),
			'wastage'          => $wastage['value'],
			'order_m2'         => round( $order, 2 ),
			'packs'            => $packs,
			'm2_per_pallet'    => round( $per['value'], 2 ),
			'pallets'          => $pallets,
			'delivery_ex_vat'  => $delivery_ex,
			'delivery_inc_vat' => $delivery_in,
			'total_inc_vat'    => $total,
		);
		$result['summary'] = self::summary( $result );
		return $result;
	}

	private static function resolve_area( array $input ) {
		$rooms      = isset( $input['rooms'] ) && is_array( $input['rooms'] ) ? $input['rooms'] : array();
		$used_rooms = false;
		$area       = 0.0;
		foreach ( $rooms as $room ) {
			$room         = is_array( $room ) ? $room : array();
			$length_blank = self::blank( $room['length'] ?? null );
			$width_blank  = self::blank( $room['width'] ?? null );
			if ( $length_blank && $width_blank ) {
				continue;
			}
			$used_rooms = true;
			$length     = self::finite( $room['length'] ?? null, 'length' );
			$width      = self::finite( $room['width'] ?? null, 'width' );
			if ( ! $length['ok'] ) {
				return $length;
			}
			if ( ! $width['ok'] ) {
				return $width;
			}
			if ( $length['value'] < 0 || $width['value'] < 0 ) {
				return self::fail( 'Room measurements cannot be negative.' );
			}
			$area += $length['value'] * $width['value'];
		}
		if ( $used_rooms ) {
			return array(
				'ok'   => true,
				'area' => $area,
			);
		}
		if ( self::blank( $input['area'] ?? null ) ) {
			return array(
				'ok'   => true,
				'area' => 0.0,
			);
		}
		$direct = self::finite( $input['area'], 'area' );
		if ( ! $direct['ok'] ) {
			return $direct;
		}
		return array(
			'ok'   => true,
			'area' => $direct['value'],
		);
	}

	private static function m2_per_pallet( array $input ) {
		$weight = self::finite( $input['weight_per_m2'] ?? null, 'weight' );
		if ( ! $weight['ok'] && empty( $weight['empty'] ) ) {
			return $weight;
		}
		if ( $weight['ok'] ) {
			if ( $weight['value'] <= 0 ) {
				return self::fail( 'Weight per m² must be greater than zero.' );
			}
			return array(
				'ok'    => true,
				'value' => 1000 / $weight['value'],
			);
		}
		$meta = self::finite( $input['m2_per_pallet'] ?? null, 'm² per pallet' );
		if ( ! $meta['ok'] && empty( $meta['empty'] ) ) {
			return $meta;
		}
		if ( $meta['ok'] ) {
			if ( $meta['value'] <= 0 ) {
				return self::fail( 'm² per pallet must be greater than zero.' );
			}
			return array(
				'ok'    => true,
				'value' => $meta['value'],
			);
		}
		return array(
			'ok'    => true,
			'value' => self::DEFAULT_M2_PER_PALLET,
		);
	}

	public static function summary( array $result ) {
		if ( empty( $result['order_m2'] ) ) {
			return 'Enter an area to calculate the order.';
		}
		$percent = (int) round( $result['wastage'] * 100 );
		$pallets = (int) $result['pallets'];
		return sprintf(
			'Order %s m² (incl. %d%% wastage) · %d %s · Delivery £%s+VAT',
			number_format( (float) $result['order_m2'], 1, '.', '' ),
			$percent,
			$pallets,
			1 === $pallets ? 'pallet' : 'pallets',
			self::money( $result['delivery_ex_vat'] )
		);
	}

	public static function money( $amount ) {
		$amount = (float) $amount;
		if ( abs( $amount - round( $amount ) ) < 0.001 ) {
			return (string) (int) round( $amount );
		}
		return number_format( $amount, 2, '.', '' );
	}

	private static function ceil_to_tenth( $value ) {
		return ceil( $value * 10 - 1e-8 ) / 10;
	}

	private static function ceil_units( $value ) {
		return (int) ceil( $value - 1e-8 );
	}

	private static function blank( $value ) {
		return null === $value || '' === trim( (string) $value );
	}

	private static function finite( $value, $label ) {
		if ( self::blank( $value ) ) {
			return array(
				'ok'    => false,
				'empty' => true,
			);
		}
		if ( is_string( $value ) ) {
			$value = str_replace( ',', '.', $value );
		}
		if ( ! is_numeric( $value ) ) {
			return self::fail( 'Enter a number for ' . $label . '.' );
		}
		$number = (float) $value;
		if ( ! is_finite( $number ) ) {
			return self::fail( 'Enter a number for ' . $label . '.' );
		}
		return array(
			'ok'    => true,
			'value' => $number,
		);
	}

	private static function fail( $message ) {
		return array(
			'ok'    => false,
			'error' => $message,
		);
	}
}
