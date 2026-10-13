<?php
/**
 * Photographs imported from simalistone.co.uk.
 *
 * The optimised files live in wp-content/uploads/simali/. This class turns them into
 * media-library attachments on demand, without copying or duplicating them, so the
 * same file can be reused by products, case studies and the theme.
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simali_Media {
	const DIR = 'simali';

	/**
	 * Absolute path of an imported file, or '' when it is not on disk.
	 */
	public static function path( $file ) {
		$file = self::clean( $file );
		if ( '' === $file ) {
			return '';
		}
		$upload = wp_upload_dir();
		$path   = trailingslashit( $upload['basedir'] ) . self::DIR . '/' . $file;
		return file_exists( $path ) ? $path : '';
	}

	/**
	 * Public URL of an imported file, or '' when it is not on disk.
	 */
	public static function url( $file ) {
		if ( ! self::path( $file ) ) {
			return '';
		}
		$upload = wp_upload_dir();
		return trailingslashit( $upload['baseurl'] ) . self::DIR . '/' . self::clean( $file );
	}

	/**
	 * Width and height of an imported file.
	 */
	public static function size( $file ) {
		$path = self::path( $file );
		if ( ! $path ) {
			return array( 0, 0 );
		}
		$size = wp_getimagesize( $path );
		return $size ? array( (int) $size[0], (int) $size[1] ) : array( 0, 0 );
	}

	/**
	 * Attachment ID for an imported file, creating the attachment the first time.
	 *
	 * @param array $image  { file, alt, source }.
	 * @param int   $parent Post the attachment belongs to.
	 */
	public static function attach( array $image, $parent = 0 ) {
		$file = self::clean( $image['file'] ?? '' );
		if ( '' === $file ) {
			return 0;
		}
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_simali_file',
				'meta_value'     => $file,
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}
		$path = self::path( $file );
		if ( ! $path && ! empty( $image['source'] ) ) {
			$path = self::fetch( $image['source'], $file );
		}
		if ( ! $path ) {
			return 0;
		}
		$type = wp_check_filetype( $path );
		$alt  = trim( (string) ( $image['alt'] ?? '' ) );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $type['type'] ?: 'image/jpeg',
				'post_title'     => $alt ? $alt : preg_replace( '/\.[a-z]+$/i', '', $file ),
				'post_status'    => 'inherit',
				'post_parent'    => (int) $parent,
			),
			$path
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		update_post_meta( $id, '_simali_file', $file );
		if ( $alt ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$meta = wp_generate_attachment_metadata( $id, $path );
		if ( $meta ) {
			wp_update_attachment_metadata( $id, $meta );
		}
		return (int) $id;
	}

	/**
	 * Attach several images and return their IDs, skipping any that are missing.
	 */
	public static function attach_all( array $images, $parent = 0 ) {
		$ids = array();
		foreach ( $images as $image ) {
			if ( is_string( $image ) ) {
				$image = array( 'file' => $image );
			}
			$id = self::attach( $image, $parent );
			if ( $id ) {
				$ids[] = $id;
			}
		}
		return $ids;
	}

	/**
	 * Download a photograph from the previous site when it is not in uploads yet.
	 */
	private static function fetch( $source, $file ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$tmp = download_url( $source, 60 );
		if ( is_wp_error( $tmp ) ) {
			return '';
		}
		$upload = wp_upload_dir();
		$dir    = trailingslashit( $upload['basedir'] ) . self::DIR;
		wp_mkdir_p( $dir );
		$dest = $dir . '/' . $file;
		if ( ! @rename( $tmp, $dest ) && ! @copy( $tmp, $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return '';
		}
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return $dest;
	}

	private static function clean( $file ) {
		$file = basename( (string) $file );
		return preg_match( '/^[a-z0-9._-]+\.(jpe?g|png|webp)$/i', $file ) ? $file : '';
	}
}
