<?php
/*
 * Plugin Name:  Per Page Locale
 * Plugin URI:   https://zaerl.com/
 * Description:  Allows you to change the locale per page.
 * Version:      0.1
 * Requires at least: 4.4
 * Author:       Francesco Bigiarini
 * Author URI:   https://zaerl.com/
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  za-per-page-locale
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Display the meta box
 *
 * @param WP_Post $post The post object
 */
function za_ppl_meta_box_callback( $post ) {
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';

	$locale    = get_post_meta( $post->ID, 'za_ppl_locale', true );
	$en_us     = array( 'en_US' => array( 'english_name' => 'English (United States)' ) );
	$languages = array_merge( $en_us, wp_get_available_translations() );

	wp_nonce_field( 'za_ppl_save_post', 'za_ppl_nonce' );
	echo '<select name="za_ppl_locale">';

	foreach ( $languages as $code => $language ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $code ), selected( $locale, $code, false ), esc_html( $language['english_name'] ) );
	}

	echo '</select>';
}

/*
 * Add a meta box to the post editor
 */
function za_ppl_add_meta_box() {
	add_meta_box( 'za_ppl', __( 'Locale', 'za-per-page-locale' ), 'za_ppl_meta_box_callback', array( 'page', 'post' ), 'side' );
}

add_action( 'add_meta_boxes', 'za_ppl_add_meta_box' );

/*
 * Save the locale when the post is saved
 *
 * @param int $post_id The post ID
 */
function za_ppl_save_post( $post_id ) {
	if ( ! isset( $_POST['za_ppl_nonce'] ) || ! wp_verify_nonce( $_POST['za_ppl_nonce'], 'za_ppl_save_post' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( array_key_exists( 'za_ppl_locale', $_POST ) ) {
		update_post_meta( $post_id, 'za_ppl_locale', $_POST['za_ppl_locale'] );
	}
}

add_action( 'save_post', 'za_ppl_save_post' );

/*
 * Filter the locale
 *
 * @param string $locale The current locale
 * @return string The new locale
 */
function za_ppl_filter_locale( $locale ) {
	if ( is_singular( array( 'post', 'page' ) ) ) {
		$post_locale = get_post_meta( get_queried_object_id(), 'za_ppl_locale', true );

		if ( $post_locale ) {
			return $post_locale;
		}
	}
	return $locale;
}

add_filter( 'locale', 'za_ppl_filter_locale' );
