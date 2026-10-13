<?php
/**
 * Category and shop archives.
 *
 * @package Simali
 */

defined( 'ABSPATH' ) || exit;

if ( isset( $_GET['simali_partial'] ) ) {
	simali_product_loop();
	exit;
}
get_header();

$term = is_product_category() ? get_queried_object() : null;
$title = $term ? $term->name : 'Shop';
$intros = array(
	'limestone' => 'Limestone floors for kitchens, halls and living rooms, supplied from Shaftesbury. Nationwide delivery, with installation within 40 miles. Filter the Timeless Stone Collection, finish and thickness.',
	'porcelain' => 'Porcelain tiles for floors and walls, including large formats. Supplied nationwide and installed within 40 miles of Shaftesbury. Matching indoor and outdoor tiles are available on selected ranges.',
	'outdoor'   => 'Porcelain and natural stone patios, supplied and installed within 40 miles of Shaftesbury. Matching indoor and outdoor tiles are available where the range allows.',
);
$intro = $term && isset( $intros[ $term->slug ] ) ? $intros[ $term->slug ] : 'Natural stone and porcelain, sold by the square metre. Installation is within 40 miles of Shaftesbury. Supply is nationwide.';
?>
<section class="section">
	<div class="wrap">
		<h1><?php echo esc_html( $title ); ?></h1>
		<p><?php echo esc_html( $intro ); ?></p>
		<div class="layout">
			<form class="filters" method="get" action="<?php echo esc_url( $term ? get_term_link( $term ) : home_url( '/shop/' ) ); ?>" id="filters">
				<h2 class="visually-hidden">Filters</h2>
				<?php
				$groups = array(
					'pa_colour'     => 'Colour',
					'pa_finish'     => 'Finish',
					'pa_thickness'  => 'Thickness',
					'pa_size'       => 'Size',
					'pa_collection' => 'Collection',
					'pa_ufh'        => 'Underfloor heating',
					'pa_location'   => 'Indoor / outdoor',
				);
				foreach ( $groups as $tax => $label ) {
					$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
					echo '<fieldset><legend>' . esc_html( $label ) . '</legend>';
					if ( ! $terms || is_wp_error( $terms ) ) {
						echo '<p class="todo">TODO(client): add ' . esc_html( strtolower( $label ) ) . ' options</p>';
					} else {
						$selected = isset( $_GET[ $tax ] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $tax ] ) ) : array();
						foreach ( $terms as $filter_term ) {
							$id = $tax . '-' . $filter_term->slug;
							echo '<label for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $tax ) . '[]" value="' . esc_attr( $filter_term->slug ) . '" ' . checked( in_array( $filter_term->slug, $selected, true ), true, false ) . '> ' . esc_html( $filter_term->name ) . '</label>';
						}
					}
					echo '</fieldset>';
				}
				?>
				<label class="field" for="price-band">Price band
					<select id="price-band" name="price_band">
						<?php
						$band = isset( $_GET['price_band'] ) ? sanitize_text_field( wp_unslash( $_GET['price_band'] ) ) : '';
						$bands = array(
							''          => 'Any price',
							'under-40'  => 'Under £40/m²',
							'40-60'     => '£40 to £60/m²',
							'60-plus'   => 'Over £60/m²',
							'on-request'=> 'Price on request',
						);
						foreach ( $bands as $value => $band_label ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $band, $value, false ), esc_html( $band_label ) );
						}
						?>
					</select>
				</label>
				<label class="field" for="orderby">Sort by
					<select id="orderby" name="orderby">
						<?php
						$order = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'menu_order';
						foreach ( array( 'menu_order' => 'Featured', 'price' => 'Price', 'date' => 'Newest' ) as $value => $order_label ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $order, $value, false ), esc_html( $order_label ) );
						}
						?>
					</select>
				</label>
				<button class="btn" type="submit">Apply filters</button>
			</form>
			<div>
				<p id="filter-status" aria-live="polite"></p>
				<div id="product-grid">
					<?php simali_product_loop(); ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
