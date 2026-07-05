<?php
/**
 * Template Name: Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'starter_home_field' ) ) {
	function starter_home_field( $name, $default = '' ) {
		if ( function_exists( 'get_field' ) ) {
			$value = get_field( $name );

			if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
				return $value;
			}
		}

		if ( function_exists( 'starter_home_acf_default_value' ) ) {
			return starter_home_acf_default_value( $name, $default );
		}

		return $default;
	}
}

if ( ! function_exists( 'starter_home_text' ) ) {
	function starter_home_text( $value ) {
		if ( '' === $value || null === $value ) {
			return '';
		}

		return wpautop( wp_kses_post( $value ) );
	}
}

if ( ! function_exists( 'starter_home_images' ) ) {
	function starter_home_images( $field, $limit = 0 ) {
		$images = starter_home_field( $field, array() );

		if ( empty( $images ) ) {
			return array();
		}

		if ( ! is_array( $images ) || isset( $images['url'] ) || isset( $images['ID'] ) || isset( $images['id'] ) ) {
			$images = array( $images );
		}

		$images = array_values( array_filter( $images ) );

		if ( $limit > 0 ) {
			$images = array_slice( $images, 0, $limit );
		}

		return $images;
	}
}

if ( ! function_exists( 'starter_home_image_url' ) ) {
	function starter_home_image_url( $image, $size = 'large' ) {
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
				return starter_home_image_url( ! empty( $image['ID'] ) ? $image['ID'] : $image['id'], $size );
			}
		}

		return is_string( $image ) ? $image : '';
	}
}

if ( ! function_exists( 'starter_home_image_alt' ) ) {
	function starter_home_image_alt( $image, $fallback = '' ) {
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

if ( ! function_exists( 'starter_home_render_image' ) ) {
	function starter_home_render_image( $image, $class = '', $size = 'large', $fallback_alt = '' ) {
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

		$url = starter_home_image_url( $image, $size );

		if ( ! $url ) {
			return;
		}

		printf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy">',
			esc_attr( $class ),
			esc_url( $url ),
			esc_attr( starter_home_image_alt( $image, $fallback_alt ) )
		);
	}
}

if ( ! function_exists( 'starter_home_posts' ) ) {
	function starter_home_posts() {
		$items = starter_home_field( 'section_4_posts', array() );
		$default_image = function_exists( 'starter_home_acf_default_value' ) ? starter_home_acf_default_value( 'section_3_hero' ) : '';

		if ( empty( $items ) || ! is_array( $items ) ) {
			return array();
		}

		$posts = array();

		foreach ( $items as $item ) {
			if ( $item instanceof WP_Post || is_numeric( $item ) ) {
				$post = $item instanceof WP_Post ? $item : get_post( (int) $item );

				if ( ! $post ) {
					continue;
				}

				$posts[] = array(
					'image' => get_post_thumbnail_id( $post ) ?: $default_image,
					'title' => get_the_title( $post ),
					'desc'  => get_the_excerpt( $post ),
					'url'   => get_permalink( $post ),
				);
				continue;
			}

			if ( is_array( $item ) ) {
				$item = $item['post'] ?? $item;

				if ( $item instanceof WP_Post || is_numeric( $item ) ) {
					$post = $item instanceof WP_Post ? $item : get_post( (int) $item );

					if ( ! $post ) {
						continue;
					}

					$posts[] = array(
						'image' => get_post_thumbnail_id( $post ) ?: $default_image,
						'title' => get_the_title( $post ),
						'desc'  => get_the_excerpt( $post ),
						'url'   => get_permalink( $post ),
					);
					continue;
				}

				if ( ! is_array( $item ) ) {
					continue;
				}

				$url = $item['url'] ?? $item['link'] ?? '';

				if ( is_array( $url ) ) {
					$url = $url['url'] ?? '';
				}

				$image = $item['img'] ?? $item['image'] ?? $item['post_img'] ?? '';
				$image = $image ?: $default_image;

				$posts[] = array(
					'image' => $image,
					'title' => $item['title'] ?? $item['post_title'] ?? '',
					'desc'  => $item['desc'] ?? $item['description'] ?? $item['post_desc'] ?? '',
					'url'   => $url,
				);
			}
		}

		return $posts;
	}
}

if ( ! function_exists( 'starter_home_fluent_form_shortcode' ) ) {
	function starter_home_fluent_form_shortcode() {
		if ( ! shortcode_exists( 'fluentform' ) ) {
			return '';
		}

		$form_id = starter_home_fluent_form_id();

		return $form_id ? sprintf( '[fluentform id="%d"]', $form_id ) : '';
	}
}

if ( ! function_exists( 'starter_home_fluent_form_id' ) ) {
	function starter_home_fluent_form_id() {
		global $wpdb;

		$forms_table = $wpdb->prefix . 'fluentform_forms';
		$meta_table  = $wpdb->prefix . 'fluentform_form_meta';
		$option_name = 'starter_home_fluent_form_id';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $forms_table ) ) !== $forms_table ) {
			return 0;
		}

		$form_id = absint( get_option( $option_name ) );

		if ( $form_id && starter_home_fluent_form_exists( $form_id, $forms_table ) ) {
			return $form_id;
		}

		$form_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$forms_table} WHERE title = %s LIMIT 1",
				'Home Message Form'
			)
		);

		if ( $form_id ) {
			update_option( $option_name, $form_id, false );
			return $form_id;
		}

		$form_fields = array(
			'fields'       => array(
				array(
					'index'          => 0,
					'element'        => 'textarea',
					'attributes'     => array(
						'name'        => 'message',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Ostavite nam poruku',
						'rows'        => 6,
						'cols'        => 2,
						'maxlength'   => '',
					),
					'settings'       => array(
						'container_class'   => '',
						'label'             => 'Poruka',
						'admin_field_label' => 'Poruka',
						'label_placement'   => 'hide_label',
						'help_message'      => '',
						'prefix_label'      => '',
						'suffix_label'      => '',
						'validation_rules'  => array(
							'required' => array(
								'value'          => true,
								'message'        => 'This field is required',
								'global_message' => 'This field is required',
								'global'         => true,
							),
						),
						'conditional_logics' => array(
							'type'       => 'any',
							'status'     => false,
							'conditions' => array(
								array(
									'field'    => '',
									'value'    => '',
									'operator' => '',
								),
							),
						),
					),
					'editor_options' => array(
						'title'      => 'Text Area',
						'icon_class' => 'ff-edit-textarea',
						'template'   => 'inputTextarea',
					),
					'uniqElKey'     => 'starter_home_message',
				),
			),
			'submitButton' => array(
				'uniqElKey'      => 'starter_home_submit',
				'element'        => 'button',
				'attributes'     => array(
					'type'  => 'submit',
					'class' => '',
				),
				'settings'       => array(
					'align'            => 'center',
					'button_style'     => 'default',
					'container_class'  => '',
					'help_message'     => '',
					'background_color' => '#bf2020',
					'button_size'      => 'md',
					'color'            => '#ffffff',
					'button_ui'        => array(
						'type'    => 'default',
						'text'    => 'Posalji',
						'img_url' => '',
					),
				),
				'editor_options' => array(
					'title' => 'Submit Button',
				),
			),
		);

		$inserted = $wpdb->insert(
			$forms_table,
			array(
				'title'       => 'Home Message Form',
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
					'messageToShow'        => 'Hvala na poruci. Kontaktiracemo vas uskoro.',
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
					'value'    => 'starter_home_message',
				),
				array( '%d', '%s', '%s' )
			);
		}

		update_option( $option_name, $form_id, false );

		return $form_id;
	}
}

if ( ! function_exists( 'starter_home_fluent_form_exists' ) ) {
	function starter_home_fluent_form_exists( $form_id, $forms_table ) {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$forms_table} WHERE id = %d LIMIT 1",
				$form_id
			)
		);
	}
}

if ( ! function_exists( 'starter_home_render_products' ) ) {
	function starter_home_render_products() {
		if ( ! function_exists( 'starter_get_upcoming_products' ) ) {
			return;
		}

		$products = starter_get_upcoming_products(
			array(
				'exclude_category' => 'pw-shop',
			)
		);

		if ( empty( $products ) ) {
			echo '<p class="home-products__empty">' . esc_html__( 'Trenutno nema proizvoda za prikaz.', 'starter-theme' ) . '</p>';
			return;
		}

		$filter_terms = array();

		foreach ( $products as $product ) {
			foreach ( $product['categories'] as $category ) {
				if ( empty( $category['slug'] ) || empty( $category['name'] ) ) {
					continue;
				}

				$filter_terms[ $category['slug'] ] = $category['name'];
			}
		}

		asort( $filter_terms, SORT_NATURAL | SORT_FLAG_CASE );
		?>
		<div class="v5e-schedule" data-home-products data-home-default-filter="all">
			<div class="v5e-shell">
				<div class="v5e-head">
					<h2 class="v5e-title"><?php esc_html_e( 'Mjesečni Raspored', 'starter-theme' ); ?></h2>
					<p class="v5e-subtitle"><?php esc_html_e( 'Ne brinite, ne treba vam nikakvo iskustvo.', 'starter-theme' ); ?></p>
				</div>

				<?php if ( count( $filter_terms ) > 1 ) : ?>
					<div class="v5e-controls" role="tablist" aria-label="<?php esc_attr_e( 'Kategorije proizvoda', 'starter-theme' ); ?>">
						<label class="v5e-category-select-wrap">
							<span><?php esc_html_e( 'Kategorija', 'starter-theme' ); ?></span>
							<select class="v5e-category-select" data-home-product-select>
								<option value="all"><?php esc_html_e( 'Sve', 'starter-theme' ); ?></option>
								<?php foreach ( $filter_terms as $slug => $name ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>

						<button class="v5e-filter is-active" type="button" data-home-product-filter="all"><?php esc_html_e( 'Sve', 'starter-theme' ); ?></button>
						<?php foreach ( $filter_terms as $slug => $name ) : ?>
							<button class="v5e-filter" type="button" data-home-product-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="v5e-grid">
					<?php foreach ( $products as $product ) : ?>
						<article class="v5e-card" data-home-product-categories="<?php echo esc_attr( implode( ',', $product['category_slugs'] ) ); ?>">
							<?php if ( $product['display_date'] ) : ?>
								<div class="v5e-date"><?php echo esc_html( $product['display_date'] ); ?></div>
							<?php endif; ?>

							<?php if ( $product['category'] ) : ?>
								<div class="v5e-meta"><?php echo esc_html( $product['category'] ); ?></div>
							<?php endif; ?>

							<a class="v5e-image" href="<?php echo esc_url( $product['link'] ); ?>" aria-label="<?php echo esc_attr( $product['name'] ); ?>">
								<?php echo wp_kses_post( $product['image_html'] ); ?>
							</a>

							<h3 class="v5e-name"><?php echo esc_html( $product['name'] ); ?></h3>

							<div class="v5e-priceRow">
								<div class="v5e-price"><?php echo wp_kses_post( $product['price_html'] ); ?></div>
								<?php if ( $product['difficulty_label'] ) : ?>
									<div class="v5e-accent"><?php echo esc_html( $product['difficulty_label'] ); ?></div>
								<?php endif; ?>
							</div>

							<div class="v5e-buy">
								<a class="v5e-button" href="<?php echo esc_url( $product['link'] ); ?>"><?php esc_html_e( 'Rezerviši', 'starter-theme' ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

get_header();

while ( have_posts() ) :
	the_post();

	$hero_images      = starter_home_images( 'hero_images', 3 );
	$section_1_images = starter_home_images( 'section_1_images', 4 );
	$section_3_images = starter_home_images( 'section_3_images', 3 );
	$section_3_hero   = starter_home_field( 'section_3_hero' );
	$section_3_style  = starter_home_image_url( $section_3_hero, 'full' );
	$form_bg          = $section_3_style ? sprintf( ' style="%s"', esc_attr( '--home-form-bg: url(' . esc_url_raw( $section_3_style ) . ');' ) ) : '';
	$section_4_posts  = starter_home_posts();
	?>

	<main class="site-main home-template">
		<section class="home-hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>" data-home-carousel>
			<?php if ( $hero_images ) : ?>
				<div class="home-hero__stage">
					<?php foreach ( $hero_images as $index => $image ) : ?>
						<figure class="home-hero__slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
							<?php starter_home_render_image( $image, 'home-hero__image', 'full', sprintf( 'Hero image %d', $index + 1 ) ); ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="home-hero__content">
				<div class="home-hero__copy">
					<h1 class="home-hero__title">
						<?php echo wp_kses_post( starter_home_field( 'hero_title', get_the_title() ) ); ?>
					</h1>

					<?php if ( starter_home_field( 'hero_desc' ) ) : ?>
						<div class="home-hero__text">
							<?php echo starter_home_text( starter_home_field( 'hero_desc' ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( count( $hero_images ) > 1 ) : ?>
				<div class="home-carousel-controls" aria-label="Hero slideshow controls">
					<button class="home-carousel-button" type="button" data-carousel-prev aria-label="Previous slide">&larr;</button>
					<div class="home-carousel-dots" data-carousel-dots></div>
					<button class="home-carousel-button" type="button" data-carousel-next aria-label="Next slide">&rarr;</button>
				</div>
			<?php endif; ?>
		</section>

		<section class="home-section home-about">
			<div class="home-about__grid">
				<?php if ( $section_1_images ) : ?>
					<div class="home-about__gallery">
						<?php foreach ( $section_1_images as $index => $image ) : ?>
							<figure class="home-about__image">
								<?php starter_home_render_image( $image, '', 'large', sprintf( 'Section image %d', $index + 1 ) ); ?>
							</figure>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="home-about__copy">
					<?php if ( starter_home_field( 'section_1_title' ) ) : ?>
						<h2><?php echo wp_kses_post( starter_home_field( 'section_1_title' ) ); ?></h2>
					<?php endif; ?>

					<?php echo starter_home_text( starter_home_field( 'section_1_desc' ) ); ?>
				</div>
			</div>
		</section>

		<section class="home-section home-products">
			<?php starter_home_render_products(); ?>
		</section>

		<section class="home-section home-voucher">
			<div class="home-voucher__inner">
				<div class="home-voucher__copy">
					<?php if ( starter_home_field( 'section_3_title' ) ) : ?>
						<h2><?php echo wp_kses_post( starter_home_field( 'section_3_title' ) ); ?></h2>
					<?php endif; ?>

					<?php echo starter_home_text( starter_home_field( 'section_3_desc', 'Tu je Paint and Wine' ) ); ?>
				</div>

				<?php if ( $section_3_images ) : ?>
					<div class="home-voucher__stage" data-home-carousel>
						<div class="home-voucher__carousel">
							<div class="home-voucher__track">
								<?php foreach ( $section_3_images as $index => $image ) : ?>
									<figure class="home-voucher__slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
										<div class="home-voucher__frame">
											<?php starter_home_render_image( $image, '', 'large', sprintf( 'Voucher image %d', $index + 1 ) ); ?>
										</div>
									</figure>
								<?php endforeach; ?>
							</div>
						</div>

						<?php if ( count( $section_3_images ) > 1 ) : ?>
							<div class="home-voucher__dots" data-carousel-dots aria-label="Voucher slideshow controls"></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="home-section home-cards">
			<div class="home-cards__inner">
				<div class="home-section__heading">
					<?php if ( starter_home_field( 'section_4_title' ) ) : ?>
						<h2><?php echo wp_kses_post( starter_home_field( 'section_4_title' ) ); ?></h2>
					<?php endif; ?>

					<?php echo starter_home_text( starter_home_field( 'section_4_desc' ) ); ?>
				</div>

				<?php if ( $section_4_posts ) : ?>
					<div class="home-cards__grid">
						<?php foreach ( $section_4_posts as $item ) : ?>
							<article class="home-card">
								<?php if ( ! empty( $item['image'] ) ) : ?>
									<figure class="home-card__image">
										<?php starter_home_render_image( $item['image'], '', 'medium_large', $item['title'] ?? '' ); ?>
									</figure>
								<?php endif; ?>

								<div class="home-card__body">
									<?php if ( ! empty( $item['title'] ) ) : ?>
										<h3><?php echo esc_html( $item['title'] ); ?></h3>
									<?php endif; ?>

									<?php if ( ! empty( $item['desc'] ) ) : ?>
										<p><?php echo esc_html( $item['desc'] ); ?></p>
									<?php endif; ?>

									<?php if ( ! empty( $item['url'] ) ) : ?>
										<a href="<?php echo esc_url( $item['url'] ); ?>">Saznaj vise</a>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="home-section home-form"<?php echo $form_bg; ?>>
			<div class="home-form__inner">
				<div class="home-form__panel">
					<?php if ( starter_home_field( 'form_title' ) ) : ?>
						<h2><?php echo wp_kses_post( starter_home_field( 'form_title' ) ); ?></h2>
					<?php endif; ?>

					<?php echo starter_home_text( starter_home_field( 'form_desc' ) ); ?>

					<?php
					$form_shortcode = trim( starter_home_field( 'form_shortcode', '' ) );

					if ( '' === $form_shortcode || '[fluentform id="1"]' === $form_shortcode ) {
						$form_shortcode = starter_home_fluent_form_shortcode();
					}

					if ( $form_shortcode && shortcode_exists( 'fluentform' ) ) :
						?>
						<div class="home-form__embed">
							<?php echo do_shortcode( $form_shortcode ); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( starter_home_field( 'form_desc2' ) ) : ?>
					<div class="home-form__after">
						<?php echo starter_home_text( starter_home_field( 'form_desc2' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<script>
		(function () {
			const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

			document.querySelectorAll("[data-home-carousel]").forEach(function (carousel) {
				const slides = Array.from(carousel.querySelectorAll(".home-hero__slide, .home-voucher__slide"));
				const dotsRoot = carousel.querySelector("[data-carousel-dots]");
				const prev = carousel.querySelector("[data-carousel-prev]");
				const next = carousel.querySelector("[data-carousel-next]");
				let activeIndex = 0;
				let autoplayId;

				if (slides.length < 2) {
					return;
				}

				function setSlide(index) {
					activeIndex = (index + slides.length) % slides.length;

					slides.forEach(function (slide, slideIndex) {
						slide.classList.toggle("is-active", slideIndex === activeIndex);
					});

					const track = carousel.querySelector(".home-voucher__track");

					if (track) {
						track.style.transform = "translate3d(-" + (activeIndex * 100) + "%, 0, 0)";
					}

					if (dotsRoot) {
						dotsRoot.querySelectorAll("button").forEach(function (dot, dotIndex) {
							dot.classList.toggle("is-active", dotIndex === activeIndex);
						});
					}
				}

				function restartAutoplay() {
					window.clearInterval(autoplayId);

					if (reduceMotion || document.hidden) {
						return;
					}

					autoplayId = window.setInterval(function () {
						setSlide(activeIndex + 1);
					}, 5200);
				}

				if (dotsRoot) {
					slides.forEach(function (_, index) {
						const dot = document.createElement("button");
						dot.type = "button";
						dot.setAttribute("aria-label", "Go to slide " + (index + 1));
						dot.addEventListener("click", function () {
							setSlide(index);
							restartAutoplay();
						});
						dotsRoot.appendChild(dot);
					});
				}

				if (prev) {
					prev.addEventListener("click", function () {
						setSlide(activeIndex - 1);
						restartAutoplay();
					});
				}

				if (next) {
					next.addEventListener("click", function () {
						setSlide(activeIndex + 1);
						restartAutoplay();
					});
				}

				setSlide(0);
				restartAutoplay();

				document.addEventListener("visibilitychange", restartAutoplay);
			});
		})();
	</script>
	<?php
endwhile;

get_footer();
