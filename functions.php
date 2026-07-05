<?php
/**
 * Theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STARTER_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/acf-home.php';

function starter_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'starter-theme' ),
	) );
}
add_action( 'after_setup_theme', 'starter_theme_setup' );

function starter_theme_assets() {
	wp_enqueue_style(
		'starter-theme-style',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		STARTER_THEME_VERSION
	);

	wp_enqueue_script(
		'starter-theme-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		STARTER_THEME_VERSION,
		true
	);

	$template_slug = get_page_template_slug();

	if ( $template_slug && 0 === strpos( $template_slug, 'templates/' ) ) {
		$template_name = basename( $template_slug, '.php' );
		$template_css  = get_template_directory() . '/assets/css/templates/' . $template_name . '.css';

		if ( file_exists( $template_css ) ) {
			wp_enqueue_style(
				'starter-theme-template-' . $template_name,
				get_template_directory_uri() . '/assets/css/templates/' . $template_name . '.css',
				array( 'starter-theme-style' ),
				STARTER_THEME_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'starter_theme_assets' );
