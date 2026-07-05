<?php
/**
 * Theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STARTER_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/acf-home.php';
require_once get_template_directory() . '/inc/woocommerce-product-fields.php';

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

if ( ! function_exists( 'starter_get_upcoming_products' ) ) {
	function starter_get_upcoming_products( $args = array() ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$args = wp_parse_args(
			$args,
			array(
				'status'             => 'publish',
				'limit'              => -1,
				'category'           => '',
				'categories'         => '',
				'exclude_category'   => '',
				'exclude_categories' => '',
			)
		);

		$query_args = array(
			'status'     => $args['status'],
			'limit'      => $args['limit'],
			'meta_key'   => '_starter_product_date',
			'orderby'    => 'meta_value',
			'order'      => 'ASC',
			'meta_query' => array(
				array(
					'key'     => '_starter_product_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		);

		$selected_slugs = starter_parse_product_category_slugs( $args['category'], $args['categories'] );
		$excluded_slugs = starter_parse_product_category_slugs( $args['exclude_category'], $args['exclude_categories'] );

		if ( ! empty( $selected_slugs ) ) {
			$query_args['category'] = $selected_slugs;
		}

		$products = wc_get_products( $query_args );

		if ( ! empty( $excluded_slugs ) ) {
			$products = array_filter(
				$products,
				function ( $product ) use ( $excluded_slugs ) {
					return ! starter_product_has_category_slug( $product->get_id(), $excluded_slugs );
				}
			);
		}

		return array_values( array_map( 'starter_format_upcoming_product', $products ) );
	}
}

if ( ! function_exists( 'starter_format_upcoming_product' ) ) {
	function starter_format_upcoming_product( $product ) {
		$product_id  = $product->get_id();
		$categories  = starter_get_product_category_data( $product_id );
		$date        = $product->get_meta( '_starter_product_date' );
		$difficulty  = $product->get_meta( '_starter_product_difficulty' );
		$image_html  = $product->get_image( 'large', array( 'loading' => 'lazy' ) );

		if ( ! $image_html && function_exists( 'wc_placeholder_img' ) ) {
			$image_html = wc_placeholder_img( 'large' );
		}

		return array(
			'id'               => $product_id,
			'name'             => $product->get_name(),
			'date'             => $date,
			'display_date'     => starter_format_product_date( $date ),
			'difficulty'       => '' === $difficulty ? null : absint( $difficulty ),
			'difficulty_label' => starter_format_product_difficulty( $difficulty ),
			'category'         => implode( ', ', wp_list_pluck( $categories, 'name' ) ),
			'categories'       => $categories,
			'category_slugs'   => wp_list_pluck( $categories, 'slug' ),
			'link'             => get_permalink( $product_id ),
			'add_to_cart_link' => $product->add_to_cart_url(),
			'price_html'       => $product->get_price_html(),
			'image_html'       => $image_html,
		);
	}
}

if ( ! function_exists( 'starter_get_product_category_data' ) ) {
	function starter_get_product_category_data( $product_id ) {
		$terms = get_the_terms( $product_id, 'product_cat' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return array_values(
			array_map(
				function ( $term ) {
					$link = get_term_link( $term );

					return array(
						'id'   => $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
						'link' => is_wp_error( $link ) ? '' : $link,
					);
				},
				$terms
			)
		);
	}
}

if ( ! function_exists( 'starter_parse_product_category_slugs' ) ) {
	function starter_parse_product_category_slugs( $category, $categories = '' ) {
		$raw = array();

		if ( $category ) {
			$raw = array_merge( $raw, (array) $category );
		}

		if ( $categories ) {
			foreach ( (array) $categories as $category_group ) {
				$raw = array_merge( $raw, explode( ',', $category_group ) );
			}
		}

		$slugs = array();

		foreach ( $raw as $value ) {
			$slug = sanitize_title( wp_unslash( trim( $value ) ) );

			if ( $slug ) {
				$slugs[] = $slug;
			}
		}

		return array_values( array_unique( $slugs ) );
	}
}

if ( ! function_exists( 'starter_product_has_category_slug' ) ) {
	function starter_product_has_category_slug( $product_id, $slugs ) {
		$product_slugs = wp_list_pluck( starter_get_product_category_data( $product_id ), 'slug' );

		return (bool) array_intersect( $product_slugs, (array) $slugs );
	}
}

if ( ! function_exists( 'starter_format_product_date' ) ) {
	function starter_format_product_date( $date ) {
		if ( ! $date ) {
			return '';
		}

		$timestamp = strtotime( $date );

		return $timestamp ? wp_date( 'd.m.Y.', $timestamp ) : $date;
	}
}

if ( ! function_exists( 'starter_format_product_difficulty' ) ) {
	function starter_format_product_difficulty( $difficulty ) {
		$difficulty = absint( $difficulty );

		if ( $difficulty < 1 ) {
			return '';
		}

		return str_repeat( '/', min( 5, $difficulty ) );
	}
}
