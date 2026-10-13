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

$term  = is_product_category() ? get_queried_object() : null;
$title = $term ? $term->name : 'All floors';
$intros = array(
	'limestone' => 'Limestone floors for kitchens, halls and living rooms, supplied from Shaftesbury. Cathedral, Heritage and the Timeless Stone Collection flagstones, with nationwide delivery and installation within 40 miles.',
	'porcelain' => 'Porcelain tiles for floors and walls, including large formats and stone, marble and wood effects. Supplied nationwide and installed within 40 miles of Shaftesbury. Matching indoor and outdoor tiles are available on selected ranges.',
	'outdoor'   => 'Porcelain and natural stone patios, steps and terraces, supplied and installed within 40 miles of Shaftesbury. Matching indoor and outdoor tiles are available where the range allows.',
	'stone-flooring-guides' => 'Straight-talking advice from the showroom, written down.',
);
$heroes = array(
	'limestone' => array( 'cathedral-limestone-cottage-living-room-floor-teffont.jpg', 'Cathedral limestone floor in a cottage living room in Teffont' ),
	'porcelain' => array( 'large-format-full-room-installation-dorset.jpg', 'Large-format grey porcelain across an open-plan room in Dorset' ),
	'outdoor'   => array( 'porcelain-outdoor-patio-installed-gillingham-dorset.jpg', 'Outdoor porcelain patio installed in Gillingham, Dorset' ),
);
$intro = $term && isset( $intros[ $term->slug ] ) ? $intros[ $term->slug ] : 'Natural stone and porcelain, sold by the square metre. Every stone can be sampled first. Installation is within 40 miles of Shaftesbury; supply is nationwide.';
$hero  = $term && isset( $heroes[ $term->slug ] ) ? $heroes[ $term->slug ] : null;
$is_filtered = false;
$base  = class_exists( 'Simali_Catalog' ) ? Simali_Catalog::category_url( $term ) : ( $term ? get_term_link( $term ) : home_url( '/shop/' ) );
?>
<section class="section archive">
	<div class="wrap">
		<header class="page-head <?php echo $hero ? 'with-media' : ''; ?>">
			<div>
				<h1><?php echo esc_html( $title ); ?></h1>
				<p class="lede"><?php echo esc_html( $intro ); ?></p>
			</div>
			<?php
			if ( $hero ) {
				simali_image( $hero[0], $hero[1], '3x2', array( 'class' => 'rounded', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
			}
			?>
		</header>
		<div class="layout">
			<form class="filters" method="get" action="<?php echo esc_url( $base ); ?>" id="filters">
				<details class="filters-shell" open>
					<summary><h2>Filter floors</h2></summary>
					<div class="filters-body">
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
						$terms = taxonomy_exists( $tax ) ? get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) ) : array();
						if ( ! $terms || is_wp_error( $terms ) ) {
							continue;
						}
						$selected = isset( $_GET[ $tax ] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $tax ] ) ) : array();
						if ( $selected ) {
							$is_filtered = true;
						}
						$many = count( $terms ) > 7;
						echo '<fieldset class="filter-group' . ( $many ? ' is-long' : '' ) . '"><legend>' . esc_html( $label ) . '</legend><div class="filter-options">';
						foreach ( $terms as $filter_term ) {
							$id = $tax . '-' . $filter_term->slug;
							echo '<label for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $tax ) . '[]" value="' . esc_attr( $filter_term->slug ) . '" ' . checked( in_array( $filter_term->slug, $selected, true ), true, false ) . '><span>' . esc_html( $filter_term->name ) . '</span></label>';
						}
						echo '</div></fieldset>';
					}
					?>
					<label class="field" for="price-band">Price band
						<select id="price-band" name="price_band">
							<?php
							$band  = isset( $_GET['price_band'] ) ? sanitize_text_field( wp_unslash( $_GET['price_band'] ) ) : '';
							$bands = array(
								''           => 'Any price',
								'under-40'   => 'Under £40/m²',
								'40-60'      => '£40 to £60/m²',
								'60-plus'    => 'Over £60/m²',
								'on-request' => 'Price on request',
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
							foreach ( array( 'menu_order' => 'Featured', 'price' => 'Price, low to high', 'price-desc' => 'Price, high to low', 'date' => 'Newest' ) as $value => $order_label ) {
								printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $order, $value, false ), esc_html( $order_label ) );
							}
							?>
						</select>
					</label>
					<div class="filter-actions">
						<button class="btn" type="submit">Apply filters</button>
						<a class="text-link" href="<?php echo esc_url( $base ); ?>">Clear</a>
					</div>
					</div>
				</details>
			</form>
			<div class="results">
				<p id="filter-status" class="fine" aria-live="polite"></p>
				<div id="product-grid">
					<?php simali_product_loop(); ?>
				</div>
				<?php if ( $term && 'limestone' === $term->slug ) : ?>
					<aside class="note">
						<h2>Not sure where to start?</h2>
						<p>Cathedral and Heritage are the two limestones we lay most often. The <a href="<?php echo esc_url( home_url( '/timeless-stone-collection-simali-stone/' ) ); ?>">Timeless Stone Collection</a> adds thicker, random-length flagstones such as Ludlow, Southwell and York. Order a few samples and live with them for a week.</p>
					</aside>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
