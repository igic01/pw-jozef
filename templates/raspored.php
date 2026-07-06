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

	$defaults = function_exists( 'starter_raspored_acf_default_values' ) ? starter_raspored_acf_default_values() : array(
		'raspored_title' => 'Raspored radionica',
		'raspored_desc'  => 'Izaberite termin, temu i drustvo, a mi pripremamo platno, boje, vino i atmosferu.',
	);

	$raspored_title = $defaults['raspored_title'];
	$raspored_desc  = $defaults['raspored_desc'];

	if ( function_exists( 'get_field' ) ) {
		$field_title = get_field( 'raspored_title' );
		$field_desc  = get_field( 'raspored_desc' );

		if ( '' !== $field_title && null !== $field_title && false !== $field_title ) {
			$raspored_title = $field_title;
		}

		if ( '' !== $field_desc && null !== $field_desc && false !== $field_desc ) {
			$raspored_desc = $field_desc;
		}
	}

	$raspored_title = trim( wp_strip_all_tags( $raspored_title ) );
	$raspored_desc  = trim( wp_strip_all_tags( $raspored_desc ) );
	?>

	<main class="site-main home-template raspored-template">
		<section class="home-section home-products">
			<?php
			starter_render_products_schedule(
				array(
					'title'    => $raspored_title ?: __( 'Raspored radionica', 'starter-theme' ),
					'subtitle' => $raspored_desc ?: __( 'Izaberite termin, temu i drustvo, a mi pripremamo platno, boje, vino i atmosferu.', 'starter-theme' ),
				)
			);
			?>
		</section>
	</main>

	<?php
endwhile;

get_footer();
