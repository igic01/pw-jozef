<?php
/**
 * ACF field definitions for the Raspored page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function starter_raspored_acf_default_values() {
	return array(
		'raspored_title' => 'Raspored radionica',
		'raspored_desc'  => 'Izaberite termin, temu i drustvo, a mi pripremamo platno, boje, vino i atmosferu.',
	);
}

function starter_raspored_acf_default_value( $name, $default = '' ) {
	$defaults = starter_raspored_acf_default_values();

	return array_key_exists( $name, $defaults ) ? $defaults[ $name ] : $default;
}

function starter_raspored_acf_load_default_value( $value, $post_id, $field ) {
	if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
		return $value;
	}

	if ( empty( $field['key'] ) || 0 !== strpos( $field['key'], 'field_starter_raspored_' ) ) {
		return $value;
	}

	if ( empty( $field['name'] ) ) {
		return $value;
	}

	return starter_raspored_acf_default_value( $field['name'], $value );
}
add_filter( 'acf/load_value', 'starter_raspored_acf_load_default_value', 10, 3 );

function starter_register_raspored_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$defaults = starter_raspored_acf_default_values();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_starter_raspored',
			'title'                 => __( 'Raspored Page Content', 'starter-theme' ),
			'fields'                => array(
				array(
					'key'           => 'field_starter_raspored_title',
					'label'         => __( 'Title', 'starter-theme' ),
					'name'          => 'raspored_title',
					'type'          => 'text',
					'required'      => 0,
					'default_value' => $defaults['raspored_title'],
				),
				array(
					'key'           => 'field_starter_raspored_desc',
					'label'         => __( 'Description', 'starter-theme' ),
					'name'          => 'raspored_desc',
					'type'          => 'wysiwyg',
					'tabs'          => 'all',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'delay'         => 0,
					'default_value' => $defaults['raspored_desc'],
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/raspored.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => array(),
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
}
add_action( 'acf/init', 'starter_register_raspored_acf_fields' );
