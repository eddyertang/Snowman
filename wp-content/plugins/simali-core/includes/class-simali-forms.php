<?php
/**
 * Quote and contact forms stored as enquiries.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simali_Forms {
	public static function register() {
		add_action( 'init', array( __CLASS__, 'cpt' ) );
		add_shortcode( 'simali_quote_form', array( __CLASS__, 'quote_form' ) );
		add_shortcode( 'simali_contact_form', array( __CLASS__, 'contact_form' ) );
		add_shortcode( 'simali_sample_form', array( __CLASS__, 'sample_form' ) );
		add_action( 'admin_post_nopriv_simali_enquiry', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_simali_enquiry', array( __CLASS__, 'handle' ) );
		add_action( 'admin_menu', array( __CLASS__, 'badges_page' ) );
	}

	public static function cpt() {
		register_post_type(
			'simali_enquiry',
			array(
				'label'        => 'Enquiries',
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'supports'     => array( 'title' ),
			)
		);
		register_post_type(
			'simali_testimonial',
			array(
				'label'        => 'Testimonials',
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);
		register_post_type(
			'simali_case',
			array(
				'label'       => 'Our work',
				'public'      => true,
				'has_archive' => 'our-work',
				'rewrite'     => array( 'slug' => 'our-work' ),
				'supports'    => array( 'title', 'editor', 'thumbnail' ),
				'show_in_rest'=> true,
			)
		);
	}

	public static function badges_page() {
		add_options_page( 'Trust badges', 'Trust badges', 'manage_options', 'simali-badges', array( __CLASS__, 'render_badges' ) );
	}

	public static function render_badges() {
		if ( isset( $_POST['simali_badges_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simali_badges_nonce'] ) ), 'simali_badges' ) ) {
			$badges = array();
			$names  = isset( $_POST['badge_name'] ) ? (array) wp_unslash( $_POST['badge_name'] ) : array();
			$flags  = isset( $_POST['badge_confirmed'] ) ? (array) wp_unslash( $_POST['badge_confirmed'] ) : array();
			foreach ( $names as $index => $name ) {
				$name = sanitize_text_field( $name );
				if ( '' === $name ) {
					continue;
				}
				$badges[] = array(
					'name'      => $name,
					'confirmed' => ! empty( $flags[ $index ] ),
				);
			}
			update_option( 'simali_badges', $badges );
			echo '<div class="updated"><p>Badges saved. Only confirmed badges are shown.</p></div>';
		}
		$badges = get_option( 'simali_badges', array() );
		echo '<div class="wrap"><h1>Trust badges</h1><form method="post">';
		wp_nonce_field( 'simali_badges', 'simali_badges_nonce' );
		foreach ( $badges as $index => $badge ) {
			echo '<p><input name="badge_name[' . esc_attr( (string) $index ) . ']" value="' . esc_attr( $badge['name'] ) . '"> ';
			echo '<label><input type="checkbox" name="badge_confirmed[' . esc_attr( (string) $index ) . ']" ' . checked( ! empty( $badge['confirmed'] ), true, false ) . '> confirmed</label></p>';
		}
		echo '<p><input name="badge_name[new]" placeholder="New badge"></p>';
		echo '<button class="button button-primary" type="submit">Save</button></form>';
		echo '<h2>Shown on the site</h2>';
		echo self::badges_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	public static function badges_html() {
		$html = '';
		foreach ( (array) get_option( 'simali_badges', array() ) as $badge ) {
			if ( empty( $badge['confirmed'] ) || empty( $badge['name'] ) ) {
				continue;
			}
			$html .= '<li>' . esc_html( $badge['name'] ) . '</li>';
		}
		return $html ? '<ul class="badges">' . $html . '</ul>' : '';
	}

	private static function errors() {
		$key = isset( $_COOKIE['simali_form'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['simali_form'] ) ) : '';
		if ( ! $key ) {
			return array( 'messages' => array(), 'old' => array() );
		}
		$data = get_transient( 'simali_form_' . $key );
		return is_array( $data ) ? $data : array( 'messages' => array(), 'old' => array() );
	}

	private static function field( $name, $label, $type, $required, $old, $extra = '' ) {
		$value = isset( $old[ $name ] ) ? $old[ $name ] : '';
		$id    = 'simali-' . $name;
		echo '<label class="field" for="' . esc_attr( $id ) . '">' . esc_html( $label );
		if ( $required ) {
			echo ' <span aria-hidden="true">*</span>';
		}
		if ( 'textarea' === $type ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" ' . ( $required ? 'required' : '' ) . '>' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input id="' . esc_attr( $id ) . '" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" ' . ( $required ? 'required' : '' ) . ' ' . $extra . '>';
		}
		echo '</label>';
	}

	private static function shell( $kind, $fields_callback ) {
		$data = self::errors();
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data" novalidate>';
		echo '<input type="hidden" name="action" value="simali_enquiry">';
		echo '<input type="hidden" name="simali_kind" value="' . esc_attr( $kind ) . '">';
		echo '<input type="hidden" name="simali_started" value="' . esc_attr( (string) time() ) . '">';
		wp_nonce_field( 'simali_enquiry', 'simali_enquiry_nonce' );
		echo '<p class="hp"><label>Website <input name="simali_website" tabindex="-1" autocomplete="off"></label></p>';
		if ( ! empty( $data['messages'] ) ) {
			echo '<div class="error-summary" role="alert"><p>Please check the following:</p><ul>';
			foreach ( $data['messages'] as $message ) {
				echo '<li>' . esc_html( $message ) . '</li>';
			}
			echo '</ul></div>';
		}
		call_user_func( $fields_callback, $data['old'] );
		echo '<label class="field"><input type="checkbox" name="consent" value="1" required> I agree to Simali Stone using these details to reply to this enquiry.</label>';
		echo '<button class="btn" type="submit">Send</button></form>';
	}

	public static function quote_form( $atts ) {
		$atts = shortcode_atts( array( 'product' => '' ), $atts );
		ob_start();
		self::shell(
			'quote',
			function ( $old ) use ( $atts ) {
				if ( empty( $old['product'] ) && $atts['product'] ) {
					$old['product'] = $atts['product'];
				}
				self::field( 'name', 'Name', 'text', true, $old, 'autocomplete="name"' );
				self::field( 'email', 'Email', 'email', true, $old, 'autocomplete="email"' );
				self::field( 'phone', 'Phone', 'tel', true, $old, 'autocomplete="tel"' );
				self::field( 'postcode', 'Postcode', 'text', true, $old, 'autocomplete="postal-code"' );
				self::field( 'property', 'Property type', 'text', false, $old );
				echo '<fieldset><legend>Rooms</legend>';
				foreach ( array( 'Kitchen', 'Hall', 'Bathroom', 'Living', 'Outdoor' ) as $room ) {
					echo '<label><input type="checkbox" name="rooms[]" value="' . esc_attr( $room ) . '"> ' . esc_html( $room ) . '</label> ';
				}
				echo '</fieldset>';
				self::field( 'area', 'Approximate m²', 'text', true, $old, 'inputmode="decimal"' );
				self::field( 'product', 'Product of interest', 'text', false, $old, 'list="simali-product-list"' );
				echo '<datalist id="simali-product-list">';
				$products = get_posts( array( 'post_type' => 'product', 'posts_per_page' => 100, 'post_status' => 'publish' ) );
				foreach ( $products as $product ) {
					echo '<option value="' . esc_attr( $product->post_title ) . '">';
				}
				echo '</datalist>';
				echo '<fieldset><legend>Is the stone supplied by Simali?</legend>';
				foreach ( array( 'yes', 'no', 'unsure' ) as $answer ) {
					echo '<label><input type="radio" name="supplied" value="' . esc_attr( $answer ) . '"> ' . esc_html( ucfirst( $answer ) ) . '</label> ';
				}
				echo '</fieldset>';
				echo '<label class="field">Timescale <select name="timescale"><option value="">Choose</option><option>ASAP</option><option>1–3 months</option><option>3+ months</option></select></label>';
				echo '<label class="field">Photographs <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.heic,.pdf,image/jpeg,image/png,image/heic,application/pdf" multiple></label>';
				self::field( 'message', 'Message', 'textarea', false, $old );
				echo '<label class="field">Preferred contact <select name="contact_method"><option>Phone</option><option>Email</option></select></label>';
			}
		);
		return ob_get_clean();
	}

	public static function contact_form( $atts ) {
		$atts = shortcode_atts( array( 'subject' => '' ), $atts );
		ob_start();
		self::shell(
			'contact',
			function ( $old ) use ( $atts ) {
				if ( empty( $old['enquiry_type'] ) && $atts['subject'] ) {
					$old['enquiry_type'] = $atts['subject'];
				}
				self::field( 'name', 'Name', 'text', true, $old, 'autocomplete="name"' );
				self::field( 'email', 'Email', 'email', true, $old, 'autocomplete="email"' );
				self::field( 'phone', 'Phone', 'tel', true, $old, 'autocomplete="tel"' );
				self::field( 'postcode', 'Postcode', 'text', false, $old, 'autocomplete="postal-code"' );
				echo '<label class="field">Enquiry type <select name="enquiry_type">';
				foreach ( array( 'Samples', 'Price', 'Installation', 'Showroom visit', 'Other' ) as $type ) {
					echo '<option ' . selected( $old['enquiry_type'] ?? $atts['subject'], $type, false ) . '>' . esc_html( $type ) . '</option>';
				}
				echo '</select></label>';
				self::field( 'message', 'Message', 'textarea', true, $old );
				echo '<label class="field">Photographs, up to 5 files, 10 MB each <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.heic,.pdf" multiple></label>';
			}
		);
		return ob_get_clean();
	}

	public static function sample_form() {
		$ids = Simali_Samples::items();
		ob_start();
		echo '<ul id="sample-list">';
		if ( ! $ids ) {
			echo '<li>No samples yet. Add them from a product.</li>';
		}
		foreach ( $ids as $id ) {
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
			if ( ! $product ) {
				continue;
			}
			echo '<li>' . esc_html( $product->get_name() ) . ' <button class="btn btn-secondary" type="button" data-remove-sample="' . esc_attr( (string) $id ) . '">Remove</button></li>';
		}
		echo '</ul>';
		self::shell(
			'sample',
			function ( $old ) use ( $ids ) {
				echo '<input type="hidden" name="samples" value="' . esc_attr( implode( ',', $ids ) ) . '">';
				self::field( 'name', 'Name', 'text', true, $old, 'autocomplete="name"' );
				self::field( 'email', 'Email', 'email', true, $old, 'autocomplete="email"' );
				self::field( 'phone', 'Phone', 'tel', true, $old, 'autocomplete="tel"' );
				self::field( 'address', 'Address', 'textarea', true, $old );
				self::field( 'postcode', 'Postcode', 'text', true, $old, 'autocomplete="postal-code"' );
				self::field( 'message', 'Project notes', 'textarea', false, $old );
			}
		);
		$max = (int) get_option( 'simali_sample_max' );
		if ( $max > 0 ) {
			echo '<p class="fine">Up to ' . esc_html( (string) $max ) . ' samples per order. If you would like more, mention it in the project notes.</p>';
		} else {
			echo '<p class="fine">Choosing between several stones? Add them all. If an order is unusually large we will call before we send it.</p>';
		}
		return ob_get_clean();
	}

	public static function handle() {
		if ( ! isset( $_POST['simali_enquiry_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simali_enquiry_nonce'] ) ), 'simali_enquiry' ) ) {
			wp_die( 'The form expired. Go back and try again.', 403 );
		}
		if ( ! empty( $_POST['simali_website'] ) ) {
			wp_safe_redirect( home_url( '/thank-you/' ) );
			exit;
		}
		$started = isset( $_POST['simali_started'] ) ? (int) $_POST['simali_started'] : 0;
		if ( $started && ( time() - $started ) < 3 ) {
			self::fail( array( 'Please wait a moment and send the form again.' ), self::old() );
		}
		$kind = isset( $_POST['simali_kind'] ) ? sanitize_key( wp_unslash( $_POST['simali_kind'] ) ) : 'contact';
		$old  = self::old();
		$errors = array();
		foreach ( array( 'name' => 'Name', 'email' => 'Email' ) as $key => $label ) {
			if ( empty( $old[ $key ] ) ) {
				$errors[] = $label . ' is required.';
			}
		}
		if ( ! empty( $old['email'] ) && ! is_email( $old['email'] ) ) {
			$errors[] = 'Enter a valid email address.';
		}
		if ( in_array( $kind, array( 'quote', 'sample' ), true ) && empty( $old['phone'] ) ) {
			$errors[] = 'Phone is required.';
		}
		if ( 'quote' === $kind ) {
			if ( empty( $old['postcode'] ) ) {
				$errors[] = 'Postcode is required.';
			}
			if ( empty( $old['area'] ) || ! is_numeric( $old['area'] ) ) {
				$errors[] = 'Approximate m² must be a number.';
			}
		}
		if ( 'sample' === $kind && ( empty( $old['address'] ) || empty( $old['postcode'] ) ) ) {
			$errors[] = 'Address and postcode are required for samples.';
		}
		if ( 'contact' === $kind && empty( $old['phone'] ) ) {
			$errors[] = 'Phone is required.';
		}
		if ( empty( $_POST['consent'] ) ) {
			$errors[] = 'Consent is required.';
		}
		$uploads = self::uploads( $errors );
		if ( $errors ) {
			self::fail( $errors, $old );
		}
		$postcode_check = ! empty( $old['postcode'] ) ? Simali_Postcode::check( $old['postcode'] ) : array();
		$id = wp_insert_post(
			array(
				'post_type'   => 'simali_enquiry',
				'post_status' => 'publish',
				'post_title'  => $kind . ' from ' . $old['name'],
			)
		);
		update_post_meta( $id, '_simali_payload', $old );
		update_post_meta( $id, '_simali_kind', $kind );
		update_post_meta( $id, '_simali_files', $uploads );
		update_post_meta( $id, '_simali_postcode_check', $postcode_check );
		$body = self::email_body( $kind, $old, $postcode_check );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $old['name'] . ' <' . $old['email'] . '>' );
		$sales_sent = wp_mail( 'sales@simalistone.co.uk', 'Simali enquiry: ' . $old['name'], $body, $headers );
		$customer_sent = wp_mail( $old['email'], 'We have your Simali Stone enquiry', "Thank you. This is a copy of what you sent.\n\n" . $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
		update_post_meta( $id, '_simali_mail', array( 'sales' => $sales_sent, 'customer' => $customer_sent, 'body' => $body ) );
		wp_safe_redirect( home_url( '/thank-you/?type=' . rawurlencode( $kind ) ) );
		exit;
	}

	private static function old() {
		$keys = array( 'name', 'email', 'phone', 'postcode', 'property', 'area', 'product', 'supplied', 'timescale', 'message', 'contact_method', 'address', 'enquiry_type', 'samples' );
		$old  = array();
		foreach ( $keys as $key ) {
			$old[ $key ] = isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '';
		}
		$old['rooms'] = isset( $_POST['rooms'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['rooms'] ) ) : array();
		return $old;
	}

	private static function fail( array $messages, array $old ) {
		$key = wp_generate_password( 12, false );
		set_transient( 'simali_form_' . $key, array( 'messages' => $messages, 'old' => $old ), MINUTE_IN_SECONDS );
		setcookie( 'simali_form', $key, time() + MINUTE_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		wp_safe_redirect( $back );
		exit;
	}

	private static function uploads( array &$errors ) {
		if ( empty( $_FILES['photos']['name'] ) || ! is_array( $_FILES['photos']['name'] ) ) {
			return array();
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$saved = array();
		$count = count( $_FILES['photos']['name'] );
		if ( $count > 5 ) {
			$errors[] = 'Attach up to 5 files.';
			return array();
		}
		$allowed = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'heic' => 'image/heic', 'pdf' => 'application/pdf' );
		foreach ( $_FILES['photos']['name'] as $index => $name ) {
			if ( UPLOAD_ERR_NO_FILE === (int) $_FILES['photos']['error'][ $index ] ) {
				continue;
			}
			if ( (int) $_FILES['photos']['size'][ $index ] > 10 * 1024 * 1024 ) {
				$errors[] = 'Each file must be 10 MB or smaller.';
				continue;
			}
			$file = array(
				'name'     => sanitize_file_name( $name ),
				'type'     => $_FILES['photos']['type'][ $index ],
				'tmp_name' => $_FILES['photos']['tmp_name'][ $index ],
				'error'    => $_FILES['photos']['error'][ $index ],
				'size'     => $_FILES['photos']['size'][ $index ],
			);
			$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
			if ( empty( $check['ext'] ) ) {
				$errors[] = 'Use jpg, png, heic or pdf files.';
				continue;
			}
			$moved = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => $allowed ) );
			if ( isset( $moved['error'] ) ) {
				$errors[] = $moved['error'];
			} else {
				$saved[] = $moved['url'];
			}
		}
		return $saved;
	}

	private static function email_body( $kind, $old, $check ) {
		$lines = array( 'Type: ' . $kind );
		foreach ( $old as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}
			if ( '' !== $value ) {
				$lines[] = $key . ': ' . $value;
			}
		}
		if ( ! empty( $check['message'] ) ) {
			$lines[] = 'Area check: ' . $check['message'];
		}
		return implode( "\n", $lines );
	}
}
