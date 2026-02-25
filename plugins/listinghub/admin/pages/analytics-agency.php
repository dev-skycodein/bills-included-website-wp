<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

global $wpdb;

// Load all agencies (gsli_agency CPT) and determine claimed/unclaimed.
$agency_args = array(
	'post_type'      => 'gsli_agency',
	'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'no_found_rows'  => true,
);
$agency_ids = get_posts( $agency_args );

$agencies  = array();
$claimed   = 0;
$unclaimed = 0;

if ( ! empty( $agency_ids ) ) {
	foreach ( $agency_ids as $agency_id ) {
		$owner_id = (int) get_post_meta( $agency_id, 'agency_owner', true );
		$status   = $owner_id > 0 ? 'claimed' : 'unclaimed';
		if ( 'claimed' === $status ) {
			$claimed++;
		} else {
			$unclaimed++;
		}

		$agencies[ $agency_id ] = array(
			'agency_id' => (int) $agency_id,
			'owner_id'  => $owner_id,
			'status'    => $status,
		);
	}
}

// Count claim starts: cya_claim CPT with agency_post_id.
$posts_table    = $wpdb->posts;
$postmeta_table = $wpdb->postmeta;

$sql_claims = $wpdb->prepare(
	"SELECT pm.meta_value AS agency_post_id,
	        COUNT(*) AS total_claims
	 FROM {$posts_table} p
	 INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id
	 WHERE p.post_type = %s
	   AND p.post_status <> 'trash'
	   AND pm.meta_key = 'agency_post_id'
	 GROUP BY pm.meta_value",
	'cya_claim'
);

$claim_rows       = $wpdb->get_results( $sql_claims, ARRAY_A );
$claims_by_agency = array();
$total_claims     = 0;

if ( ! empty( $claim_rows ) ) {
	foreach ( $claim_rows as $row ) {
		$agency_post_id = (int) $row['agency_post_id'];
		$count          = (int) $row['total_claims'];
		$total_claims  += $count;
		$claims_by_agency[ $agency_post_id ] = $count;
	}
}

// Merge claim counts into agency rows.
foreach ( $agencies as $id => &$agency ) {
	$agency['claims'] = isset( $claims_by_agency[ $id ] ) ? (int) $claims_by_agency[ $id ] : 0;
}
unset( $agency );

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Agency engagement', 'listinghub' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Counts of claimed vs unclaimed agencies and how many agency claims have been requested.', 'listinghub' ); ?>
			</p>
		</div>
	</div>

	<div class="row mb-3">
		<div class="col-md-3">
			<div class="card p-3">
				<strong><?php esc_html_e( 'Total agencies', 'listinghub' ); ?></strong>
				<p class="mt-1 mb-0"><?php echo esc_html( (string) count( $agencies ) ); ?></p>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card p-3">
				<strong><?php esc_html_e( 'Claimed agencies', 'listinghub' ); ?></strong>
				<p class="mt-1 mb-0"><?php echo esc_html( (string) $claimed ); ?></p>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card p-3">
				<strong><?php esc_html_e( 'Unclaimed agencies', 'listinghub' ); ?></strong>
				<p class="mt-1 mb-0"><?php echo esc_html( (string) $unclaimed ); ?></p>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card p-3">
				<strong><?php esc_html_e( 'Total claim requests', 'listinghub' ); ?></strong>
				<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_claims ); ?></p>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<?php if ( empty( $agencies ) ) : ?>
				<p><?php esc_html_e( 'No agencies found.', 'listinghub' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Agency', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Agency owner', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Claim requests', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $agencies as $agency ) : ?>
							<?php
							$agency_id   = (int) $agency['agency_id'];
							$status      = $agency['status'] === 'claimed' ? __( 'Claimed', 'listinghub' ) : __( 'Unclaimed', 'listinghub' );
							$owner_id    = (int) $agency['owner_id'];
							$claims      = (int) $agency['claims'];
							$title       = get_the_title( $agency_id );
							$link        = get_edit_post_link( $agency_id, 'raw' );
							$owner_label = '';
							if ( $owner_id > 0 ) {
								$user = get_user_by( 'ID', $owner_id );
								if ( $user ) {
									$owner_label = $user->display_name . ' (#' . (int) $owner_id . ')';
								} else {
									$owner_label = '#' . (int) $owner_id;
								}
							} else {
								$owner_label = '—';
							}
							?>
							<tr>
								<td>
									<?php
									if ( $link && $title ) {
										echo '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . ' (#' . (int) $agency_id . ')</a>';
									} elseif ( $title ) {
										echo esc_html( $title ) . ' (#' . (int) $agency_id . ')';
									} else {
										echo '#' . (int) $agency_id;
									}
									?>
								</td>
								<td><?php echo esc_html( $status ); ?></td>
								<td><?php echo esc_html( $owner_label ); ?></td>
								<td><?php echo esc_html( (string) $claims ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

