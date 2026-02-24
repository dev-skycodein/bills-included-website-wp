<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Agency engagement', 'listinghub' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This section will show metrics about agency activity (claims, logins, listings per agency, response times, etc.).', 'listinghub' ); ?>
			</p>
			<p><?php esc_html_e( 'Implementation coming next.', 'listinghub' ); ?></p>
		</div>
	</div>
</div>

