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

	if ( ! is_array( $section_posts ) ) {
		$section_posts = array();
	}

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

		<section class="v4p-frame" aria-labelledby="v4p-types-title">
			<div class="v4p-inner">
				<?php if ( $section_title ) : ?>
					<h2 class="v4p-heading" id="v4p-types-title"><?php echo esc_html( $section_title ); ?></h2>
				<?php endif; ?>

				<?php if ( $section_desc ) : ?>
					<div class="v4p-intro">
						<?php echo starter_private_workshops_text( $section_desc ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $section_posts ) : ?>
					<div class="v4p-types-grid">
						<?php foreach ( $section_posts as $index => $post_card ) : ?>
							<?php
							$post_img   = $post_card['img'] ?? '';
							$post_title = $post_card['title'] ?? '';
							$post_desc  = $post_card['desc'] ?? '';
							$post_key   = 'post-' . $index;
							?>
							<article class="v4p-card">
								<?php if ( $post_img ) : ?>
									<div class="v4p-card-media">
										<?php starter_private_workshops_render_image( $post_img, '', 'large', $post_title ); ?>
									</div>
								<?php endif; ?>

								<div class="v4p-card-body">
									<?php if ( $post_title ) : ?>
										<h3><?php echo esc_html( $post_title ); ?></h3>
									<?php endif; ?>

									<?php if ( $post_desc ) : ?>
										<p><?php echo esc_html( $post_desc ); ?></p>
									<?php endif; ?>

									<button class="v4p-button" type="button" data-v4p-open="<?php echo esc_attr( $post_key ); ?>"><?php esc_html_e( 'Saznaj više', 'starter-theme' ); ?></button>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( $gallery_images ) : ?>
			<section class="v4p-frame" aria-labelledby="v4p-gallery-title">
				<div class="v4p-inner">
					<h2 class="v4p-heading" id="v4p-gallery-title"><?php esc_html_e( 'Galerija', 'starter-theme' ); ?></h2>

					<div class="v4p-gallery-grid">
						<?php foreach ( $gallery_images as $index => $image ) : ?>
							<figure class="v4p-gallery-card">
								<?php starter_private_workshops_render_image( $image, '', 'large', sprintf( 'Galerija privatne radionice %d', $index + 1 ) ); ?>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

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
						if ( shortcode_exists( 'forminator_form' ) ) {
							echo do_shortcode( '[forminator_form id="161"]' );
						}
						?>
					</div>
				</div>
			</div>
		</section>

		<div class="v4p-hidden-detail">
			<?php foreach ( $section_posts as $index => $post_card ) : ?>
				<?php
				$post_img   = $post_card['img'] ?? '';
				$post_title = $post_card['title'] ?? '';
				$post_desc  = $post_card['desc'] ?? '';
				$post_key   = 'post-' . $index;
				?>
				<article data-v4p-detail="<?php echo esc_attr( $post_key ); ?>">
					<?php if ( $post_img ) : ?>
						<div class="v4p-modal-media">
							<?php starter_private_workshops_render_image( $post_img, '', 'large', $post_title ); ?>
						</div>
					<?php endif; ?>

					<div class="v4p-modal-body">
						<?php if ( $post_title ) : ?>
							<h3><?php echo esc_html( $post_title ); ?></h3>
						<?php endif; ?>

						<?php if ( $post_desc ) : ?>
							<?php echo starter_private_workshops_text( $post_desc ); ?>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="v4p-modal" id="v4p-modal" aria-hidden="true">
			<div class="v4p-modal-backdrop" data-v4p-close></div>
			<div class="v4p-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="v4p-modal-title">
				<button class="v4p-modal-close" type="button" aria-label="<?php esc_attr_e( 'Zatvori prozor', 'starter-theme' ); ?>" data-v4p-close>&times;</button>
				<div class="v4p-modal-content" id="v4p-modal-content"></div>
			</div>
		</div>
	</main>

	<script>
		(function () {
			const root = document.querySelector(".private-workshops-template");
			if (!root) return;

			const modal = root.querySelector("#v4p-modal");
			const modalContent = root.querySelector("#v4p-modal-content");
			const openButtons = root.querySelectorAll("[data-v4p-open]");
			const closeButtons = root.querySelectorAll("[data-v4p-close]");
			const details = root.querySelector(".v4p-hidden-detail");
			let lastTrigger = null;
			let previousBodyOverflow = "";
			let previousBodyPaddingRight = "";

			function openModal(key, trigger) {
				const detail = details.querySelector('[data-v4p-detail="' + key + '"]');
				if (!detail) return;

				const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
				const bodyPaddingRight = parseFloat(window.getComputedStyle(document.body).paddingRight) || 0;

				modalContent.innerHTML = detail.innerHTML;
				const title = modalContent.querySelector("h3");
				if (title) title.id = "v4p-modal-title";
				previousBodyOverflow = document.body.style.overflow;
				previousBodyPaddingRight = document.body.style.paddingRight;
				modal.classList.add("is-open");
				modal.setAttribute("aria-hidden", "false");
				document.body.style.overflow = "hidden";
				if (scrollbarWidth > 0) {
					document.body.style.paddingRight = bodyPaddingRight + scrollbarWidth + "px";
				}
				lastTrigger = trigger || null;
			}

			function closeModal() {
				modal.classList.remove("is-open");
				modal.setAttribute("aria-hidden", "true");
				modalContent.innerHTML = "";
				document.body.style.overflow = previousBodyOverflow;
				document.body.style.paddingRight = previousBodyPaddingRight;
				if (lastTrigger) lastTrigger.focus();
			}

			openButtons.forEach(function (button) {
				button.addEventListener("click", function () {
					openModal(button.dataset.v4pOpen, button);
				});
			});

			closeButtons.forEach(function (button) {
				button.addEventListener("click", closeModal);
			});

			document.addEventListener("keydown", function (event) {
				if (event.key === "Escape" && modal.classList.contains("is-open")) {
					closeModal();
				}
			});
		})();
	</script>

	<?php
endwhile;
?>

<?php wp_footer(); ?>
</body>
</html>
