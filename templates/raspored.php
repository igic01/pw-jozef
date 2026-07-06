<?php
/**
 * Template Name: Raspored
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$description = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags( get_the_content() );
	$description = trim( $description );
	?>

	<main class="site-main home-template raspored-template">
		<section class="home-section home-products">
			<?php
			starter_render_products_schedule(
				array(
					'title'    => get_the_title() ?: __( 'Raspored', 'starter-theme' ),
					'subtitle' => $description ?: __( 'Ne brinite, ne treba vam nikakvo iskustvo.', 'starter-theme' ),
				)
			);
			?>
		</section>
	</main>

	<?php
endwhile;

get_footer();
