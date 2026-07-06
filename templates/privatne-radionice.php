<?php
/**
 * Template Name: Privatne Radionice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_url = content_url( '/uploads/2026/06/placeholder-1.webp' );

$workshops = array(
	array(
		'key'        => 'classic',
		'title'      => 'Klasična P&W',
		'alt'        => 'Klasična Paint and Wine radionica',
		'summary'    => 'Korak po korak do savršene slike uz vino, opuštenu atmosferu i vođenje instruktorke.',
		'modalTitle' => 'Klasična Paint and Wine Radionica',
		'copy'       => array(
			'Kod nas u ateljeu, ili na lokaciji koju vi odaberete, zakažite radionicu na kojoj će se svi učesnici zajedno opustiti, smijati i kvalitetno provesti vrijeme. Učesnici će slikati unaprijed odabranu temu, korak po korak sa instruktorkom, dok uživaju u vrhunskim vinima i druženju.',
			'Emocije učesnika na kraju radionice su neprocjenjive, a najbolje od svega je što rad ostaje kao suvenir.',
			'<strong>Preporuka za:</strong> kolektive, veće turističke grupe, MICE grupe i događaje na kojima želite uspomenu koja ostaje.',
		),
	),
	array(
		'key'        => 'neon',
		'title'      => 'Neon P&C',
		'alt'        => 'Neon Paint and Cocktails radionica',
		'summary'    => 'Večernji format sa UV bojama, koktel atmosferom i dinamičnim vizuelnim efektom.',
		'modalTitle' => 'Neon Paint & Cocktails',
		'copy'       => array(
			'Ovaj format donosi intenzivnije boje, UV svjetlo i energiju večernjeg izlaska, ali i dalje zadržava jednostavan korak-po-korak pristup zbog kojeg svi mogu da učestvuju.',
			'Idealan je kada želite malo jaču atmosferu, zabavniji ritam i vizuelno upečatljiv sadržaj za privatni event ili poseban izlazak.',
			'<strong>Preporuka za:</strong> djevojačke večeri, večernje evente, mlađe grupe i brend aktivacije.',
		),
	),
	array(
		'key'        => 'kids',
		'title'      => 'Paint & Kids',
		'alt'        => 'Paint and Kids radionica',
		'summary'    => 'Kreativan format za rođendane, porodična okupljanja i razigrane privatne proslave.',
		'modalTitle' => 'Paint & Kids',
		'copy'       => array(
			'Lagani i razigrani format u kojem je fokus na druženju, kreativnosti i iskustvu koje je prilagođeno mlađim učesnicima i porodičnim grupama.',
			'Tempo radionice, izbor motiva i trajanje mogu se prilagoditi uzrastu i tipu proslave kako bi događaj ostao opušten i jednostavan za organizaciju.',
			'<strong>Preporuka za:</strong> rođendane, porodične proslave i privatna okupljanja sa djecom.',
		),
	),
	array(
		'key'        => 'custom',
		'title'      => 'Radionica Po Mjeri',
		'alt'        => 'Radionica po mjeri',
		'summary'    => 'Temu, trajanje i atmosferu prilagođavamo vašem timu, gostima i lokaciji događaja.',
		'modalTitle' => 'Radionica Po Mjeri',
		'copy'       => array(
			'Kada imate specifičan koncept, temu, broj gostiju ili lokaciju, osmišljavamo privatnu radionicu koja odgovara baš tom događaju.',
			'Zajedno definišemo format, trajanje, vrstu pića, nivo vođenja i sve detalje kako bi sadržaj bio usklađen sa vašim gostima i ciljem događaja.',
			'<strong>Preporuka za:</strong> kompanije, turističke grupe, hotele, agencije i posebne privatne proslave.',
		),
	),
);
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
	?>

	<main class="site-main v4p-page private-workshops-template">
		<section class="v4p-hero" aria-label="<?php esc_attr_e( 'Privatne radionice hero', 'starter-theme' ); ?>">
			<div class="v4p-hero-media">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Privatna Paint and Wine radionica', 'starter-theme' ); ?>">
			</div>

			<div class="v4p-hero-content">
				<div class="v4p-hero-shell">
					<p class="v4p-eyebrow"><?php esc_html_e( 'Privatne Radionice', 'starter-theme' ); ?></p>
					<h1 class="v4p-title">Zakažite <span class="v4p-accent">Privatnu</span> Radionicu!</h1>
					<p class="v4p-lead">
						Bilo da ste HR u firmi kojem treba nova zanimacija za zaposlene, kuma koja organizuje
						djevojačko veče, turistička organizacija koja želi da impresionira goste, ili slično,
						mi smo tu za vas!
					</p>
					<p class="v4p-lead">
						Radionicu možete zakazati u našem ateljeu, ali i bilo gdje u Crnoj Gori, mi dolazimo
						na lokaciju.
					</p>
				</div>
			</div>
		</section>

		<section class="v4p-frame" aria-labelledby="v4p-types-title">
			<div class="v4p-inner">
				<h2 class="v4p-heading" id="v4p-types-title"><?php esc_html_e( 'Vrste Radionica', 'starter-theme' ); ?></h2>
				<p class="v4p-intro">
					Birajte format koji najbolje odgovara vašem događaju. Svaka radionica može da se organizuje
					u našem ateljeu ili na lokaciji koju vi odaberete.
				</p>

				<div class="v4p-types-grid">
					<?php foreach ( $workshops as $workshop ) : ?>
						<article class="v4p-card">
							<div class="v4p-card-media">
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $workshop['alt'] ); ?>" loading="lazy">
							</div>
							<div class="v4p-card-body">
								<h3><?php echo esc_html( $workshop['title'] ); ?></h3>
								<p><?php echo esc_html( $workshop['summary'] ); ?></p>
								<button class="v4p-button" type="button" data-v4p-open="<?php echo esc_attr( $workshop['key'] ); ?>"><?php esc_html_e( 'Saznaj više', 'starter-theme' ); ?></button>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="v4p-frame" aria-labelledby="v4p-gallery-title">
			<div class="v4p-inner">
				<h2 class="v4p-heading" id="v4p-gallery-title"><?php esc_html_e( 'Galerija', 'starter-theme' ); ?></h2>

				<div class="v4p-gallery-grid">
					<?php for ( $index = 1; $index <= 8; $index++ ) : ?>
						<figure class="v4p-gallery-card">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( sprintf( 'Galerija privatne radionice %d', $index ) ); ?>" loading="lazy">
						</figure>
					<?php endfor; ?>
				</div>
			</div>
		</section>

		<section class="v4p-frame" aria-labelledby="v4p-form-title">
			<div class="v4p-inner">
				<div class="v4p-form-layout">
					<div class="v4p-form-copy">
						<h2 id="v4p-form-title"><?php esc_html_e( 'Formular sa osnovnim informacijama za rezervisanje', 'starter-theme' ); ?></h2>
						<p class="v4p-form-note">
							Pošaljite nam osnovne informacije o događaju i javićemo vam se sa prijedlogom
							termina, ponudom i svim narednim koracima.
						</p>
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
			<?php foreach ( $workshops as $workshop ) : ?>
				<article data-v4p-detail="<?php echo esc_attr( $workshop['key'] ); ?>">
					<div class="v4p-modal-media">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $workshop['alt'] ); ?>" loading="lazy">
					</div>
					<div class="v4p-modal-body">
						<h3><?php echo esc_html( $workshop['modalTitle'] ); ?></h3>
						<?php foreach ( $workshop['copy'] as $paragraph ) : ?>
							<p><?php echo wp_kses_post( $paragraph ); ?></p>
						<?php endforeach; ?>
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
