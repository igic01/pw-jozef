<?php
/**
 * Reusable private workshops media gallery.
 *
 * Expected args:
 * - title: Section heading.
 * - media: Relationship attachments, attachment IDs, URLs, or legacy ACF image arrays.
 * - section_id: Optional unique HTML id prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

if ( ! function_exists( 'starter_private_workshops_is_video_url' ) ) {
	function starter_private_workshops_is_video_url( $url ) {
		return (bool) preg_match( '/\.(mp4|m4v|webm|ogv|mov)(\?.*)?$/i', (string) $url );
	}
}

if ( ! function_exists( 'starter_private_workshops_is_image_url' ) ) {
	function starter_private_workshops_is_image_url( $url ) {
		return (bool) preg_match( '/\.(jpg|jpeg|png|gif|webp|avif)(\?.*)?$/i', (string) $url );
	}
}

if ( ! function_exists( 'starter_private_workshops_gallery_media' ) ) {
	function starter_private_workshops_gallery_media( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$media = array();

		foreach ( $items as $item ) {
			$id    = 0;
			$url   = '';
			$mime  = '';
			$title = '';
			$alt   = '';

			if ( $item instanceof WP_Post ) {
				$id    = $item->ID;
				$url   = wp_get_attachment_url( $id );
				$mime  = get_post_mime_type( $id );
				$title = get_the_title( $id );
				$alt   = get_post_meta( $id, '_wp_attachment_image_alt', true );
			} elseif ( is_numeric( $item ) ) {
				$id    = (int) $item;
				$url   = wp_get_attachment_url( $id );
				$mime  = get_post_mime_type( $id );
				$title = get_the_title( $id );
				$alt   = get_post_meta( $id, '_wp_attachment_image_alt', true );
			} elseif ( is_array( $item ) ) {
				$id    = ! empty( $item['ID'] ) ? (int) $item['ID'] : ( ! empty( $item['id'] ) ? (int) $item['id'] : 0 );
				$url   = ! empty( $item['url'] ) ? $item['url'] : starter_private_workshops_image_url( $item, 'large' );
				$mime  = ! empty( $item['mime_type'] ) ? $item['mime_type'] : ( $id ? get_post_mime_type( $id ) : '' );
				$title = ! empty( $item['title'] ) ? $item['title'] : ( $id ? get_the_title( $id ) : '' );
				$alt   = ! empty( $item['alt'] ) ? $item['alt'] : ( $id ? get_post_meta( $id, '_wp_attachment_image_alt', true ) : '' );
			} elseif ( is_string( $item ) ) {
				$url = $item;
			}

			if ( ! $url ) {
				continue;
			}

			$is_video = 0 === strpos( (string) $mime, 'video/' ) || starter_private_workshops_is_video_url( $url );
			$is_image = 0 === strpos( (string) $mime, 'image/' ) || starter_private_workshops_is_image_url( $url );

			if ( ! $is_video && ! $is_image ) {
				continue;
			}

			$media[] = array(
				'id'    => $id,
				'type'  => $is_video ? 'video' : 'image',
				'url'   => $url,
				'mime'  => $mime,
				'title' => $title,
				'alt'   => $alt ?: $title,
				'raw'   => $item,
			);
		}

		return $media;
	}
}

$gallery_title = $args['title'] ?? __( 'Galerija', 'starter-theme' );
$gallery_media = starter_private_workshops_gallery_media( $args['media'] ?? array() );
$section_id    = ! empty( $args['section_id'] ) ? sanitize_title( $args['section_id'] ) : ( function_exists( 'wp_unique_id' ) ? wp_unique_id( 'v4p-gallery-' ) : uniqid( 'v4p-gallery-', false ) );
$heading_id    = $section_id . '-title';

if ( ! $gallery_media ) {
	return;
}
?>

<div class="v4p-page v4p-gallery-section private-workshops-gallery-section" id="<?php echo esc_attr( $section_id ); ?>">
	<section class="v4p-frame" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="v4p-inner">
			<?php if ( $gallery_title ) : ?>
				<h2 class="v4p-heading" id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $gallery_title ); ?></h2>
			<?php endif; ?>

			<div class="v4p-gallery-grid">
				<?php foreach ( $gallery_media as $index => $item ) : ?>
					<figure class="v4p-gallery-card v4p-gallery-card--<?php echo esc_attr( $item['type'] ); ?>">
						<?php if ( 'video' === $item['type'] ) : ?>
							<video class="v4p-gallery-video" controls preload="metadata" playsinline>
								<source src="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['mime'] ? ' type="' . esc_attr( $item['mime'] ) . '"' : ''; ?>>
							</video>
						<?php else : ?>
							<?php
							$image = $item['id'] ? $item['id'] : $item['raw'];
							starter_private_workshops_render_image( $image, '', 'large', $item['alt'] ?: sprintf( 'Galerija privatne radionice %d', $index + 1 ) );
							?>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
</div>
