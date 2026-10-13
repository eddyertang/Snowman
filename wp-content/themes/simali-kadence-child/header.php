<?php
/**
 * Site header.
 *
 * @package Simali
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
	<div class="utility">
		<div class="wrap">
			<a href="<?php echo esc_url( simali_phone_href() ); ?>">01747 852557</a>
			<a href="<?php echo esc_url( home_url( '/showroom/' ) ); ?>">Visit our Shaftesbury showroom</a>
			<span>Nationwide delivery · installation within 40 miles</span>
			<a class="utility-end" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
		</div>
	</div>
	<div class="bar wrap">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="logo-mark">Simali</span>
			<span class="logo-sub">Stone</span>
		</a>
		<nav class="primary-nav" aria-label="Primary">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => 'simali_nav_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<div class="tools">
			<button class="icon-btn" type="button" aria-expanded="false" aria-controls="site-search">
				<?php echo simali_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="visually-hidden">Search</span>
			</button>
			<a class="icon-btn" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">
				<?php echo simali_icon( 'sample' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="visually-hidden">Samples</span>
				<span class="count" id="sample-count" aria-live="polite"><?php echo esc_html( (string) simali_sample_count() ); ?></span>
			</a>
			<a class="icon-btn" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) ); ?>">
				<?php echo simali_icon( 'basket' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="visually-hidden">Basket</span>
				<span class="count" id="basket-count" aria-live="polite"><?php echo esc_html( (string) simali_basket_count() ); ?></span>
			</a>
			<a class="icon-btn tool-account" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) ); ?>">
				<?php echo simali_icon( 'account' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="visually-hidden">Account</span>
			</a>
			<button class="icon-btn menu-toggle" type="button" aria-expanded="false" aria-controls="site-drawer">
				<?php echo simali_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="visually-hidden">Menu</span>
			</button>
		</div>
	</div>
	<form id="site-search" class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" hidden>
		<label class="visually-hidden" for="simali-search">Search products</label>
		<input id="simali-search" type="search" name="s" placeholder="Search products">
		<input type="hidden" name="post_type" value="product">
		<button class="btn" type="submit">Search</button>
	</form>
</header>
<div id="site-drawer" class="drawer" hidden>
	<button class="icon-btn" type="button" data-close-drawer>
		<?php echo simali_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="visually-hidden">Close menu</span>
	</button>
	<nav class="drawer-nav" aria-label="Mobile">
		<?php foreach ( simali_primary_links() as $url => $label ) : ?>
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) ); ?>">Account</a>
	</nav>
	<div class="drawer-foot">
		<a class="btn" href="<?php echo esc_url( simali_phone_href() ); ?>">Call 01747 852557</a>
		<a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/samples/' ) ); ?>">Order samples</a>
		<a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/installation/#quote' ) ); ?>">Get an installation quote</a>
	</div>
</div>
<main id="main">
