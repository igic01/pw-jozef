<?php
/**
 * ACF field definitions for the Privatne Radionice page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'STARTER_PRIVATE_WORKSHOPS_PLACEHOLDER_IMAGE' ) ) {
	define( 'STARTER_PRIVATE_WORKSHOPS_PLACEHOLDER_IMAGE', 'http://pw-2.local/wp-content/uploads/2026/07/placeholder.webp' );
}

function starter_private_workshops_acf_placeholder_image_value() {
	static $placeholder = null;

	if ( null !== $placeholder ) {
		return $placeholder;
	}

	$placeholder = STARTER_PRIVATE_WORKSHOPS_PLACEHOLDER_IMAGE;

	if ( function_exists( 'attachment_url_to_postid' ) ) {
		$attachment_id = attachment_url_to_postid( STARTER_PRIVATE_WORKSHOPS_PLACEHOLDER_IMAGE );

		if ( $attachment_id ) {
			$placeholder = $attachment_id;
		}
	}

	return $placeholder;
}

function starter_private_workshops_acf_default_values() {
	$image = starter_private_workshops_acf_placeholder_image_value();

	return array(
		'hero_title'      => 'Zakažite <span class="v4p-accent">Privatnu</span> Radionicu!',
		'hero_desc'       => "Bilo da ste HR u firmi kojem treba nova zanimacija za zaposlene, kuma koja organizuje djevojačko veče, turistička organizacija koja želi da impresionira goste, ili slično, mi smo tu za vas!\n\nRadionicu možete zakazati u našem ateljeu, ali i bilo gdje u Crnoj Gori, mi dolazimo na lokaciju.",
		'hero_img'        => $image,
		'section_1_title' => 'Vrste Radionica',
		'section_1_desc'  => 'Birajte format koji najbolje odgovara vašem događaju. Svaka radionica može da se organizuje u našem ateljeu ili na lokaciji koju vi odaberete.',
		'section_1_posts' => array(
			array(
				'img'   => $image,
				'title' => 'Klasična P&W',
				'desc'  => 'Korak po korak do savršene slike uz vino, opuštenu atmosferu i vođenje instruktorke.',
			),
			array(
				'img'   => $image,
				'title' => 'Neon P&C',
				'desc'  => 'Večernji format sa UV bojama, koktel atmosferom i dinamičnim vizuelnim efektom.',
			),
			array(
				'img'   => $image,
				'title' => 'Paint & Kids',
				'desc'  => 'Kreativan format za rođendane, porodična okupljanja i razigrane privatne proslave.',
			),
			array(
				'img'   => $image,
				'title' => 'Radionica Po Mjeri',
				'desc'  => 'Temu, trajanje i atmosferu prilagođavamo vašem timu, gostima i lokaciji događaja.',
			),
		),
		'images'          => array_fill( 0, 8, $image ),
		'form_title'      => 'Formular sa osnovnim informacijama za rezervisanje',
		'form_desc'       => 'Pošaljite nam osnovne informacije o događaju i javićemo vam se sa prijedlogom termina, ponudom i svim narednim koracima.',
	);
}

function starter_private_workshops_acf_default_value( $name, $default = '' ) {
	if ( 'section_1_posts' === $name || 'images' === $name ) {
		return array();
	}

	$defaults = starter_private_workshops_acf_default_values();

	return array_key_exists( $name, $defaults ) ? $defaults[ $name ] : $default;
}

function starter_private_workshops_acf_load_default_value( $value, $post_id, $field ) {
	if ( empty( $field['key'] ) || 0 !== strpos( $field['key'], 'field_starter_private_workshops_' ) ) {
		return $value;
	}

	if ( empty( $field['name'] ) ) {
		return $value;
	}

	if ( 'section_1_posts' === $field['name'] || 'images' === $field['name'] ) {
		return is_array( $value ) ? $value : array();
	}

	if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
		return $value;
	}

	return starter_private_workshops_acf_default_value( $field['name'], $value );
}
add_filter( 'acf/load_value', 'starter_private_workshops_acf_load_default_value', 10, 3 );

function starter_private_workshops_acf_gallery_media_query( $args, $field, $post_id ) {
	$args['post_status']    = 'inherit';
	$args['post_mime_type'] = array( 'image', 'video' );

	return $args;
}
add_filter( 'acf/fields/relationship/query/key=field_starter_private_workshops_images', 'starter_private_workshops_acf_gallery_media_query', 10, 3 );

function starter_register_private_workshops_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$defaults = starter_private_workshops_acf_default_values();
	$image    = starter_private_workshops_acf_placeholder_image_value();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_starter_private_workshops',
			'title'                 => __( 'Privatne Radionice Page Content', 'starter-theme' ),
			'fields'                => array(
				array(
					'key'       => 'field_starter_private_workshops_hero_tab',
					'label'     => __( 'Hero', 'starter-theme' ),
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				),
				array(
					'key'           => 'field_starter_private_workshops_hero_title',
					'label'         => __( 'Hero Title', 'starter-theme' ),
					'name'          => 'hero_title',
					'type'          => 'text',
					'default_value' => $defaults['hero_title'],
				),
				array(
					'key'           => 'field_starter_private_workshops_hero_desc',
					'label'         => __( 'Hero Desc', 'starter-theme' ),
					'name'          => 'hero_desc',
					'type'          => 'wysiwyg',
					'tabs'          => 'all',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'delay'         => 0,
					'default_value' => $defaults['hero_desc'],
				),
				array(
					'key'           => 'field_starter_private_workshops_hero_img',
					'label'         => __( 'Hero Img', 'starter-theme' ),
					'name'          => 'hero_img',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'default_value' => $defaults['hero_img'],
				),
				array(
					'key'       => 'field_starter_private_workshops_section_1_tab',
					'label'     => __( 'Section 1', 'starter-theme' ),
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				),
				array(
					'key'           => 'field_starter_private_workshops_section_1_title',
					'label'         => __( 'Section 1 Title', 'starter-theme' ),
					'name'          => 'section_1_title',
					'type'          => 'text',
					'default_value' => $defaults['section_1_title'],
				),
				array(
					'key'           => 'field_starter_private_workshops_section_1_desc',
					'label'         => __( 'Section 1 Desc', 'starter-theme' ),
					'name'          => 'section_1_desc',
					'type'          => 'wysiwyg',
					'tabs'          => 'all',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'delay'         => 0,
					'default_value' => $defaults['section_1_desc'],
				),
				array(
					'key'           => 'field_starter_private_workshops_section_1_posts',
					'label'         => __( 'Section 1 Posts', 'starter-theme' ),
					'name'          => 'section_1_posts',
					'type'          => 'relationship',
					'instructions'  => __( 'Select the posts to display in this section. The selected order controls the frontend order.', 'starter-theme' ),
					'post_type'     => array( 'post' ),
					'taxonomy'      => '',
					'filters'       => array( 'search', 'post_type', 'taxonomy' ),
					'elements'      => array( 'featured_image' ),
					'return_format' => 'object',
					'min'           => 0,
					'max'           => 0,
				),
				array(
					'key'       => 'field_starter_private_workshops_gallery_tab',
					'label'     => __( 'Gallery', 'starter-theme' ),
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				),
				array(
					'key'           => 'field_starter_private_workshops_images',
					'label'         => __( 'Gallery Media', 'starter-theme' ),
					'name'          => 'images',
					'type'          => 'relationship',
					'instructions'  => __( 'Select image and video attachments to display in the gallery. The selected order controls the frontend order.', 'starter-theme' ),
					'post_type'     => array( 'attachment' ),
					'post_status'   => array( 'inherit' ),
					'taxonomy'      => '',
					'filters'       => array( 'search' ),
					'elements'      => array( 'featured_image' ),
					'return_format' => 'object',
					'min'           => 0,
					'max'           => 0,
				),
				array(
					'key'       => 'field_starter_private_workshops_form_tab',
					'label'     => __( 'Form', 'starter-theme' ),
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				),
				array(
					'key'           => 'field_starter_private_workshops_form_title',
					'label'         => __( 'Form Title', 'starter-theme' ),
					'name'          => 'form_title',
					'type'          => 'text',
					'default_value' => $defaults['form_title'],
				),
				array(
					'key'           => 'field_starter_private_workshops_form_desc',
					'label'         => __( 'Form Desc', 'starter-theme' ),
					'name'          => 'form_desc',
					'type'          => 'wysiwyg',
					'tabs'          => 'all',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'delay'         => 0,
					'default_value' => $defaults['form_desc'],
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/privatne-radionice.php',
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
add_action( 'acf/init', 'starter_register_private_workshops_acf_fields' );
