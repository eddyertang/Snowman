<?php
/**
 * Fallback index.
 *
 * @package Simali
 */

get_header();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		echo '<article class="section"><div class="wrap"><h1>' . esc_html( get_the_title() ) . '</h1>';
		the_content();
		echo '</div></article>';
	}
}
get_footer();
