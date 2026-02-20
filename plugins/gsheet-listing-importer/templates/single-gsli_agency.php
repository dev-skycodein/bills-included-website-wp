<?php
/**
 * Single agency template: agency header with details card, then listing grid (same as all-listings, contained).
 * Used when viewing /agencies/{slug}/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) {
	the_post();
	$agency_id   = get_the_ID();
	$agency_name = get_the_title();
	$agency_desc = get_the_content();
	// Single source for logo – agency_logo (same key used by importer, edit form, and listing sidebar).
	$agency_logo_url = get_post_meta( $agency_id, 'agency_logo', true );
	$agency_logo_url = is_string( $agency_logo_url ) ? trim( $agency_logo_url ) : '';
	$email       = get_post_meta( $agency_id, 'agency_email', true );
	$phone       = get_post_meta( $agency_id, 'agency_phone', true );
	$website     = get_post_meta( $agency_id, 'agency_website', true );
	$address     = get_post_meta( $agency_id, 'agency_address', true );
	$city        = get_post_meta( $agency_id, 'agency_city', true );
	$owner_id    = (int) get_post_meta( $agency_id, 'agency_owner', true );
	$owner       = $owner_id ? get_userdata( $owner_id ) : null;
	?>
	<article id="agency-<?php echo (int) $agency_id; ?>" <?php post_class( 'gsli-single-agency' ); ?>>
		<div class="container py-4">
			<div class="row align-items-start">
				<!-- Heading + intro -->
				<div class="col-lg-8 mb-4 mb-lg-0">
					<header class="agency-header">
						<?php if ( $agency_logo_url ) : ?>
							<div class="agency-logo mb-3">
								<img src="<?php echo esc_url( $agency_logo_url ); ?>" alt="" class="rounded shadow-sm" style="max-width: 100%; height: auto;">
							</div>
						<?php endif; ?>
						<h1 class="agency-title mb-2"><?php echo esc_html( $agency_name ); ?></h1>
						<?php if ( $agency_desc ) : ?>
							<div class="agency-description text-secondary"><?php the_content(); ?></div>
						<?php endif; ?>
					</header>
				</div>
				<!-- Details card -->
				<div class="col-lg-4">
					<div class="card gsli-agency-details-card shadow-sm border-0 h-100">
						<div class="card-body">
							<h3 class="h6 text-uppercase text-muted mb-3"><?php esc_html_e( 'Agency details', 'gsheet-listing-importer' ); ?></h3>
							<ul class="list-unstyled mb-0">
								<?php if ( ! empty( $email ) ) : ?>
									<li class="mb-2">
										<a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>" class="text-decoration-none">
											<span class="d-inline-block mr-2" style="width:1.25rem; text-align:center;"><i class="fa fa-envelope text-muted" aria-hidden="true"></i></span>
											<?php echo esc_html( $email ); ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $phone ) ) : ?>
									<li class="mb-2">
										<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="text-decoration-none">
											<span class="d-inline-block mr-2" style="width:1.25rem; text-align:center;"><i class="fa fa-phone text-muted" aria-hidden="true"></i></span>
											<?php echo esc_html( $phone ); ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $website ) ) : ?>
									<li class="mb-2">
										<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
											<span class="d-inline-block mr-2" style="width:1.25rem; text-align:center;"><i class="fa fa-globe text-muted" aria-hidden="true"></i></span>
											<?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ); ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $address ) || ! empty( $city ) ) : ?>
									<li class="mb-2">
										<span class="d-inline-block mr-2" style="width:1.25rem; text-align:center;"><i class="fa fa-map-marker text-muted" aria-hidden="true"></i></span>
										<?php
										$parts = array_filter( array( $address, $city ) );
										echo esc_html( implode( ', ', $parts ) );
										?>
									</li>
								<?php endif; ?>
								<?php if ( ! $email && ! $phone && ! $website && ! $address && ! $city ) : ?>
									<li class="text-muted"><?php esc_html_e( 'No contact details listed.', 'gsheet-listing-importer' ); ?></li>
								<?php endif; ?>
							</ul>
							<?php if ( $owner && $owner->display_name ) : ?>
								<hr class="my-3">
								<h3 class="h6 text-uppercase text-muted mb-2"><?php esc_html_e( 'Agency contact', 'gsheet-listing-importer' ); ?></h3>
								<div class="d-flex align-items-center">
									<?php echo get_avatar( $owner_id, 48, '', $owner->display_name, array( 'class' => 'rounded-circle mr-2' ) ); ?>
									<div>
										<span class="font-weight-bold"><?php echo esc_html( $owner->display_name ); ?></span>
										<?php
										$author_url = get_author_posts_url( $owner_id );
										if ( $author_url ) :
											?>
											<br><a href="<?php echo esc_url( $author_url ); ?>" class="small text-decoration-none"><?php esc_html_e( 'View profile', 'gsheet-listing-importer' ); ?></a>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="agency-listings">
			<?php
			// Same UI as all-listings (filters + grid), no map, contained width.
			echo do_shortcode( '[listinghub_archive_grid_no_map agency_post_id="' . (int) $agency_id . '" search-form="on-page" wrapper_class="container"]' );
			?>
		</div>
	</article>
	<?php
}

get_footer();
