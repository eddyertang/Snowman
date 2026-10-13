<?php
/**
 * PHPUnit coverage for the same vectors as tests/calculator.test.mjs.
 *
 * @package Simali
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-simali-calculator.php';
require_once dirname( __DIR__ ) . '/includes/class-simali-postcode.php';

final class CalculatorTest extends TestCase {
	public function test_thirty_at_ten_percent(): void {
		$result = Simali_Calculator::quote(
			array(
				'area'     => 30,
				'wastage'  => 0.1,
				'price_m2' => 50,
			)
		);
		$this->assertTrue( $result['ok'] );
		$this->assertSame( 33.0, $result['order_m2'] );
		$this->assertSame( 3, $result['pallets'] );
		$this->assertSame( 210, $result['delivery_ex_vat'] );
		$this->assertSame( 252.0, $result['delivery_inc_vat'] );
		$this->assertSame( 1902.0, $result['total_inc_vat'] );
		$this->assertSame( 'Order 33.0 m² (incl. 10% wastage) · 3 pallets · Delivery £210+VAT', $result['summary'] );
	}

	public function test_zero_area(): void {
		$result = Simali_Calculator::quote(
			array(
				'area'    => 0,
				'wastage' => 0.1,
			)
		);
		$this->assertSame( 0.0, $result['order_m2'] );
		$this->assertSame( 0, $result['pallets'] );
		$this->assertSame( 0, $result['delivery_ex_vat'] );
		$this->assertNull( $result['total_inc_vat'] );
	}

	public function test_non_numeric(): void {
		$result = Simali_Calculator::quote(
			array(
				'area'    => 'twelve',
				'wastage' => 0.1,
			)
		);
		$this->assertFalse( $result['ok'] );
		$this->assertStringContainsString( 'number', strtolower( $result['error'] ) );
	}

	public function test_exact_pallet(): void {
		$result = Simali_Calculator::quote(
			array(
				'area'    => 15,
				'wastage' => 0,
			)
		);
		$this->assertSame( 15.0, $result['order_m2'] );
		$this->assertSame( 1, $result['pallets'] );
		$this->assertSame( 70, $result['delivery_ex_vat'] );
	}

	public function test_published_two_pallet_example(): void {
		$result = Simali_Calculator::quote(
			array(
				'area'    => 30,
				'wastage' => 0,
			)
		);
		$this->assertSame( 30.0, $result['order_m2'] );
		$this->assertSame( 2, $result['pallets'] );
		$this->assertSame( 140, $result['delivery_ex_vat'] );
	}

	public function test_rooms_and_packs_and_weight(): void {
		$rooms = Simali_Calculator::quote(
			array(
				'rooms'   => array(
					array(
						'length' => 5,
						'width'  => 4,
					),
					array(
						'length' => 2,
						'width'  => 2.5,
					),
				),
				'wastage' => 0.1,
			)
		);
		$this->assertSame( 25.0, $rooms['area'] );
		$this->assertSame( 27.5, $rooms['order_m2'] );

		$packs = Simali_Calculator::quote(
			array(
				'area'    => 10.1,
				'wastage' => 0,
				'pack_m2' => 2.5,
			)
		);
		$this->assertSame( 5, $packs['packs'] );
		$this->assertSame( 12.5, $packs['order_m2'] );

		$weight = Simali_Calculator::quote(
			array(
				'area'          => 30,
				'wastage'       => 0,
				'weight_per_m2' => 50,
			)
		);
		$this->assertSame( 20.0, $weight['m2_per_pallet'] );
		$this->assertSame( 2, $weight['pallets'] );
	}

	public function test_showroom_postcode_is_in_area_and_manchester_is_not(): void {
		$home = Simali_Postcode::check( 'SP7 9QJ' );
		$this->assertTrue( $home['in_area'] );
		$this->assertSame( 'In our installation area', $home['message'] );
		$this->assertLessThan( 1, $home['miles'] );

		$manchester = Simali_Postcode::check( 'M1 1AE' );
		$this->assertFalse( $manchester['in_area'] );
		$this->assertSame( 'Outside our 40-mile installation area. We can still deliver nationwide.', $manchester['message'] );
		$this->assertGreaterThan( 40, $manchester['miles'] );

		$this->assertNull( Simali_Postcode::outward( 'not a postcode' ) );
	}
}
