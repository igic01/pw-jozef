<?php
/**
 * Template Name: Privatne Radionice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'starter_private_workshops_field' ) ) {
	function starter_private_workshops_field( $name, $default = '' ) {
		if ( function_exists( 'get_field' ) ) {
			$value = get_field( $name );

			if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
				return $value;
			}
		}

		if ( function_exists( 'starter_private_workshops_acf_default_value' ) ) {
			return starter_private_workshops_acf_default_value( $name, $default );
		}

		return $default;
	}
}

if ( ! function_exists( 'starter_private_workshops_text' ) ) {
	function starter_private_workshops_text( $value ) {
		if ( '' === $value || null === $value ) {
			return '';
		}

		return wpautop( wp_kses_post( $value ) );
	}
}

if ( ! function_exists( 'starter_private_workshops_image_url' ) ) {
	function starter_private_workshops_image_url( $image, $size = 'large' ) {
		if ( empty( $image ) ) {
			return '';
		}

		if ( is_numeric( $image ) ) {
			$src = wp_get_attachment_image_src( (int) $image, $size );
			return $src ? $src[0] : '';
		}

		if ( is_array( $image ) ) {
			if ( ! empty( $image['sizes'][ $size ] ) ) {
				return $image['sizes'][ $size ];
			}

			if ( ! empty( $image['url'] ) ) {
				return $image['url'];
			}

			if ( ! empty( $image['ID'] ) || ! empty( $image['id'] ) ) {
				return starter_private_workshops_image_url( ! empty( $image['ID'] ) ? $image['ID'] : $image['id'], $size );
			}
		}

		return is_string( $image ) ? $image : '';
	}
}

if ( ! function_exists( 'starter_private_workshops_image_alt' ) ) {
	function starter_private_workshops_image_alt( $image, $fallback = '' ) {
		if ( is_array( $image ) && ! empty( $image['alt'] ) ) {
			return $image['alt'];
		}

		if ( is_numeric( $image ) ) {
			$alt = get_post_meta( (int) $image, '_wp_attachment_image_alt', true );
			return $alt ? $alt : $fallback;
		}

		return $fallback;
	}
}

if ( ! function_exists( 'starter_private_workshops_render_image' ) ) {
	function starter_private_workshops_render_image( $image, $class = '', $size = 'large', $fallback_alt = '' ) {
		if ( empty( $image ) ) {
			return;
		}

		if ( is_numeric( $image ) ) {
			echo wp_get_attachment_image(
				(int) $image,
				$size,
				false,
				array(
					'class'   => $class,
					'loading' => 'lazy',
				)
			);
			return;
		}

		$url = starter_private_workshops_image_url( $image, $size );

		if ( ! $url ) {
			return;
		}

		printf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy">',
			esc_attr( $class ),
			esc_url( $url ),
			esc_attr( starter_private_workshops_image_alt( $image, $fallback_alt ) )
		);
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_form_shortcode' ) ) {
	function starter_private_workshops_fluent_form_shortcode() {
		if ( ! shortcode_exists( 'fluentform' ) ) {
			return '';
		}

		$form_id = starter_private_workshops_fluent_form_id();

		return $form_id ? sprintf( '[fluentform id="%d"]', $form_id ) : '';
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_form_id' ) ) {
	function starter_private_workshops_fluent_form_id() {
		global $wpdb;

		$forms_table = $wpdb->prefix . 'fluentform_forms';
		$meta_table  = $wpdb->prefix . 'fluentform_form_meta';
		$option_name = 'starter_private_workshops_fluent_form_id';
		$form_title  = 'Private Workshop Reservation Form';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $forms_table ) ) !== $forms_table ) {
			return 0;
		}

		$form_id = absint( get_option( $option_name ) );

		if ( $form_id && starter_private_workshops_fluent_form_exists( $form_id, $forms_table ) ) {
			starter_private_workshops_maybe_update_fluent_form( $form_id, $forms_table, $form_title );
			return $form_id;
		}

		$form_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$forms_table} WHERE title = %s LIMIT 1",
				$form_title
			)
		);

		if ( $form_id ) {
			update_option( $option_name, $form_id, false );
			starter_private_workshops_maybe_update_fluent_form( $form_id, $forms_table, $form_title );
			return $form_id;
		}

		$form_fields = starter_private_workshops_fluent_form_fields();

		$inserted = $wpdb->insert(
			$forms_table,
			array(
				'title'       => $form_title,
				'status'      => 'published',
				'form_fields' => wp_json_encode( $form_fields ),
				'has_payment' => 0,
				'type'        => 'form',
				'created_by'  => get_current_user_id(),
				'created_at'  => current_time( 'mysql' ),
				'updated_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		$form_id = (int) $wpdb->insert_id;

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $meta_table ) ) === $meta_table ) {
			$form_settings = array(
				'confirmation' => array(
					'redirectTo'           => 'samePage',
					'messageToShow'        => 'Hvala. Vaš zahtjev za privatnu radionicu je poslat.',
					'customPage'           => null,
					'samePageFormBehavior' => 'hide_form',
					'customUrl'            => null,
				),
				'restrictions' => array(
					'limitNumberOfEntries' => array(
						'enabled'        => false,
						'numberOfEntries' => null,
						'period'         => 'total',
						'limitReachedMsg' => 'Maximum number of entries exceeded.',
					),
					'scheduleForm'         => array(
						'enabled'      => false,
						'start'        => null,
						'end'          => null,
						'pendingMsg'   => 'Form submission is not started yet.',
						'expiredMsg'   => 'Form submission is now closed.',
						'selectedDays' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
					),
					'requireLogin'         => array(
						'enabled'         => false,
						'requireLoginMsg' => 'You must be logged in to submit the form.',
					),
					'denyEmptySubmission'  => array(
						'enabled' => false,
						'message' => 'Sorry, you cannot submit an empty form.',
					),
				),
				'layout'       => array(
					'labelPlacement'        => 'top',
					'helpMessagePlacement'  => 'with_label',
					'errorMessagePlacement' => 'inline',
					'cssClassName'          => '',
					'asteriskPlacement'     => 'asterisk-right',
				),
			);

			$wpdb->insert(
				$meta_table,
				array(
					'form_id'  => $form_id,
					'meta_key' => 'formSettings',
					'value'    => wp_json_encode( $form_settings ),
				),
				array( '%d', '%s', '%s' )
			);

			$wpdb->insert(
				$meta_table,
				array(
					'form_id'  => $form_id,
					'meta_key' => 'template_name',
					'value'    => 'starter_private_workshops_reservation',
				),
				array( '%d', '%s', '%s' )
			);
		}

		update_option( $option_name, $form_id, false );
		update_option( 'starter_private_workshops_fluent_form_version', 2, false );

		return $form_id;
	}
}

if ( ! function_exists( 'starter_private_workshops_maybe_update_fluent_form' ) ) {
	function starter_private_workshops_maybe_update_fluent_form( $form_id, $forms_table, $form_title ) {
		global $wpdb;

		if ( absint( get_option( 'starter_private_workshops_fluent_form_version' ) ) >= 2 ) {
			return;
		}

		$current_title = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT title FROM {$forms_table} WHERE id = %d LIMIT 1",
				$form_id
			)
		);

		if ( $current_title !== $form_title ) {
			return;
		}

		$wpdb->update(
			$forms_table,
			array(
				'form_fields' => wp_json_encode( starter_private_workshops_fluent_form_fields() ),
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => $form_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		update_option( 'starter_private_workshops_fluent_form_version', 2, false );
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_form_exists' ) ) {
	function starter_private_workshops_fluent_form_exists( $form_id, $forms_table ) {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$forms_table} WHERE id = %d LIMIT 1",
				$form_id
			)
		);
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_required' ) ) {
	function starter_private_workshops_fluent_required() {
		return array(
			'required' => array(
				'value'          => true,
				'message'        => 'Ovo polje je obavezno',
				'global_message' => 'Ovo polje je obavezno',
				'global'         => true,
			),
		);
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_conditional' ) ) {
	function starter_private_workshops_fluent_conditional() {
		return array(
			'type'       => 'any',
			'status'     => false,
			'conditions' => array(
				array(
					'field'    => '',
					'value'    => '',
					'operator' => '',
				),
			),
		);
	}
}

if ( ! function_exists( 'starter_private_workshops_fluent_form_fields' ) ) {
	function starter_private_workshops_fluent_form_fields() {
		$conditional = starter_private_workshops_fluent_conditional();
		$required    = starter_private_workshops_fluent_required();

		return array(
			'fields'       => array(
				array(
					'index'          => 0,
					'element'        => 'select',
					'attributes'     => array(
						'name'  => 'tip_radionice',
						'value' => '',
						'class' => '',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Tip radionice',
						'admin_field_label'  => 'Tip radionice',
						'label_placement'    => '',
						'help_message'       => '',
						'placeholder'        => 'Odaberite tip radionice',
						'advanced_options'   => array(
							array( 'label' => 'Klasična P&W', 'value' => 'Klasična P&W' ),
							array( 'label' => 'Neon P&C', 'value' => 'Neon P&C' ),
							array( 'label' => 'Paint & Kids', 'value' => 'Paint & Kids' ),
							array( 'label' => 'Radionica po mjeri', 'value' => 'Radionica po mjeri' ),
						),
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Dropdown',
						'icon_class' => 'ff-edit-dropdown',
						'template'   => 'select',
					),
					'uniqElKey'     => 'starter_private_workshops_type',
				),
				array(
					'index'          => 1,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'zeljeni_datum',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'dd/mm/yyyy',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Željeni datum',
						'admin_field_label'  => 'Željeni datum',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_date',
				),
				array(
					'index'          => 2,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'zeljena_lokacija',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Adresa',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Željena lokacija',
						'admin_field_label'  => 'Željena lokacija',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_location',
				),
				array(
					'index'          => 3,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'broj_osoba',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Unesite broj učesnika',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Broj osoba',
						'admin_field_label'  => 'Broj osoba',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_people',
				),
				array(
					'index'          => 4,
					'element'        => 'select',
					'attributes'     => array(
						'name'  => 'vino_dane',
						'value' => '',
						'class' => '',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Vino - Da/Ne',
						'admin_field_label'  => 'Vino - Da/Ne',
						'label_placement'    => '',
						'help_message'       => '',
						'placeholder'        => 'Da',
						'advanced_options'   => array(
							array( 'label' => 'Da', 'value' => 'Da' ),
							array( 'label' => 'Ne', 'value' => 'Ne' ),
						),
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Dropdown',
						'icon_class' => 'ff-edit-dropdown',
						'template'   => 'select',
					),
					'uniqElKey'     => 'starter_private_workshops_wine',
				),
				array(
					'index'          => 5,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'pocetak_radionice',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Eg. 08',
					),
					'settings'       => array(
						'container_class'    => 'v4p-time-field v4p-time-hour',
						'label'              => 'Početak radionice',
						'admin_field_label'  => 'Početak radionice',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_time',
				),
				array(
					'index'          => 6,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'minuta',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Eg. 00',
					),
					'settings'       => array(
						'container_class'    => 'v4p-time-field v4p-time-minute',
						'label'              => 'Minuta',
						'admin_field_label'  => 'Minuta',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => array(),
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_minute',
				),
				array(
					'index'          => 7,
					'element'        => 'select',
					'attributes'     => array(
						'name'  => 'am_pm',
						'value' => '',
						'class' => '',
					),
					'settings'       => array(
						'container_class'    => 'v4p-time-field v4p-time-period',
						'label'              => 'AM/PM',
						'admin_field_label'  => 'AM/PM',
						'label_placement'    => '',
						'help_message'       => '',
						'placeholder'        => 'AM',
						'advanced_options'   => array(
							array( 'label' => 'AM', 'value' => 'AM' ),
							array( 'label' => 'PM', 'value' => 'PM' ),
						),
						'validation_rules'   => array(),
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Dropdown',
						'icon_class' => 'ff-edit-dropdown',
						'template'   => 'select',
					),
					'uniqElKey'     => 'starter_private_workshops_period',
				),
				array(
					'index'          => 8,
					'element'        => 'input_text',
					'attributes'     => array(
						'name'        => 'kontakt_osoba',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Ime i prezime',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Kontakt osoba',
						'admin_field_label'  => 'Kontakt osoba',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $required,
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Input Text',
						'icon_class' => 'ff-edit-text',
						'template'   => 'inputText',
					),
					'uniqElKey'     => 'starter_private_workshops_contact',
				),
				array(
					'index'          => 9,
					'element'        => 'textarea',
					'attributes'     => array(
						'name'        => 'dodatna_pitanja',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Napišite sve što je važno za vaš događaj',
						'rows'        => 6,
						'cols'        => 2,
						'maxlength'   => '',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Dodatna pitanja',
						'admin_field_label'  => 'Dodatna pitanja',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => array(),
						'conditional_logics' => $conditional,
					),
					'editor_options' => array(
						'title'      => 'Text Area',
						'icon_class' => 'ff-edit-textarea',
						'template'   => 'inputTextarea',
					),
					'uniqElKey'     => 'starter_private_workshops_questions',
				),
			),
			'submitButton' => array(
				'uniqElKey'      => 'starter_private_workshops_submit',
				'element'        => 'button',
				'attributes'     => array(
					'type'  => 'submit',
					'class' => '',
				),
				'settings'       => array(
					'align'            => 'left',
					'button_style'     => 'default',
					'container_class'  => '',
					'help_message'     => '',
					'background_color' => '#bf2020',
					'button_size'      => 'sm',
					'color'            => '#ffffff',
					'button_ui'        => array(
						'type'    => 'default',
						'text'    => 'Pošaljite rezervaciju',
						'img_url' => '',
					),
				),
				'editor_options' => array(
					'title' => 'Submit Button',
				),
			),
		);
	}
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
while ( have_posts() ) :
	the_post();

	$hero_title     = starter_private_workshops_field( 'hero_title', get_the_title() );
	$hero_desc      = starter_private_workshops_field( 'hero_desc' );
	$hero_img       = starter_private_workshops_field( 'hero_img' );
	$section_title  = starter_private_workshops_field( 'section_1_title' );
	$section_desc   = starter_private_workshops_field( 'section_1_desc' );
	$section_posts  = starter_private_workshops_field( 'section_1_posts', array() );
	$gallery_images = starter_private_workshops_field( 'images', array() );
	$form_title     = starter_private_workshops_field( 'form_title' );
	$form_desc      = starter_private_workshops_field( 'form_desc' );
	$form_shortcode = trim( starter_private_workshops_field( 'form_shortcode', '' ) );

	if ( empty( $gallery_images ) ) {
		$gallery_images = array();
	} elseif ( ! is_array( $gallery_images ) || isset( $gallery_images['url'] ) || isset( $gallery_images['ID'] ) || isset( $gallery_images['id'] ) ) {
		$gallery_images = array( $gallery_images );
	}
	?>

	<main class="site-main v4p-page private-workshops-template">
		<section class="v4p-hero" aria-label="<?php esc_attr_e( 'Privatne radionice hero', 'starter-theme' ); ?>">
			<?php if ( $hero_img ) : ?>
				<div class="v4p-hero-media">
					<?php starter_private_workshops_render_image( $hero_img, '', 'full', __( 'Privatna Paint and Wine radionica', 'starter-theme' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="v4p-hero-content">
				<div class="v4p-hero-shell">
					<p class="v4p-eyebrow"><?php esc_html_e( 'Privatne Radionice', 'starter-theme' ); ?></p>
					<h1 class="v4p-title"><?php echo wp_kses_post( $hero_title ); ?></h1>

					<?php if ( $hero_desc ) : ?>
						<div class="v4p-lead">
							<?php echo starter_private_workshops_text( $hero_desc ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php
		get_template_part(
			'template-parts/private-workshops-posts',
			null,
			array(
				'title'      => $section_title,
				'desc'       => $section_desc,
				'posts'      => $section_posts,
				'section_id' => 'v4p-types',
			)
		);
		?>
		<?php
		get_template_part(
			'template-parts/private-workshops-gallery',
			null,
			array(
				'title'      => __( 'Galerija', 'starter-theme' ),
				'media'      => $gallery_images,
				'section_id' => 'v4p-gallery',
			)
		);
		?>

		<section class="v4p-frame" aria-labelledby="v4p-form-title">
			<div class="v4p-inner">
				<div class="v4p-form-layout">
					<div class="v4p-form-copy">
						<?php if ( $form_title ) : ?>
							<h2 id="v4p-form-title"><?php echo esc_html( $form_title ); ?></h2>
						<?php endif; ?>

						<?php if ( $form_desc ) : ?>
							<div class="v4p-form-note">
								<?php echo starter_private_workshops_text( $form_desc ); ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="v4p-form">
						<?php
						if ( '' === $form_shortcode ) {
							$form_shortcode = starter_private_workshops_fluent_form_shortcode();
						}

						if ( $form_shortcode && shortcode_exists( 'fluentform' ) ) {
							echo do_shortcode( $form_shortcode );
						}
						?>
					</div>
				</div>
			</div>
		</section>
	</main>

	<?php
endwhile;
?>

<?php wp_footer(); ?>
</body>
</html>
