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
	$page_content     = trim( get_the_content() );
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
			<?php
			if ( shortcode_exists( 'paint_wine_products' ) ) {
				echo do_shortcode( '[paint_wine_products exclude_category="pw-shop"]' );
			}
			?>
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

					<?php if ( $page_content ) : ?>
						<div class="home-form__embed">
							<?php the_content(); ?>
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
