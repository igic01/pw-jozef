<?php
/**
 * ACF field definitions for the Home page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'STARTER_HOME_PLACEHOLDER_IMAGE' ) ) {
	define( 'STARTER_HOME_PLACEHOLDER_IMAGE', 'http://pw-2.local/wp-content/uploads/2026/07/placeholder.webp' );
}

function starter_home_acf_placeholder_image_value() {
	static $placeholder = null;

	if ( null !== $placeholder ) {
		return $placeholder;
	}

	$placeholder = STARTER_HOME_PLACEHOLDER_IMAGE;

	if ( function_exists( 'attachment_url_to_postid' ) ) {
		$attachment_id = attachment_url_to_postid( STARTER_HOME_PLACEHOLDER_IMAGE );

		if ( $attachment_id ) {
			$placeholder = $attachment_id;
		}
	}

	return $placeholder;
}

function starter_home_acf_default_values() {
	$image = starter_home_acf_placeholder_image_value();

	return array(
		'hero_title'       => 'Paint & Wine',
		'hero_desc'        => 'Slikarske radionice za amatere i ljude bez slikarskog iskustva, uz vino, druzenje i uspomene koje nosite kuci.',
		'hero_images'      => array_fill( 0, 3, $image ),
		'section_1_title'  => 'Otkrijte i vi umjetnika u sebi!',
		'section_1_desc'   => 'Na nasem mjesecnom programu odaberete temu koja vam se najvise svidja i prijavite se za radionicu sa tim datumom. Kada dodjete, ceka vas sav materijal potreban za slikanje i instruktorica koja vas vodi korak po korak kroz proces.',
		'section_1_images' => array_fill( 0, 4, $image ),
		'section_2_title'  => 'Raspored radionica',
		'section_2_desc'   => 'Izaberite termin, temu i drustvo, a mi pripremamo platno, boje, vino i atmosferu.',
		'section_3_hero'   => $image,
		'section_3_title'  => 'Zelite da iznenadite voljenu osobu posebnim poklonom?',
		'section_3_images' => array_fill( 0, 3, $image ),
		'section_4_title'  => 'Organizujete team building, djevojacko vece, rodjendan ili proslavu?',
		'section_4_desc'   => 'Zakazite privatnu radionicu. Nudimo nekoliko vrsta dogadjaja, a vi birate onu najbolju za vas.',
		'section_4_posts'  => array(
			array(
				'img'   => $image,
				'title' => 'Team Building',
				'desc'  => 'Privatna radionica za timove i opusteno druzenje uz slikanje.',
				'url'   => '',
			),
			array(
				'img'   => $image,
				'title' => 'Djevojacko Vece',
				'desc'  => 'Kreativno vece za drustvo, vino i uspomene prije proslave.',
				'url'   => '',
			),
			array(
				'img'   => $image,
				'title' => 'Rodjendan',
				'desc'  => 'Posebna rodjendanska radionica za vase najblize goste.',
				'url'   => '',
			),
			array(
				'img'   => $image,
				'title' => 'Porodicna Proslava',
				'desc'  => 'Toplo druzenje za porodicu, smijeh i zajednicku sliku.',
				'url'   => '',
			),
			array(
				'img'   => $image,
				'title' => 'Tematska Radionica',
				'desc'  => 'Odaberite temu i napravite radionicu po svom ukusu.',
				'url'   => '',
			),
		),
		'form_title'       => 'Postanite vinski saradnik!',
		'form_desc'        => 'Ukoliko vam se svidja nas koncept i zelite da nasi gosti probaju vasa vina, ostavite nam poruku, a mi cemo vas kontaktirati u kratkom roku.',
		'form_desc2'       => 'Slikanje dokazano smanjuje stres, poboljsava emocionalnu regulaciju, samopouzdanje, koncentraciju i paznju.',
	);
}

function starter_home_acf_default_value( $name, $default = '' ) {
	$defaults = starter_home_acf_default_values();

	return array_key_exists( $name, $defaults ) ? $defaults[ $name ] : $default;
}

function starter_home_acf_load_default_value( $value, $post_id, $field ) {
	if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
		return $value;
	}

	if ( empty( $field['name'] ) ) {
		return $value;
	}

	return starter_home_acf_default_value( $field['name'], $value );
}
add_filter( 'acf/load_value', 'starter_home_acf_load_default_value', 10, 3 );

function starter_register_home_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$defaults = starter_home_acf_default_values();
	$image    = starter_home_acf_placeholder_image_value();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_starter_home',
			'title'                 => __( 'Home Page Content', 'starter-theme' ),
			'fields'                => array(
				array(
					'key'               => 'field_starter_home_hero_tab',
					'label'             => __( 'Hero', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_hero_title',
					'label'             => __( 'Hero Title', 'starter-theme' ),
					'name'              => 'hero_title',
					'type'              => 'text',
					'required'          => 0,
					'default_value'     => $defaults['hero_title'],
				),
				array(
					'key'               => 'field_starter_home_hero_desc',
					'label'             => __( 'Hero Desc', 'starter-theme' ),
					'name'              => 'hero_desc',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['hero_desc'],
				),
				array(
					'key'               => 'field_starter_home_hero_images',
					'label'             => __( 'Hero Images', 'starter-theme' ),
					'name'              => 'hero_images',
					'type'              => 'gallery',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'insert'            => 'append',
					'library'           => 'all',
					'min'               => 0,
					'max'               => 3,
					'default_value'     => $defaults['hero_images'],
				),
				array(
					'key'               => 'field_starter_home_section_1_tab',
					'label'             => __( 'Section 1', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_section_1_title',
					'label'             => __( 'Section 1 Title', 'starter-theme' ),
					'name'              => 'section_1_title',
					'type'              => 'text',
					'default_value'     => $defaults['section_1_title'],
				),
				array(
					'key'               => 'field_starter_home_section_1_desc',
					'label'             => __( 'Section 1 Desc', 'starter-theme' ),
					'name'              => 'section_1_desc',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['section_1_desc'],
				),
				array(
					'key'               => 'field_starter_home_section_1_images',
					'label'             => __( 'Section 1 Images', 'starter-theme' ),
					'name'              => 'section_1_images',
					'type'              => 'gallery',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'insert'            => 'append',
					'library'           => 'all',
					'min'               => 0,
					'max'               => 4,
					'default_value'     => $defaults['section_1_images'],
				),
				array(
					'key'               => 'field_starter_home_section_2_tab',
					'label'             => __( 'Section 2', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_section_2_title',
					'label'             => __( 'Section 2 Title', 'starter-theme' ),
					'name'              => 'section_2_title',
					'type'              => 'text',
					'default_value'     => $defaults['section_2_title'],
				),
				array(
					'key'               => 'field_starter_home_section_2_desc',
					'label'             => __( 'Section 2 Desc', 'starter-theme' ),
					'name'              => 'section_2_desc',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['section_2_desc'],
				),
				array(
					'key'               => 'field_starter_home_section_3_tab',
					'label'             => __( 'Section 3', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_section_3_hero',
					'label'             => __( 'Section 3 Hero', 'starter-theme' ),
					'name'              => 'section_3_hero',
					'type'              => 'image',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'default_value'     => $defaults['section_3_hero'],
				),
				array(
					'key'               => 'field_starter_home_section_3_title',
					'label'             => __( 'Section 3 Title', 'starter-theme' ),
					'name'              => 'section_3_title',
					'type'              => 'text',
					'default_value'     => $defaults['section_3_title'],
				),
				array(
					'key'               => 'field_starter_home_section_3_images',
					'label'             => __( 'Section 3 Images', 'starter-theme' ),
					'name'              => 'section_3_images',
					'type'              => 'gallery',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'insert'            => 'append',
					'library'           => 'all',
					'min'               => 0,
					'max'               => 3,
					'default_value'     => $defaults['section_3_images'],
				),
				array(
					'key'               => 'field_starter_home_section_4_tab',
					'label'             => __( 'Section 4', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_section_4_title',
					'label'             => __( 'Section 4 Title', 'starter-theme' ),
					'name'              => 'section_4_title',
					'type'              => 'text',
					'default_value'     => $defaults['section_4_title'],
				),
				array(
					'key'               => 'field_starter_home_section_4_desc',
					'label'             => __( 'Section 4 Desc', 'starter-theme' ),
					'name'              => 'section_4_desc',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['section_4_desc'],
				),
				array(
					'key'               => 'field_starter_home_section_4_posts',
					'label'             => __( 'Section 4 Posts', 'starter-theme' ),
					'name'              => 'section_4_posts',
					'type'              => 'repeater',
					'layout'            => 'block',
					'button_label'      => __( 'Add Post Card', 'starter-theme' ),
					'collapsed'         => 'field_starter_home_section_4_post_title',
					'default_value'     => $defaults['section_4_posts'],
					'sub_fields'        => array(
						array(
							'key'           => 'field_starter_home_section_4_post_img',
							'label'         => __( 'Image', 'starter-theme' ),
							'name'          => 'img',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
							'library'       => 'all',
							'default_value' => $image,
						),
						array(
							'key'           => 'field_starter_home_section_4_post_title',
							'label'         => __( 'Title', 'starter-theme' ),
							'name'          => 'title',
							'type'          => 'text',
							'default_value' => 'Team Building',
						),
						array(
							'key'           => 'field_starter_home_section_4_post_desc',
							'label'         => __( 'Desc', 'starter-theme' ),
							'name'          => 'desc',
							'type'          => 'textarea',
							'rows'          => 3,
							'new_lines'     => '',
							'default_value' => 'Privatna radionica za timove i opusteno druzenje uz slikanje.',
						),
						array(
							'key'           => 'field_starter_home_section_4_post_url',
							'label'         => __( 'Link', 'starter-theme' ),
							'name'          => 'url',
							'type'          => 'url',
							'instructions'  => __( 'Optional. Leave empty if this card should not have a button.', 'starter-theme' ),
						),
					),
				),
				array(
					'key'               => 'field_starter_home_form_tab',
					'label'             => __( 'Form', 'starter-theme' ),
					'name'              => '',
					'type'              => 'tab',
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_starter_home_form_title',
					'label'             => __( 'Form Title', 'starter-theme' ),
					'name'              => 'form_title',
					'type'              => 'text',
					'default_value'     => $defaults['form_title'],
				),
				array(
					'key'               => 'field_starter_home_form_desc',
					'label'             => __( 'Form Desc', 'starter-theme' ),
					'name'              => 'form_desc',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['form_desc'],
				),
				array(
					'key'               => 'field_starter_home_form_desc2',
					'label'             => __( 'Form Desc2', 'starter-theme' ),
					'name'              => 'form_desc2',
					'type'              => 'wysiwyg',
					'tabs'              => 'all',
					'toolbar'           => 'basic',
					'media_upload'      => 0,
					'delay'             => 0,
					'default_value'     => $defaults['form_desc2'],
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/home.php',
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
add_action( 'acf/init', 'starter_register_home_acf_fields' );
