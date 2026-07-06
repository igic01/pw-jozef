<?php
/**
 * Reusable private workshops posts section.
 *
 * Expected args:
 * - title: Section heading.
 * - desc: Section intro copy.
 * - posts: Relationship posts, post IDs, or legacy card arrays.
 * - section_id: Optional unique HTML id prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

if ( ! function_exists( 'starter_private_workshops_post_text' ) ) {
	function starter_private_workshops_post_text( $post ) {
		$excerpt = get_the_excerpt( $post );

		if ( $excerpt ) {
			return $excerpt;
		}

		return wp_trim_words( wp_strip_all_tags( $post->post_content ), 28 );
	}
}

if ( ! function_exists( 'starter_private_workshops_posts' ) ) {
	function starter_private_workshops_posts( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$cards         = array();
		$default_image = function_exists( 'starter_private_workshops_acf_placeholder_image_value' ) ? starter_private_workshops_acf_placeholder_image_value() : '';

		foreach ( $items as $item ) {
			if ( is_array( $item ) && ( isset( $item['img'] ) || isset( $item['title'] ) || isset( $item['desc'] ) ) ) {
				$cards[] = array(
					'img'    => $item['img'] ?? $default_image,
					'title'  => $item['title'] ?? '',
					'desc'   => $item['desc'] ?? '',
					'detail' => $item['desc'] ?? '',
				);
				continue;
			}

			$selected_post = $item instanceof WP_Post ? $item : get_post( $item );

			if ( ! $selected_post instanceof WP_Post ) {
				continue;
			}

			$excerpt = starter_private_workshops_post_text( $selected_post );
			$content = trim( $selected_post->post_content ) ? $selected_post->post_content : $excerpt;

			$cards[] = array(
				'img'    => get_post_thumbnail_id( $selected_post ) ?: $default_image,
				'title'  => get_the_title( $selected_post ),
				'desc'   => $excerpt,
				'detail' => $content,
			);
		}

		return $cards;
	}
}

$section_title = $args['title'] ?? '';
$section_desc  = $args['desc'] ?? '';
$section_posts = starter_private_workshops_posts( $args['posts'] ?? array() );
$section_id    = ! empty( $args['section_id'] ) ? sanitize_title( $args['section_id'] ) : ( function_exists( 'wp_unique_id' ) ? wp_unique_id( 'v4p-posts-' ) : uniqid( 'v4p-posts-', false ) );
$heading_id    = $section_id . '-title';
$modal_id      = $section_id . '-modal';

if ( ! $section_title && ! $section_desc && ! $section_posts ) {
	return;
}
?>

<div class="v4p-page v4p-posts-section private-workshops-posts-section" id="<?php echo esc_attr( $section_id ); ?>">
	<section class="v4p-frame" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="v4p-inner">
			<?php if ( $section_title ) : ?>
				<h2 class="v4p-heading" id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $section_title ); ?></h2>
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
						$post_key   = $section_id . '-post-' . $index;
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

	<div class="v4p-hidden-detail">
		<?php foreach ( $section_posts as $index => $post_card ) : ?>
			<?php
			$post_img   = $post_card['img'] ?? '';
			$post_title = $post_card['title'] ?? '';
			$post_desc  = $post_card['detail'] ?? ( $post_card['desc'] ?? '' );
			$post_key   = $section_id . '-post-' . $index;
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

	<div class="v4p-modal" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
		<div class="v4p-modal-backdrop" data-v4p-close></div>
		<div class="v4p-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $modal_id ); ?>-title">
			<button class="v4p-modal-close" type="button" aria-label="<?php esc_attr_e( 'Zatvori prozor', 'starter-theme' ); ?>" data-v4p-close>&times;</button>
			<div class="v4p-modal-content" data-v4p-modal-content></div>
		</div>
	</div>
</div>

<script>
	(function () {
		const root = document.getElementById(<?php echo wp_json_encode( $section_id ); ?>);
		if (!root) return;

		const modal = root.querySelector(".v4p-modal");
		const modalContent = root.querySelector("[data-v4p-modal-content]");
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
			if (title) title.id = <?php echo wp_json_encode( $modal_id . '-title' ); ?>;
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
