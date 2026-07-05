<?php
/**
 * Extra WooCommerce product fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'starter_product_extra_fields' ) ) {
	function starter_product_extra_fields() {
		if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
			return;
		}

		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'                => '_starter_product_date',
				'label'             => __( 'Date', 'starter-theme' ),
				'description'       => __( 'Required. Select the product date.', 'starter-theme' ),
				'desc_tip'          => true,
				'type'              => 'date',
				'custom_attributes' => array(
					'required' => 'required',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_starter_product_difficulty',
				'label'             => __( 'Difficulty', 'starter-theme' ),
				'description'       => __( 'Required. Enter a number from 1 to 5.', 'starter-theme' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'min'      => '1',
					'max'      => '5',
					'step'     => '1',
					'required' => 'required',
				),
			)
		);

		echo '</div>';
	}
}
add_action( 'woocommerce_product_options_general_product_data', 'starter_product_extra_fields' );

if ( ! function_exists( 'starter_require_product_extra_fields_before_publish' ) ) {
	function starter_require_product_extra_fields_before_publish( $data, $postarr ) {
		if ( empty( $data['post_type'] ) || 'product' !== $data['post_type'] ) {
			return $data;
		}

		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
			return $data;
		}

		if ( ! in_array( $data['post_status'], array( 'publish', 'future', 'private' ), true ) ) {
			return $data;
		}

		$date       = isset( $_POST['_starter_product_date'] ) ? sanitize_text_field( wp_unslash( $_POST['_starter_product_date'] ) ) : '';
		$difficulty = isset( $_POST['_starter_product_difficulty'] ) ? absint( wp_unslash( $_POST['_starter_product_difficulty'] ) ) : 0;

		if ( starter_is_valid_product_date( $date ) && $difficulty >= 1 && $difficulty <= 5 ) {
			return $data;
		}

		$data['post_status'] = 'draft';

		if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Product date and difficulty are required before this product can be published.', 'starter-theme' ) );
		}

		return $data;
	}
}
add_filter( 'wp_insert_post_data', 'starter_require_product_extra_fields_before_publish', 10, 2 );

if ( ! function_exists( 'starter_save_product_extra_fields' ) ) {
	function starter_save_product_extra_fields( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$date       = isset( $_POST['_starter_product_date'] ) ? sanitize_text_field( wp_unslash( $_POST['_starter_product_date'] ) ) : '';
		$difficulty = isset( $_POST['_starter_product_difficulty'] ) ? absint( wp_unslash( $_POST['_starter_product_difficulty'] ) ) : 0;

		if ( starter_is_valid_product_date( $date ) ) {
			$product->update_meta_data( '_starter_product_date', $date );
		} else {
			WC_Admin_Meta_Boxes::add_error( __( 'Product date is required and must be a valid date.', 'starter-theme' ) );
		}

		if ( $difficulty >= 1 && $difficulty <= 5 ) {
			$product->update_meta_data( '_starter_product_difficulty', $difficulty );
		} else {
			WC_Admin_Meta_Boxes::add_error( __( 'Product difficulty is required and must be between 1 and 5.', 'starter-theme' ) );
		}
	}
}
add_action( 'woocommerce_admin_process_product_object', 'starter_save_product_extra_fields' );

if ( ! function_exists( 'starter_is_valid_product_date' ) ) {
	function starter_is_valid_product_date( $date ) {
		if ( ! is_string( $date ) || '' === $date ) {
			return false;
		}

		$parsed = DateTime::createFromFormat( 'Y-m-d', $date );

		return $parsed && $parsed->format( 'Y-m-d' ) === $date;
	}
}

if ( ! function_exists( 'starter_display_product_extra_fields' ) ) {
	function starter_display_product_extra_fields() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$date       = $product->get_meta( '_starter_product_date' );
		$difficulty = $product->get_meta( '_starter_product_difficulty' );

		if ( ! $date && ! $difficulty ) {
			return;
		}

		echo '<div class="starter-product-meta">';

		if ( $date ) {
			printf(
				'<p class="starter-product-meta__item"><strong>%1$s</strong> %2$s</p>',
				esc_html__( 'Date:', 'starter-theme' ),
				esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) )
			);
		}

		if ( $difficulty ) {
			printf(
				'<p class="starter-product-meta__item"><strong>%1$s</strong> %2$d/5</p>',
				esc_html__( 'Difficulty:', 'starter-theme' ),
				absint( $difficulty )
			);
		}

		echo '</div>';
	}
}
add_action( 'woocommerce_single_product_summary', 'starter_display_product_extra_fields', 25 );
