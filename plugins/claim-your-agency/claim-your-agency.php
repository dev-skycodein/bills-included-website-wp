<?php
/**
 * Plugin Name: Claim Your Agency
 * Description: Provides Agency Claim CPT and settings for the Claim My Agency flow (recaptcha config, basic approve/reject).
 * Version:     0.1.0
 * Author:      Bills Included
 * Text Domain: claim-your-agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple logging for Claim Your Agency, similar in spirit to GSLI logging.
 * Logs are written to wp-content/plugins/claim-your-agency/logs/*.log
 */
function cya_get_log_dir() {
	return plugin_dir_path( __FILE__ ) . 'logs' . DIRECTORY_SEPARATOR;
}

function cya_log( $message, $context = array() ) {
	$dir = cya_get_log_dir();
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	// Single rolling log file per day.
	$filename = 'cya-' . gmdate( 'Ymd' ) . '.log';
	$filepath = $dir . $filename;

	$timestamp = gmdate( 'Y-m-d H:i:s' );
	$line      = '[' . $timestamp . '] ' . $message;
	if ( ! empty( $context ) ) {
		$line .= ' | ' . wp_json_encode( $context );
	}
	$line .= PHP_EOL;

	@file_put_contents( $filepath, $line, FILE_APPEND );
}

/**
 * Register the Agency Claim CPT.
 */
function cya_register_claim_cpt() {
	$labels = array(
		'name'               => __( 'Agency Claims', 'claim-your-agency' ),
		'singular_name'      => __( 'Agency Claim', 'claim-your-agency' ),
		'menu_name'          => __( 'Agency Claims', 'claim-your-agency' ),
		'name_admin_bar'     => __( 'Agency Claim', 'claim-your-agency' ),
		'add_new'            => __( 'Add New', 'claim-your-agency' ),
		'add_new_item'       => __( 'Add New Agency Claim', 'claim-your-agency' ),
		'edit_item'          => __( 'Edit Agency Claim', 'claim-your-agency' ),
		'new_item'           => __( 'New Agency Claim', 'claim-your-agency' ),
		'view_item'          => __( 'View Agency Claim', 'claim-your-agency' ),
		'search_items'       => __( 'Search Agency Claims', 'claim-your-agency' ),
		'not_found'          => __( 'No agency claims found.', 'claim-your-agency' ),
		'not_found_in_trash' => __( 'No agency claims found in Trash.', 'claim-your-agency' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_admin_bar'  => true,
		'capability_type'    => 'post',
		// Only need a title; all data is stored as meta via the popup form.
		'supports'           => array( 'title' ),
		'has_archive'        => false,
		'rewrite'            => false,
		'menu_position'      => 58,
		'menu_icon'          => 'dashicons-businessperson',
	);

	register_post_type( 'cya_claim', $args );
}
add_action( 'init', 'cya_register_claim_cpt' );

/**
 * Ensure new claims start with a default status of "pending".
 */
function cya_set_default_claim_status( $post_id, $post, $update ) {
	if ( $post->post_type !== 'cya_claim' ) {
		return;
	}

	// Only set default on first save.
	if ( $update ) {
		return;
	}

	if ( ! get_post_meta( $post_id, 'cya_status', true ) ) {
		update_post_meta( $post_id, 'cya_status', 'pending' );
	}
}
add_action( 'save_post', 'cya_set_default_claim_status', 10, 3 );

/**
 * Add a Status column to the Agency Claims list table.
 */
function cya_claim_columns( $columns ) {
	$columns['cya_status']        = __( 'Status', 'claim-your-agency' );
	$columns['cya_email_verified'] = __( 'Email verified', 'claim-your-agency' );
	return $columns;
}
add_filter( 'manage_cya_claim_posts_columns', 'cya_claim_columns' );

/**
 * Render custom column content.
 */
function cya_claim_custom_column( $column, $post_id ) {
	if ( $column === 'cya_status' ) {
		$status = get_post_meta( $post_id, 'cya_status', true );
		if ( ! $status ) {
			$status = 'pending';
		}
		echo esc_html( ucfirst( $status ) );
	} elseif ( $column === 'cya_email_verified' ) {
		$verified = (int) get_post_meta( $post_id, 'cya_email_verified', true );
		echo $verified ? esc_html__( 'Yes', 'claim-your-agency' ) : esc_html__( 'No', 'claim-your-agency' );
	}
}
add_action( 'manage_cya_claim_posts_custom_column', 'cya_claim_custom_column', 10, 2 );

/**
 * Add Approve/Reject row actions to Agency Claims.
 */
function cya_claim_row_actions( $actions, $post ) {
	if ( $post->post_type !== 'cya_claim' ) {
		return $actions;
	}

	$current_status = get_post_meta( $post->ID, 'cya_status', true );
	if ( ! $current_status ) {
		$current_status = 'pending';
	}

	// Only show Approve/Reject while pending.
	if ( $current_status === 'pending' ) {
		$approve_url = wp_nonce_url(
			add_query_arg(
				array(
					'cya_action' => 'approve',
					'post'       => $post->ID,
				),
				admin_url( 'post.php' )
			),
			'cya_change_status_' . $post->ID
		);

		$reject_url = wp_nonce_url(
			add_query_arg(
				array(
					'cya_action' => 'reject',
					'post'       => $post->ID,
				),
				admin_url( 'post.php' )
			),
			'cya_change_status_' . $post->ID
		);

		$actions['cya_approve'] = '<a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Approve', 'claim-your-agency' ) . '</a>';
		$actions['cya_reject']  = '<a href="' . esc_url( $reject_url ) . '">' . esc_html__( 'Reject', 'claim-your-agency' ) . '</a>';
	}

	return $actions;
}
add_filter( 'post_row_actions', 'cya_claim_row_actions', 10, 2 );

/**
 * Get the ListingHub package that represents "Agency" (by role name).
 * Used so we don't rely on a fixed package ID across sites.
 *
 * @return array|null { 'package_id' => int, 'role' => string } or null if not found.
 */
function cya_get_agency_package_from_listinghub() {
	$packages = get_posts(
		array(
			'post_type'      => 'listinghub_pack',
			'post_status'    => 'any',
			'posts_per_page' => 50,
			'meta_key'       => 'listinghub_package_user_role',
			'meta_compare'   => 'EXISTS',
			'fields'         => 'ids',
		)
	);

	if ( empty( $packages ) ) {
		return null;
	}

	foreach ( $packages as $package_id ) {
		$role = get_post_meta( $package_id, 'listinghub_package_user_role', true );
		if ( is_string( $role ) && strtolower( trim( $role ) ) === 'agency' ) {
			return array(
				'package_id' => (int) $package_id,
				'role'       => trim( $role ),
			);
		}
	}

	return null;
}

/**
 * On claim approval: create/find user, assign Agency package role, set agency_owner, reassign listings.
 *
 * @param int $claim_id cya_claim post ID.
 * @return WP_Error|null Error on failure, null on success.
 */
function cya_on_approve_link_user_and_agency( $claim_id ) {
	$claimant_email = get_post_meta( $claim_id, 'claimant_email', true );
	$claimant_name  = get_post_meta( $claim_id, 'claimant_name', true );
	$agency_post_id = (int) get_post_meta( $claim_id, 'agency_post_id', true );

	if ( ! $claimant_email || ! $agency_post_id ) {
		cya_log(
			'Approve: missing claimant email or agency_post_id.',
			array( 'claim_id' => $claim_id, 'claimant_email' => $claimant_email, 'agency_post_id' => $agency_post_id )
		);
		return new WP_Error( 'cya_missing_data', __( 'Claim is missing email or agency.', 'claim-your-agency' ) );
	}

	$agency = cya_get_agency_package_from_listinghub();
	if ( ! $agency ) {
		cya_log(
			'Approve: no ListingHub package with role "Agency" found.',
			array( 'claim_id' => $claim_id )
		);
		return new WP_Error( 'cya_no_agency_package', __( 'No ListingHub package with role "Agency" found. Create one under ListingHub packages.', 'claim-your-agency' ) );
	}

	$role_slug   = $agency['role'];
	$package_id  = $agency['package_id'];

	// Create or get WordPress user by email.
	$user = get_user_by( 'email', $claimant_email );
	if ( ! $user ) {
		$username = sanitize_user( str_replace( array( '@', '.', '+' ), array( '_', '_', '_' ), $claimant_email ), true );
		if ( username_exists( $username ) ) {
			$username = $username . '_' . wp_rand( 100, 999 );
		}
		$user_id = wp_create_user(
			$username,
			wp_generate_password( 24, true ),
			$claimant_email
		);
		if ( is_wp_error( $user_id ) ) {
			cya_log( 'Approve: failed to create user.', array( 'claim_id' => $claim_id, 'error' => $user_id->get_error_message() ) );
			return $user_id;
		}
		$user = get_user_by( 'ID', $user_id );
		if ( $claimant_name ) {
			wp_update_user(
				array(
					'ID'         => $user_id,
					'display_name' => $claimant_name,
					'first_name'  => $claimant_name,
					'last_name'   => '',
				)
			);
		}
		cya_log( 'Approve: created new user.', array( 'claim_id' => $claim_id, 'user_id' => $user_id, 'email' => $claimant_email ) );
	} else {
		$user_id = $user->ID;
	}

	// Assign the Agency role from the package (don't override administrator).
	if ( ! $user->has_cap( 'manage_options' ) ) {
		$user->set_role( $role_slug );
	}
	update_user_meta( $user_id, 'listinghub_package_id', $package_id );

	// Set agency owner on the agency profile (gsli_agency).
	update_post_meta( $agency_post_id, 'agency_owner', $user_id );

	// Reassign all listings linked to this agency to the new owner (post_author).
	$listing_post_type = get_option( 'ep_listinghub_url', 'listing' );
	$listings = get_posts(
		array(
			'post_type'      => $listing_post_type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'agency_post_id',
					'value' => $agency_post_id,
					'compare' => '=',
				),
			),
		)
	);
	foreach ( $listings as $listing_id ) {
		wp_update_post(
			array(
				'ID'          => (int) $listing_id,
				'post_author' => $user_id,
			)
		);
	}

	cya_log(
		'Claim approved: user linked to agency.',
		array(
			'claim_id'       => $claim_id,
			'user_id'        => $user_id,
			'agency_post_id' => $agency_post_id,
			'role'           => $role_slug,
			'package_id'     => $package_id,
			'listings_updated' => count( $listings ),
		)
	);

	return null;
}

/**
 * Handle Approve/Reject actions on the single post screen.
 */
function cya_handle_claim_status_change() {
	if ( ! is_admin() || empty( $_GET['cya_action'] ) || empty( $_GET['post'] ) ) {
		return;
	}

	$action  = sanitize_key( wp_unslash( $_GET['cya_action'] ) );
	$post_id = (int) $_GET['post'];

	if ( ! $post_id || get_post_type( $post_id ) !== 'cya_claim' ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	check_admin_referer( 'cya_change_status_' . $post_id );

	if ( $action === 'approve' ) {
		update_post_meta( $post_id, 'cya_status', 'approved' );

		$err = cya_on_approve_link_user_and_agency( $post_id );
		if ( is_wp_error( $err ) ) {
			cya_log( 'Approve: link user/agency failed.', array( 'claim_id' => $post_id, 'error' => $err->get_error_message() ) );
			set_transient(
				'cya_approve_error_' . get_current_user_id(),
				sprintf(
					/* translators: %s: error message */
					__( 'Claim marked approved, but linking the claimant to the agency failed: %s', 'claim-your-agency' ),
					$err->get_error_message()
				),
				120
			);
		}

		cya_log(
			'Claim approved by admin.',
			array(
				'claim_id'  => $post_id,
				'admin_id'  => get_current_user_id(),
				'admin_ip'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'=> isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '',
			)
		);
	} elseif ( $action === 'reject' ) {
		update_post_meta( $post_id, 'cya_status', 'rejected' );
		cya_log(
			'Claim rejected by admin.',
			array(
				'claim_id'  => $post_id,
				'admin_id'  => get_current_user_id(),
				'admin_ip'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'=> isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '',
			)
		);
	}

	// Redirect back to the list table or edit screen to avoid resubmission.
	wp_safe_redirect( remove_query_arg( array( 'cya_action', '_wpnonce' ) ) );
	exit;
}
add_action( 'admin_init', 'cya_handle_claim_status_change' );

/**
 * Show admin notice if claim was approved but linking user/agency failed.
 */
function cya_admin_notice_approve_error() {
	$key   = 'cya_approve_error_' . get_current_user_id();
	$message = get_transient( $key );
	if ( ! $message ) {
		return;
	}
	delete_transient( $key );
	?>
	<div class="notice notice-warning is-dismissible">
		<p><strong><?php esc_html_e( 'Claim Your Agency:', 'claim-your-agency' ); ?></strong> <?php echo esc_html( $message ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'cya_admin_notice_approve_error' );

/**
 * Register settings for reCAPTCHA configuration.
 */
function cya_register_settings() {
	register_setting(
		'cya_recaptcha_settings',
		'cya_recaptcha_site_key',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	register_setting(
		'cya_recaptcha_settings',
		'cya_recaptcha_secret_key',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	register_setting(
		'cya_recaptcha_settings',
		'cya_recaptcha_enabled',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => function ( $value ) {
				return $value ? 1 : 0;
			},
			'default'           => 0,
		)
	);

	// Firebase config snippet (script tag or JS config object).
	register_setting(
		'cya_recaptcha_settings',
		'cya_firebase_config_snippet',
		array(
			'type'              => 'string',
			// Allow raw snippet; will be escaped on output in textarea.
			'sanitize_callback' => null,
			'default'           => '',
		)
	);

	// Firebase Email Link callback URL (Action URL).
	register_setting(
		'cya_recaptcha_settings',
		'cya_firebase_callback_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);

	// Agency dashboard URL (where approved owners land after Firebase login).
	register_setting(
		'cya_recaptcha_settings',
		'cya_agency_dashboard_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'cya_register_settings' );

/**
 * Add settings page under Settings → Claim Your Agency.
 */
function cya_add_settings_page() {
	// Add as a submenu under the Agency Claims CPT menu.
	add_submenu_page(
		'edit.php?post_type=cya_claim',
		__( 'Claim Your Agency Settings', 'claim-your-agency' ),
		__( 'Settings', 'claim-your-agency' ),
		'manage_options',
		'claim-your-agency-settings',
		'cya_render_settings_page'
	);
}
add_action( 'admin_menu', 'cya_add_settings_page' );

/**
 * Render the settings page HTML.
 */
function cya_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$site_key    = get_option( 'cya_recaptcha_site_key', '' );
	$secret_key  = get_option( 'cya_recaptcha_secret_key', '' );
	$enabled     = (int) get_option( 'cya_recaptcha_enabled', 0 );
	$fb_snippet  = get_option( 'cya_firebase_config_snippet', '' );
	$callback_url   = get_option( 'cya_firebase_callback_url', '' );
	$dashboard_url  = get_option( 'cya_agency_dashboard_url', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Claim Your Agency – Settings', 'claim-your-agency' ); ?></h1>

		<div class="notice notice-info" style="margin: 20px 0; padding: 16px 20px; border-left: 4px solid #2271b1; max-width: 800px;">
			<h2 style="margin: 0 0 12px 0; font-size: 1.1em;"><?php esc_html_e( 'Admin setup: what you need to do for the Claim flow to work (Steps 1 & 2)', 'claim-your-agency' ); ?></h2>
			<p style="margin: 0 0 10px 0;"><?php esc_html_e( 'Complete these steps before testing the “Claim this agency” flow and email verification.', 'claim-your-agency' ); ?></p>
			<ol style="margin: 0; padding-left: 20px; line-height: 1.6;">
				<li><strong><?php esc_html_e( 'Firebase project', 'claim-your-agency' ); ?></strong> — In the Firebase Console, use (or create) a project and enable <strong>Authentication → Sign-in method → Email/Password</strong>. Turn on <strong>Email link (passwordless sign-in)</strong> for that provider.</li>
				<li><strong><?php esc_html_e( 'Authorized domains', 'claim-your-agency' ); ?></strong> — In Firebase: <strong>Authentication → Settings → Authorized domains</strong>. Add your site domain (e.g. <code>yoursite.com</code>, <code>test.yoursite.com</code>). For local testing, add <code>localhost</code>.</li>
				<li><strong><?php esc_html_e( 'Callback page in WordPress', 'claim-your-agency' ); ?></strong> — Create a page (e.g. “Agency login callback”) that will open when users click the link in the verification email. In the page content, add <strong>only</strong> this shortcode: <code>[agency_login_callback]</code>. Publish the page and note its full URL (e.g. <code>https://yoursite.com/agency-login-callback/</code>).</li>
				<li><strong><?php esc_html_e( 'Callback URL in this settings page', 'claim-your-agency' ); ?></strong> — In the “Firebase Email Link callback URL” field below, enter that <strong>exact full URL</strong> (the same as the page you created). This must match the URL you will set in Firebase as the Action URL.</li>
				<li><strong><?php esc_html_e( 'Action URL in Firebase', 'claim-your-agency' ); ?></strong> — In Firebase: <strong>Authentication → Templates</strong> (or the Email Link / Action URL setting). Set the <strong>Action URL</strong> (or “Continue URL”) to the same URL as above (your WordPress callback page). Firebase will send users to this URL when they click the email link.</li>
				<li><strong><?php esc_html_e( 'Firebase config in the plugin', 'claim-your-agency' ); ?></strong> — The plugin loads Firebase via its own script. Ensure <code>plugins/claim-your-agency/firebase-sdk.js</code> contains your project’s Firebase config (apiKey, authDomain, projectId, etc.). You can copy this from Firebase Console → Project settings → Your apps.</li>
				<li><strong><?php esc_html_e( 'Email deliverability (optional but recommended)', 'claim-your-agency' ); ?></strong> — In Firebase: <strong>Authentication → Templates</strong>. Set a clear <strong>Sender name</strong> (e.g. “Bills Included”), a proper <strong>Reply-to</strong> address, and your <strong>App display name</strong> in Project settings so verification emails are less likely to go to spam.</li>
			</ol>
			<p style="margin: 12px 0 0 0; font-size: 13px;"><?php esc_html_e( 'After this, the “Claim this agency” button on listing pages will create a claim, send a verification email, and the callback page will verify the user’s email. You can then approve or reject claims under Agency Claims.', 'claim-your-agency' ); ?></p>
		</div>

		<form action="options.php" method="post">
			<?php settings_fields( 'cya_recaptcha_settings' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'Firebase App Connection', 'claim-your-agency' ); ?></h2>
						<p class="description">
							<?php esc_html_e( 'Paste your Firebase config script or JS snippet, and the callback URL you configure in Firebase Email Link (Action URL).', 'claim-your-agency' ); ?>
						</p>
					</th>
				</tr>
				<tr>
					<th scope="row">
						<label for="cya_firebase_config_snippet"><?php esc_html_e( 'Firebase config script/snippet', 'claim-your-agency' ); ?></label>
					</th>
					<td>
						<textarea class="large-text code" rows="6" id="cya_firebase_config_snippet" name="cya_firebase_config_snippet"><?php echo esc_textarea( $fb_snippet ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Example: the <script> block or JS config object you copy from the Firebase console.', 'claim-your-agency' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cya_firebase_callback_url"><?php esc_html_e( 'Firebase Email Link callback URL', 'claim-your-agency' ); ?></label>
					</th>
					<td>
						<input type="url" class="regular-text" id="cya_firebase_callback_url" name="cya_firebase_callback_url" value="<?php echo esc_attr( $callback_url ); ?>" />
						<p class="description">
							<?php esc_html_e( 'Full URL of the page in WordPress that will handle the Firebase Email Link callback (this same URL should be set as the Action URL in the Firebase console).', 'claim-your-agency' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cya_agency_dashboard_url"><?php esc_html_e( 'Agency dashboard URL', 'claim-your-agency' ); ?></label>
					</th>
					<td>
						<input type="url" class="regular-text" id="cya_agency_dashboard_url" name="cya_agency_dashboard_url" value="<?php echo esc_attr( $dashboard_url ); ?>" />
						<p class="description">
							<?php esc_html_e( 'Where approved agency owners are redirected after signing in with the Firebase Email Link. Use a page that contains the shortcode [agency_dashboard].', 'claim-your-agency' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'reCAPTCHA', 'claim-your-agency' ); ?></h2>
						<p class="description">
							<?php esc_html_e( 'Configure Google reCAPTCHA for the Claim My Agency flow. It will only be used when explicitly enabled here.', 'claim-your-agency' ); ?>
						</p>
					</th>
				</tr>
				<tr>
					<th scope="row">
						<label for="cya_recaptcha_site_key"><?php esc_html_e( 'reCAPTCHA Site Key', 'claim-your-agency' ); ?></label>
					</th>
					<td>
						<input type="text" class="regular-text" id="cya_recaptcha_site_key" name="cya_recaptcha_site_key" value="<?php echo esc_attr( $site_key ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cya_recaptcha_secret_key"><?php esc_html_e( 'reCAPTCHA Secret Key', 'claim-your-agency' ); ?></label>
					</th>
					<td>
						<input type="password" class="regular-text" id="cya_recaptcha_secret_key" name="cya_recaptcha_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Enable reCAPTCHA', 'claim-your-agency' ); ?>
					</th>
					<td>
						<label for="cya_recaptcha_enabled">
							<input type="checkbox" id="cya_recaptcha_enabled" name="cya_recaptcha_enabled" value="1" <?php checked( $enabled, 1 ); ?> />
							<?php esc_html_e( 'Turn on reCAPTCHA protection for the Claim My Agency form.', 'claim-your-agency' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'You can leave this unchecked for now; keys can be saved in advance and enabled later.', 'claim-your-agency' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Shortcode for Firebase Email Link callback page.
 * No localStorage: claim_id comes from URL only; email from user form. Works on any device.
 */
function cya_render_agency_login_callback() {
	$firebase_sdk_url = plugins_url( 'firebase-sdk.js', __FILE__ ) . '?ver=2';
	$root_id          = 'cya-agency-login-callback-root';
	$form_id          = 'cya-callback-email-form';
	$email_input_id   = 'cya-callback-email';
	$msg_id           = 'cya-callback-msg';

	ob_start();
	?>
	<div id="<?php echo esc_attr( $root_id ); ?>">
		<p class="cya-callback-loading"><?php esc_html_e( 'Loading…', 'claim-your-agency' ); ?></p>
	</div>
	<script type="module" src="<?php echo esc_url( $firebase_sdk_url ); ?>"></script>
	<style>
		.cya-callback-form { max-width: 360px; margin: 1em 0; }
		.cya-callback-form label { display: block; margin-bottom: 0.25em; font-weight: 600; }
		.cya-callback-form input[type="email"] { width: 100%; padding: 8px 12px; margin-bottom: 12px; box-sizing: border-box; }
		.cya-callback-form button { padding: 10px 20px; cursor: pointer; }
		.cya-callback-msg { margin-top: 1em; padding: 10px; border-radius: 4px; }
		.cya-callback-msg.success { background: #d4edda; color: #155724; }
		.cya-callback-msg.error { background: #f8d7da; color: #721c24; }
	</style>
	<script>
		(function() {
			var cyaAjaxUrl   = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var cyaAjaxNonce = <?php echo wp_json_encode( wp_create_nonce( 'cya_claim_nonce' ) ); ?>;
			var rootId = <?php echo wp_json_encode( $root_id ); ?>;
			var formId = <?php echo wp_json_encode( $form_id ); ?>;
			var emailInputId = <?php echo wp_json_encode( $email_input_id ); ?>;
			var msgId = <?php echo wp_json_encode( $msg_id ); ?>;

			function getQueryParam(name) {
				var params = new URLSearchParams(window.location.search);
				return params.get(name);
			}

			function showForm(root) {
				root.innerHTML =
					'<p><?php echo esc_js( __( 'To complete verification, enter the email address where you received the sign-in link.', 'claim-your-agency' ) ); ?></p>' +
					'<form id="' + formId + '" class="cya-callback-form">' +
					'<label for="' + emailInputId + '"><?php echo esc_js( __( 'Email address', 'claim-your-agency' ) ); ?></label>' +
					'<input type="email" id="' + emailInputId + '" name="email" required placeholder="<?php echo esc_js( __( 'you@example.com', 'claim-your-agency' ) ); ?>">' +
					'<button type="submit"><?php echo esc_js( __( 'Verify email', 'claim-your-agency' ) ); ?></button>' +
					'</form>' +
					'<div id="' + msgId + '" class="cya-callback-msg" style="display:none;"></div>';
			}

			function showMessage(root, html, isError) {
				var msgEl = document.getElementById(msgId);
				if (msgEl) {
					msgEl.style.display = 'block';
					msgEl.className = 'cya-callback-msg ' + (isError ? 'error' : 'success');
					msgEl.innerHTML = html;
				} else {
					root.innerHTML = '<div class="cya-callback-msg ' + (isError ? 'error' : 'success') + '">' + html + '</div>';
				}
			}

			function markClaimVerified(claimId, email) {
				var formData = new FormData();
				formData.append('action', 'cya_mark_email_verified');
				formData.append('nonce', cyaAjaxNonce);
				formData.append('email', email);
				if (claimId) formData.append('claim_id', claimId);
				return fetch(cyaAjaxUrl, { method: 'POST', credentials: 'same-origin', body: formData }).then(function(r) { return r.json(); });
			}

			function waitForFirebase(cb, maxWait) {
				maxWait = maxWait || 8000;
				var start = Date.now();
				function check() {
					if (window.cyaFirebase && window.cyaFirebase.auth && window.cyaFirebase.isSignInWithEmailLink && window.cyaFirebase.signInWithEmailLink) {
						cb(null);
						return;
					}
					if (Date.now() - start > maxWait) {
						cb(new Error('Firebase SDK did not load'));
						return;
					}
					setTimeout(check, 200);
				}
				check();
			}

			document.addEventListener('DOMContentLoaded', function() {
				var root = document.getElementById(rootId);
				if (!root) return;

				waitForFirebase(function(err) {
					if (err) {
						root.innerHTML = '<p class="cya-callback-msg error"><?php echo esc_js( __( 'Could not load verification. Please try again.', 'claim-your-agency' ) ); ?></p>';
						return;
					}

					if (!window.cyaFirebase.isSignInWithEmailLink(window.location.href)) {
						root.innerHTML = '<p><?php echo esc_js( __( 'This page is for completing your sign-in. Please use the link from the email we sent you.', 'claim-your-agency' ) ); ?></p>';
						return;
					}

					showForm(root);

					document.getElementById(formId).addEventListener('submit', function(e) {
						e.preventDefault();
						var emailInput = document.getElementById(emailInputId);
						var email = (emailInput && emailInput.value) ? emailInput.value.trim() : '';
						if (!email) return;

						var btn = this.querySelector('button[type="submit"]');
						if (btn) { btn.disabled = true; btn.textContent = '<?php echo esc_js( __( 'Verifying…', 'claim-your-agency' ) ); ?>'; }

						window.cyaFirebase.signInWithEmailLink(email, window.location.href)
							.then(function() {
								var claimId = getQueryParam('claim_id');
								if (claimId) {
									return markClaimVerified(claimId, email).then(function(json) {
										if (json && json.success) {
											showMessage(root, '<?php echo esc_js( __( 'Your email has been verified. An admin will review your claim shortly.', 'claim-your-agency' ) ); ?>', false);
											root.querySelector('form') && root.querySelector('form').remove();
										} else {
											showMessage(root, (json && json.data && json.data.message) ? json.data.message : '<?php echo esc_js( __( 'Verification recorded but something went wrong. Please contact support.', 'claim-your-agency' ) ); ?>', true);
										}
									});
								}
								var formData = new FormData();
								formData.append('action', 'cya_agency_wp_login');
								formData.append('nonce', cyaAjaxNonce);
								formData.append('email', email);
								return fetch(cyaAjaxUrl, { method: 'POST', credentials: 'same-origin', body: formData }).then(function(r) { return r.json(); }).then(function(json) {
									if (json && json.success && json.data && json.data.redirect) {
										showMessage(root, '<?php echo esc_js( __( 'Signing you in…', 'claim-your-agency' ) ); ?>', false);
										window.location.href = json.data.redirect;
										return;
									}
									showMessage(root, (json && json.data && json.data.message) ? json.data.message : '<?php echo esc_js( __( 'Could not sign you in. Your claim may still be pending approval.', 'claim-your-agency' ) ); ?>', true);
								});
							})
							.catch(function(error) {
								showMessage(root, (error && error.message) ? error.message : '<?php echo esc_js( __( 'There was a problem verifying your email. Please use the link from your email and try again.', 'claim-your-agency' ) ); ?>', true);
								if (btn) { btn.disabled = false; btn.textContent = '<?php echo esc_js( __( 'Verify email', 'claim-your-agency' ) ); ?>'; }
							});
					});
				});
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode: Agency login request – form to request a Firebase Email Link to sign in (for approved owners).
 */
function cya_render_agency_login_request() {
	$callback_url = get_option( 'cya_firebase_callback_url', '' );
	if ( ! $callback_url ) {
		return '<p>' . esc_html__( 'Agency login is not configured. Please set the Firebase callback URL in Claim Your Agency settings.', 'claim-your-agency' ) . '</p>';
	}

	if ( is_user_logged_in() && cya_user_is_agency_owner( get_current_user_id() ) ) {
		$dashboard_url = get_option( 'cya_agency_dashboard_url', home_url( '/' ) );
		return '<p>' . sprintf(
			/* translators: %s: dashboard URL */
			__( 'You are already signed in. <a href="%s">Go to your agency dashboard</a>.', 'claim-your-agency' ),
			esc_url( $dashboard_url )
		) . '</p>';
	}

	$ajax_url   = admin_url( 'admin-ajax.php' );
	$nonce      = wp_create_nonce( 'cya_claim_nonce' );
	$firebase_url = plugins_url( 'firebase-sdk.js', __FILE__ ) . '?ver=2';
	$form_id   = 'cya-login-request-form';
	$msg_id    = 'cya-login-request-msg';
	ob_start();
	?>
	<div class="cya-login-request-wrap">
		<p><?php esc_html_e( 'Enter the work email for your approved agency account. We’ll send you a sign-in link (no password needed).', 'claim-your-agency' ); ?></p>
		<form id="<?php echo esc_attr( $form_id ); ?>" class="cya-callback-form">
			<label for="cya-login-request-email"><?php esc_html_e( 'Email address', 'claim-your-agency' ); ?></label>
			<input type="email" id="cya-login-request-email" name="email" required placeholder="<?php echo esc_attr__( 'you@example.com', 'claim-your-agency' ); ?>">
			<button type="submit"><?php esc_html_e( 'Send sign-in link', 'claim-your-agency' ); ?></button>
		</form>
		<div id="<?php echo esc_attr( $msg_id ); ?>" class="cya-callback-msg" style="display:none;"></div>
	</div>
	<style>
		.cya-login-request-wrap .cya-callback-form { max-width: 360px; margin: 1em 0; }
		.cya-login-request-wrap .cya-callback-form label { display: block; margin-bottom: 0.25em; font-weight: 600; }
		.cya-login-request-wrap .cya-callback-form input[type="email"] { width: 100%; padding: 8px 12px; margin-bottom: 12px; box-sizing: border-box; }
		.cya-login-request-wrap .cya-callback-form button { padding: 10px 20px; cursor: pointer; }
		.cya-login-request-wrap .cya-callback-msg { margin-top: 1em; padding: 10px; border-radius: 4px; max-width: 360px; }
		.cya-login-request-wrap .cya-callback-msg.success { background: #d4edda; color: #155724; }
		.cya-login-request-wrap .cya-callback-msg.error { background: #f8d7da; color: #721c24; }
	</style>
	<script type="module">
		(function() {
			var form = document.getElementById('<?php echo esc_js( $form_id ); ?>');
			var msgEl = document.getElementById('<?php echo esc_js( $msg_id ); ?>');
			var callbackUrl = <?php echo wp_json_encode( $callback_url ); ?>;

			function showMsg(html, isError) {
				msgEl.style.display = 'block';
				msgEl.className = 'cya-callback-msg ' + (isError ? 'error' : 'success');
				msgEl.innerHTML = html;
			}

			if (!form) return;

			form.addEventListener('submit', function(e) {
				e.preventDefault();
				var emailInput = document.getElementById('cya-login-request-email');
				var email = emailInput && emailInput.value ? emailInput.value.trim() : '';
				if (!email) return;

				var btn = form.querySelector('button[type="submit"]');
				if (btn) { btn.disabled = true; }
				msgEl.style.display = 'none';

				if (typeof window.cyaFirebase === 'undefined' || !window.cyaFirebase.sendSignInLinkToEmail) {
					showMsg('<?php echo esc_js( __( 'Sign-in is loading. Please try again in a moment.', 'claim-your-agency' ) ); ?>', true);
					if (btn) btn.disabled = false;
					return;
				}

				window.cyaFirebase.sendSignInLinkToEmail(email, { url: callbackUrl, handleCodeInApp: true })
					.then(function() {
						showMsg('<?php echo esc_js( __( 'Check your email for the sign-in link. Click it to open your agency dashboard.', 'claim-your-agency' ) ); ?>', false);
						if (btn) btn.disabled = false;
					})
					.catch(function(err) {
						showMsg(err && err.message ? err.message : '<?php echo esc_js( __( 'Could not send the link. Please try again.', 'claim-your-agency' ) ); ?>', true);
						if (btn) btn.disabled = false;
					});
			});
		})();
	</script>
	<script type="module" src="<?php echo esc_url( $firebase_url ); ?>"></script>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode: Agency dashboard – welcome and link to edit profile (for logged-in agency owners).
 * If URL has ?agency_edit=1, shows the edit profile form on the same page.
 */
function cya_render_agency_dashboard() {
	if ( ! is_user_logged_in() ) {
		return cya_render_agency_login_request();
	}

	$user_id = get_current_user_id();
	if ( ! cya_user_is_agency_owner( $user_id ) ) {
		return '<p>' . esc_html__( 'You do not have an agency account linked. If you have claimed an agency, wait for admin approval.', 'claim-your-agency' ) . '</p>';
	}

	// If editing, show the edit profile form on this page.
	if ( isset( $_GET['agency_edit'] ) && '1' === $_GET['agency_edit'] ) {
		return cya_render_agency_edit_profile();
	}

	$agency_post_id = cya_get_agency_post_id_for_user( $user_id );
	$agency_name    = $agency_post_id ? get_the_title( $agency_post_id ) : __( 'Your agency', 'claim-your-agency' );
	$user           = wp_get_current_user();
	$display_name   = $user->display_name ? $user->display_name : $user->user_email;
	$dashboard_url  = get_permalink( get_the_ID() );
	$edit_url       = add_query_arg( 'agency_edit', '1', $dashboard_url );

	ob_start();
	?>
	<div class="cya-dashboard-wrap">
		<h2><?php echo esc_html( sprintf( __( 'Welcome, %s', 'claim-your-agency' ), $display_name ) ); ?></h2>
		<p><?php echo esc_html( sprintf( __( 'Agency: %s', 'claim-your-agency' ), $agency_name ) ); ?></p>
		<p><a href="<?php echo esc_url( $edit_url ); ?>" class="button"><?php esc_html_e( 'Edit agency profile', 'claim-your-agency' ); ?></a></p>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get the gsli_agency post ID that the given user owns (agency_owner).
 *
 * @param int $user_id WordPress user ID.
 * @return int 0 if none.
 */
function cya_get_agency_post_id_for_user( $user_id ) {
	$posts = get_posts(
		array(
			'post_type'      => 'gsli_agency',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array( 'key' => 'agency_owner', 'value' => (int) $user_id, 'compare' => '=' ),
			),
		)
	);
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

/**
 * Shortcode: Agency edit profile – form to edit agency details (for logged-in agency owners only).
 */
function cya_render_agency_edit_profile() {
	if ( ! is_user_logged_in() ) {
		return '<p>' . esc_html__( 'You must be signed in to edit your agency profile.', 'claim-your-agency' ) . '</p>';
	}

	$user_id        = get_current_user_id();
	$agency_post_id = cya_get_agency_post_id_for_user( $user_id );
	if ( ! $agency_post_id ) {
		return '<p>' . esc_html__( 'You do not have an agency linked.', 'claim-your-agency' ) . '</p>';
	}

	$post = get_post( $agency_post_id );
	if ( ! $post || $post->post_type !== 'gsli_agency' ) {
		return '<p>' . esc_html__( 'Invalid agency.', 'claim-your-agency' ) . '</p>';
	}

	$title   = $post->post_title;
	$website = get_post_meta( $agency_post_id, 'agency_website', true );
	$email   = get_post_meta( $agency_post_id, 'agency_email', true );
	$phone   = get_post_meta( $agency_post_id, 'agency_phone', true );
	$address = get_post_meta( $agency_post_id, 'agency_address', true );
	$city    = get_post_meta( $agency_post_id, 'agency_city', true );

	$ajax_url = admin_url( 'admin-ajax.php' );
	$nonce    = wp_create_nonce( 'cya_claim_nonce' );
	ob_start();
	?>
	<div class="cya-edit-profile-wrap">
		<h2><?php esc_html_e( 'Edit agency profile', 'claim-your-agency' ); ?></h2>
		<form id="cya-agency-edit-form" class="cya-edit-form">
			<input type="hidden" name="agency_post_id" value="<?php echo esc_attr( $agency_post_id ); ?>">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<label for="cya-agency-title"><?php esc_html_e( 'Agency name', 'claim-your-agency' ); ?></label>
				<input type="text" id="cya-agency-title" name="agency_title" value="<?php echo esc_attr( $title ); ?>" class="widefat">
			</p>
			<p>
				<label for="cya-agency-website"><?php esc_html_e( 'Website', 'claim-your-agency' ); ?></label>
				<input type="url" id="cya-agency-website" name="agency_website" value="<?php echo esc_attr( $website ); ?>" class="widefat">
			</p>
			<p>
				<label for="cya-agency-email"><?php esc_html_e( 'Email', 'claim-your-agency' ); ?></label>
				<input type="email" id="cya-agency-email" name="agency_email" value="<?php echo esc_attr( $email ); ?>" class="widefat">
			</p>
			<p>
				<label for="cya-agency-phone"><?php esc_html_e( 'Phone', 'claim-your-agency' ); ?></label>
				<input type="text" id="cya-agency-phone" name="agency_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat">
			</p>
			<p>
				<label for="cya-agency-address"><?php esc_html_e( 'Address', 'claim-your-agency' ); ?></label>
				<input type="text" id="cya-agency-address" name="agency_address" value="<?php echo esc_attr( $address ); ?>" class="widefat">
			</p>
			<p>
				<label for="cya-agency-city"><?php esc_html_e( 'City', 'claim-your-agency' ); ?></label>
				<input type="text" id="cya-agency-city" name="agency_city" value="<?php echo esc_attr( $city ); ?>" class="widefat">
			</p>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save changes', 'claim-your-agency' ); ?></button>
			</p>
			<div id="cya-agency-edit-msg" style="display:none; margin-top:10px; padding:10px; border-radius:4px;"></div>
		</form>
	</div>
	<script>
		(function() {
			var form = document.getElementById('cya-agency-edit-form');
			var msgEl = document.getElementById('cya-agency-edit-msg');
			if (!form || !msgEl) return;
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				var fd = new FormData(form);
				fd.append('action', 'cya_agency_update_profile');
				var btn = form.querySelector('button[type="submit"]');
				if (btn) btn.disabled = true;
				fetch(<?php echo wp_json_encode( $ajax_url ); ?>, { method: 'POST', credentials: 'same-origin', body: fd })
					.then(function(r) { return r.json(); })
					.then(function(json) {
						msgEl.style.display = 'block';
						if (json && json.success) {
							msgEl.className = 'notice notice-success';
							msgEl.textContent = '<?php echo esc_js( __( 'Profile saved.', 'claim-your-agency' ) ); ?>';
						} else {
							msgEl.className = 'notice notice-error';
							msgEl.textContent = (json && json.data && json.data.message) ? json.data.message : '<?php echo esc_js( __( 'Could not save.', 'claim-your-agency' ) ); ?>';
						}
						if (btn) btn.disabled = false;
					})
					.catch(function() {
						msgEl.style.display = 'block';
						msgEl.className = 'notice notice-error';
						msgEl.textContent = '<?php echo esc_js( __( 'Could not save.', 'claim-your-agency' ) ); ?>';
						if (btn) btn.disabled = false;
					});
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}

/**
 * AJAX: Update agency profile (title + meta) – only for the agency owner.
 */
function cya_agency_update_profile() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya_claim_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'claim-your-agency' ) ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'You must be signed in.', 'claim-your-agency' ) ) );
	}

	$agency_post_id = isset( $_POST['agency_post_id'] ) ? (int) $_POST['agency_post_id'] : 0;
	if ( ! $agency_post_id ) {
		wp_send_json_error( array( 'message' => __( 'Missing agency.', 'claim-your-agency' ) ) );
	}

	$owner = (int) get_post_meta( $agency_post_id, 'agency_owner', true );
	if ( $owner !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this agency.', 'claim-your-agency' ) ) );
	}

	$post = get_post( $agency_post_id );
	if ( ! $post || $post->post_type !== 'gsli_agency' ) {
		wp_send_json_error( array( 'message' => __( 'Invalid agency.', 'claim-your-agency' ) ) );
	}

	$title   = isset( $_POST['agency_title'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_title'] ) ) : $post->post_title;
	$website = isset( $_POST['agency_website'] ) ? esc_url_raw( wp_unslash( $_POST['agency_website'] ) ) : '';
	$email   = isset( $_POST['agency_email'] ) ? sanitize_email( wp_unslash( $_POST['agency_email'] ) ) : '';
	$phone   = isset( $_POST['agency_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_phone'] ) ) : '';
	$address = isset( $_POST['agency_address'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_address'] ) ) : '';
	$city    = isset( $_POST['agency_city'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_city'] ) ) : '';

	wp_update_post( array( 'ID' => $agency_post_id, 'post_title' => $title ) );
	update_post_meta( $agency_post_id, 'agency_website', $website );
	update_post_meta( $agency_post_id, 'agency_email', $email );
	update_post_meta( $agency_post_id, 'agency_phone', $phone );
	update_post_meta( $agency_post_id, 'agency_address', $address );
	update_post_meta( $agency_post_id, 'agency_city', $city );

	wp_send_json_success( array( 'message' => __( 'Profile saved.', 'claim-your-agency' ) ) );
}
add_action( 'wp_ajax_cya_agency_update_profile', 'cya_agency_update_profile' );

function cya_register_shortcodes() {
	add_shortcode( 'agency_login_callback', 'cya_render_agency_login_callback' );
	add_shortcode( 'agency_login_request', 'cya_render_agency_login_request' );
	add_shortcode( 'agency_dashboard', 'cya_render_agency_dashboard' );
	add_shortcode( 'agency_edit_profile', 'cya_render_agency_edit_profile' );
}
add_action( 'init', 'cya_register_shortcodes' );

/**
 * Add a read-only meta box on the Agency Claim edit screen to show submitted data.
 */
function cya_add_claim_meta_box() {
	add_meta_box(
		'cya_claim_details',
		__( 'Claim details', 'claim-your-agency' ),
		'cya_render_claim_meta_box',
		'cya_claim',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cya_add_claim_meta_box' );

/**
 * Render the claim details meta box (admin side).
 *
 * @param WP_Post $post Current claim post.
 */
function cya_render_claim_meta_box( $post ) {
	$claim_id        = $post->ID;
	$status          = get_post_meta( $claim_id, 'cya_status', true );
	$agency_post_id  = (int) get_post_meta( $claim_id, 'agency_post_id', true );
	$listing_id      = (int) get_post_meta( $claim_id, 'listing_id', true );
	$claimant_name   = get_post_meta( $claim_id, 'claimant_name', true );
	$claimant_email  = get_post_meta( $claim_id, 'claimant_email', true );
	$agency_name     = get_post_meta( $claim_id, 'agency_name', true );
	$agency_website  = get_post_meta( $claim_id, 'agency_website', true );
	$proof_staff     = get_post_meta( $claim_id, 'proof_staff_page_url', true );
	$proof_companies = get_post_meta( $claim_id, 'proof_companies_house_url', true );
	$proof_notes     = get_post_meta( $claim_id, 'proof_notes', true );
	$email_verified  = (int) get_post_meta( $claim_id, 'cya_email_verified', true );

	// Simple domain hint: compare claimant email domain with submitted agency website host.
	$email_domain   = '';
	$website_host   = '';
	$domain_message = '';
	$domain_style   = 'color:#646970;'; // default neutral.

	if ( $claimant_email && false !== strpos( $claimant_email, '@' ) ) {
		$email_domain = strtolower( substr( strrchr( $claimant_email, '@' ), 1 ) );
	}

	if ( $agency_website ) {
		$normalized_url = ( 0 === strpos( $agency_website, 'http' ) ) ? $agency_website : 'https://' . $agency_website;
		$host           = wp_parse_url( $normalized_url, PHP_URL_HOST );
		if ( is_string( $host ) ) {
			$website_host = strtolower( preg_replace( '/^www\./i', '', $host ) );
		}
	}

	if ( $email_domain && $website_host ) {
		$clean_email_domain = preg_replace( '/^www\./i', '', $email_domain );
		if ( $clean_email_domain === $website_host ) {
			$domain_message = __( 'Email domain matches the agency website.', 'claim-your-agency' );
			$domain_style   = 'color:#046a38;font-weight:600;'; // green.
		} elseif ( false !== strpos( $website_host, $clean_email_domain ) || false !== strpos( $clean_email_domain, $website_host ) ) {
			$domain_message = sprintf(
				/* translators: 1: email domain, 2: website host */
				__( 'Email domain (%1$s) is similar to agency host (%2$s) – check manually.', 'claim-your-agency' ),
				$clean_email_domain,
				$website_host
			);
			$domain_style = 'color:#d9831f;font-weight:600;'; // amber.
		} else {
			$domain_message = sprintf(
				/* translators: 1: email domain, 2: website host */
				__( 'Email domain (%1$s) does not match agency host (%2$s). Review proof links carefully.', 'claim-your-agency' ),
				$clean_email_domain,
				$website_host
			);
			$domain_style = 'color:#b32d2e;font-weight:600;'; // red.
		}
	} elseif ( $email_domain || $website_host ) {
		$domain_message = __( 'Not enough information to compare domains (missing email or website).', 'claim-your-agency' );
	} else {
		$domain_message = __( 'No email or website provided to compare domains.', 'claim-your-agency' );
	}

	$agency_post   = $agency_post_id ? get_post( $agency_post_id ) : null;
	$listing_post  = $listing_id ? get_post( $listing_id ) : null;
	$agency_title  = $agency_post ? $agency_post->post_title : '';
	$agency_view   = $agency_post ? get_permalink( $agency_post_id ) : '';
	$listing_title = $listing_post ? $listing_post->post_title : '';
	$listing_view  = $listing_post ? get_permalink( $listing_id ) : '';
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'claim-your-agency' ); ?></th>
				<td><strong><?php echo esc_html( $status ? ucfirst( $status ) : 'Pending' ); ?></strong></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Email verified', 'claim-your-agency' ); ?></th>
				<td>
					<?php
					if ( $email_verified ) {
						echo '<span style="color:#046a38;font-weight:600;">' . esc_html__( 'Yes', 'claim-your-agency' ) . '</span>';
					} else {
						echo '<span style="color:#b32d2e;font-weight:600;">' . esc_html__( 'No', 'claim-your-agency' ) . '</span>';
					}
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Agency', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $agency_post ) : ?>
						<strong><?php echo esc_html( $agency_title ); ?></strong><br />
						<?php if ( $agency_view ) : ?>
							<a href="<?php echo esc_url( $agency_view ); ?>" target="_blank"><?php esc_html_e( 'View agency', 'claim-your-agency' ); ?></a>
						<?php endif; ?>
					<?php else : ?>
						<em><?php esc_html_e( 'No agency linked.', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Listing', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $listing_post ) : ?>
						<strong><?php echo esc_html( $listing_title ); ?></strong><br />
						<?php if ( $listing_view ) : ?>
							<a href="<?php echo esc_url( $listing_view ); ?>" target="_blank"><?php esc_html_e( 'View listing', 'claim-your-agency' ); ?></a>
						<?php endif; ?>
					<?php else : ?>
						<em><?php esc_html_e( 'No listing linked.', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Claimant', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $claimant_name ) : ?>
						<strong><?php echo esc_html( $claimant_name ); ?></strong><br />
					<?php endif; ?>
					<?php if ( $claimant_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $claimant_email ); ?>"><?php echo esc_html( $claimant_email ); ?></a>
					<?php else : ?>
						<em><?php esc_html_e( 'No email provided.', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Agency name (submitted)', 'claim-your-agency' ); ?></th>
				<td><?php echo $agency_name ? esc_html( $agency_name ) : '<em>' . esc_html__( 'Not provided', 'claim-your-agency' ) . '</em>'; ?></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Agency website (submitted)', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $agency_website ) : ?>
						<a href="<?php echo esc_url( ( strpos( $agency_website, 'http' ) === 0 ) ? $agency_website : 'https://' . $agency_website ); ?>" target="_blank">
							<?php echo esc_html( $agency_website ); ?>
						</a>
					<?php else : ?>
						<em><?php esc_html_e( 'Not provided', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Domain match hint', 'claim-your-agency' ); ?></th>
				<td>
					<p style="<?php echo esc_attr( $domain_style ); ?>">
						<?php echo esc_html( $domain_message ); ?>
					</p>
					<?php if ( $email_domain || $website_host ) : ?>
						<p style="margin-top:4px;font-size:12px;color:#646970;">
							<?php
							printf(
								/* translators: 1: email domain, 2: website host */
								esc_html__( 'Email domain: %1$s, Website host: %2$s', 'claim-your-agency' ),
								$email_domain ? esc_html( $email_domain ) : esc_html__( 'n/a', 'claim-your-agency' ),
								$website_host ? esc_html( $website_host ) : esc_html__( 'n/a', 'claim-your-agency' )
							);
							?>
						</p>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Staff/team page URL', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $proof_staff ) : ?>
						<a href="<?php echo esc_url( $proof_staff ); ?>" target="_blank"><?php echo esc_html( $proof_staff ); ?></a>
					<?php else : ?>
						<em><?php esc_html_e( 'Not provided', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Companies House / official registration URL', 'claim-your-agency' ); ?></th>
				<td>
					<?php if ( $proof_companies ) : ?>
						<a href="<?php echo esc_url( $proof_companies ); ?>" target="_blank"><?php echo esc_html( $proof_companies ); ?></a>
					<?php else : ?>
						<em><?php esc_html_e( 'Not provided', 'claim-your-agency' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Additional notes', 'claim-your-agency' ); ?></th>
				<td>
					<?php
					if ( $proof_notes ) {
						echo nl2br( esc_html( $proof_notes ) );
					} elseif ( $post->post_content ) {
						// Fallback to post content if used.
						echo nl2br( esc_html( $post->post_content ) );
					} else {
						echo '<em>' . esc_html__( 'No additional notes provided.', 'claim-your-agency' ) . '</em>';
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/**
 * Front-end Claim My Agency popup (Step 1: Start Verification).
 * Renders a modal and JS handler used by the "Claim this agency" button.
 */
function cya_output_claim_popup_js() {
	if ( is_admin() ) {
		return;
	}

	$ajax_url          = admin_url( 'admin-ajax.php' );
	$nonce             = wp_create_nonce( 'cya_claim_nonce' );
	$recaptcha_enabled = (int) get_option( 'cya_recaptcha_enabled', 0 );
	$recaptcha_sitekey = get_option( 'cya_recaptcha_site_key', '' );
	$callback_url      = get_option( 'cya_firebase_callback_url', '' );
	// Append a version query to bust browser cache when the SDK changes.
	$firebase_sdk_url  = plugins_url( 'firebase-sdk.js', __FILE__ ) . '?ver=2';
	?>
	<script type="module" src="<?php echo esc_url( $firebase_sdk_url ); ?>"></script>
	<style>
		.cya-claim-agency-button.cya-claim-disabled {
			opacity: 0.6;
			pointer-events: none;
			cursor: wait;
		}

		#cya-claim-modal {
			max-width: 420px;
			width: 80%;
			border-radius: 20px;
			max-height: 90vh;
			overflow-y: auto;
		}

		.cya-claim-body {
			padding: 16px 20px;
		}

		.cya-claim-input,
		.cya-claim-textarea {
			width: 100%;
			padding: 10px 14px;
			border-radius: 10px;
			border: 1px solid #dddddd;
			box-sizing: border-box;
		}

		@media (max-width: 600px) {
			#cya-claim-modal {
				width: 95%;
				max-width: 95%;
			}
			.cya-claim-body {
				padding: 12px 15px;
			}
		}
	</style>
	<div id="cya-claim-modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99998;"></div>
	<div id="cya-claim-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.2); z-index:99999;">
		<div style="padding:16px 20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
			<h2 style="margin:0; font-size:18px;"><?php esc_html_e( 'Claim this agency', 'claim-your-agency' ); ?></h2>
			<button type="button" id="cya-claim-close" style="background:none;border:0;font-size:20px;line-height:1;cursor:pointer;">&times;</button>
		</div>
		<div class="cya-claim-body">
			<div id="cya-claim-message" style="display:none; margin-bottom:12px; font-size:14px;"></div>
			<div id="cya-claim-loading" style="display:none; margin-bottom:12px; font-size:14px; color:#555;">
				<?php esc_html_e( 'Submitting your claim, please wait...', 'claim-your-agency' ); ?>
			</div>
			<div id="cya-claim-success" style="display:none; margin-bottom:12px; font-size:14px; text-align:center;">
				<p id="cya-claim-success-text" style="margin-bottom:12px;"></p>
				<button type="button" id="cya-claim-success-ok" class="button button-primary">
					<?php esc_html_e( 'OK', 'claim-your-agency' ); ?>
				</button>
			</div>
			<form id="cya-claim-form">
				<input type="hidden" name="action" value="cya_submit_claim" />
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
				<input type="hidden" name="agency_post_id" id="cya-agency-post-id" value="" />
				<input type="hidden" name="listing_id" id="cya-listing-id" value="" />

				<div style="margin-bottom:10px;">
					<label for="cya-claimant-name" style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Your name', 'claim-your-agency' ); ?></label>
					<input type="text" id="cya-claimant-name" name="claimant_name" class="cya-claim-input" required />
				</div>

				<div style="margin-bottom:10px;">
					<label for="cya-claimant-email" style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Work email', 'claim-your-agency' ); ?></label>
					<input type="email" id="cya-claimant-email" name="claimant_email" class="cya-claim-input" required />
				</div>

				<div style="margin-bottom:10px;">
					<label style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Agency name', 'claim-your-agency' ); ?></label>
					<input type="text" id="cya-agency-name" name="agency_name" class="cya-claim-input" readonly />
				</div>

				<div style="margin-bottom:10px;">
					<label style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Agency website', 'claim-your-agency' ); ?></label>
					<input type="text" id="cya-agency-website" name="agency_website" class="cya-claim-input" readonly/>
				</div>

				<div style="margin-bottom:10px;">
					<label for="cya-proof-staff" style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Staff/team page URL (optional)', 'claim-your-agency' ); ?></label>
					<input type="url" id="cya-proof-staff" name="proof_staff_page_url" class="cya-claim-input" />
				</div>

				<div style="margin-bottom:10px;">
					<label for="cya-proof-companies" style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Companies House / official registration URL (optional)', 'claim-your-agency' ); ?></label>
					<input type="url" id="cya-proof-companies" name="proof_companies_house_url" class="cya-claim-input" />
				</div>

				<div style="margin-bottom:10px;">
					<label for="cya-proof-notes" style="display:block; font-weight:600; margin-bottom:3px;"><?php esc_html_e( 'Anything else that helps us verify you (optional)', 'claim-your-agency' ); ?></label>
					<textarea id="cya-proof-notes" name="proof_notes" class="cya-claim-textarea" style="min-height:70px;"></textarea>
				</div>

				<div style="margin-bottom:12px;">
					<label>
						<input type="checkbox" id="cya-claim-consent" name="claim_consent" value="1" required />
						<?php esc_html_e( 'I confirm I am authorised to act on behalf of this agency.', 'claim-your-agency' ); ?>
					</label>
				</div>

				<?php if ( $recaptcha_enabled && ! empty( $recaptcha_sitekey ) ) : ?>
					<div style="margin-bottom:12px;">
						<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_sitekey ); ?>"></div>
					</div>
				<?php endif; ?>

				<div style="text-align:right;">
					<button type="submit" id="cya-claim-submit" class="btn btn-border btn-big">
						<?php esc_html_e( 'Submit claim', 'claim-your-agency' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>

	<?php if ( $recaptcha_enabled && ! empty( $recaptcha_sitekey ) ) : ?>
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<?php endif; ?>

	<script>
		(function() {
			var cyaAjaxUrl     = <?php echo wp_json_encode( $ajax_url ); ?>;
			var cyaAjaxNonce   = <?php echo wp_json_encode( $nonce ); ?>;
			var cyaCallbackUrl = <?php echo wp_json_encode( $callback_url ); ?>;
			var cyaModal      = document.getElementById('cya-claim-modal');
			var cyaBackdrop   = document.getElementById('cya-claim-modal-backdrop');
			var cyaForm       = document.getElementById('cya-claim-form');
			var cyaMsg        = document.getElementById('cya-claim-message');
			var cyaLoading    = document.getElementById('cya-claim-loading');
			var cyaSuccess    = document.getElementById('cya-claim-success');
			var cyaSuccessTxt = document.getElementById('cya-claim-success-text');
			var cyaSuccessOk  = document.getElementById('cya-claim-success-ok');
			var cyaCloseBtn   = document.getElementById('cya-claim-close');
			var agencyIdField = document.getElementById('cya-agency-post-id');
			var listingIdField= document.getElementById('cya-listing-id');
			var agencyNameEl  = document.getElementById('cya-agency-name');
			var agencyWebEl   = document.getElementById('cya-agency-website');

			function cyaOpenModal() {
				if (!cyaModal || !cyaBackdrop) return;
				cyaMsg.style.display = 'none';
				cyaMsg.textContent   = '';
				if (cyaLoading) {
					cyaLoading.style.display = 'none';
				}
				if (cyaSuccess) {
					cyaSuccess.style.display = 'none';
				}
				if (cyaForm) {
					cyaForm.style.display = 'block';
				}
				cyaModal.style.display    = 'block';
				cyaBackdrop.style.display = 'block';
			}

			function cyaCloseModal() {
				if (!cyaModal || !cyaBackdrop) return;
				cyaModal.style.display    = 'none';
				cyaBackdrop.style.display = 'none';
			}

			if (cyaCloseBtn) {
				cyaCloseBtn.addEventListener('click', cyaCloseModal);
			}
			if (cyaBackdrop) {
				cyaBackdrop.addEventListener('click', cyaCloseModal);
			}

			if (cyaSuccessOk) {
				cyaSuccessOk.addEventListener('click', function() {
					cyaCloseModal();
				});
			}

			window.bia_open_claim_agency_popup = function(buttonEl, agencyId, listingId) {
				if (!agencyId) {
					console.error('Claim My Agency: missing agencyId');
					return;
				}

				if (buttonEl) {
					// If already in loading state, do nothing.
					if (buttonEl.classList && buttonEl.classList.contains('cya-claim-disabled')) {
						return;
					}
					buttonEl.disabled = true;
					if (buttonEl.classList) {
						buttonEl.classList.add('cya-claim-disabled');
					}
				}

				if (agencyIdField && listingIdField) {
					agencyIdField.value  = agencyId;
					listingIdField.value = listingId || '';
				}
				// Fetch agency details to pre-fill name & website.
				var formData = new FormData();
				formData.append('action', 'cya_get_agency_details');
				formData.append('nonce', cyaAjaxNonce);
				formData.append('agency_post_id', agencyId);

				fetch(cyaAjaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				}).then(function(resp) {
					return resp.json();
				}).then(function(json) {
					if (json && json.success && json.data) {
						if (agencyNameEl && json.data.agency_name) {
							agencyNameEl.value = json.data.agency_name;
						}
						if (agencyWebEl && json.data.agency_website) {
							agencyWebEl.value = json.data.agency_website;
						}
					}
					cyaOpenModal();
					if (buttonEl) {
						buttonEl.disabled = false;
						if (buttonEl.classList) {
							buttonEl.classList.remove('cya-claim-disabled');
						}
					}
				}).catch(function() {
					// Even if details fail to load, still open modal.
					cyaOpenModal();
					if (buttonEl) {
						buttonEl.disabled = false;
						if (buttonEl.classList) {
							buttonEl.classList.remove('cya-claim-disabled');
						}
					}
				});
			};

			if (cyaForm) {
				cyaForm.addEventListener('submit', function(e) {
					e.preventDefault();

					if (!document.getElementById('cya-claim-consent').checked) {
						cyaMsg.style.display = 'block';
						cyaMsg.style.color   = '#b32d2e';
						cyaMsg.textContent   = '<?php echo esc_js( __( 'Please confirm you are authorised to act on behalf of this agency.', 'claim-your-agency' ) ); ?>';
						return;
					}

					var submitBtn = document.getElementById('cya-claim-submit');
					if (submitBtn) {
						submitBtn.disabled = true;
					}

					if (cyaMsg) {
						cyaMsg.style.display = 'none';
					}
					if (cyaSuccess) {
						cyaSuccess.style.display = 'none';
					}
					if (cyaLoading) {
						cyaLoading.style.display = 'block';
					}

					var formData = new FormData(cyaForm);

					fetch(cyaAjaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: formData
					}).then(function(resp) {
						return resp.json();
					}).then(function(json) {
						if (submitBtn) {
							submitBtn.disabled = false;
						}
						if (cyaLoading) {
							cyaLoading.style.display = 'none';
						}
						if (json && json.success) {
							// After successful claim, send a Firebase Email Link to verify the email identity,
							// but do NOT grant agency/dashboard access yet.
							var emailForLink = document.getElementById('cya-claimant-email') ? document.getElementById('cya-claimant-email').value : '';

							if (window.cyaFirebase && cyaCallbackUrl && emailForLink) {
								try {
									// Include claim_id in the callback URL so it works when the link is opened on another device (no localStorage).
									var callbackUrlWithClaim = cyaCallbackUrl;
									if (json.data && json.data.claim_id) {
										var sep = cyaCallbackUrl.indexOf('?') !== -1 ? '&' : '?';
										callbackUrlWithClaim = cyaCallbackUrl + sep + 'claim_id=' + encodeURIComponent(String(json.data.claim_id));
									}
									console.log('[CYA] Preparing to send Firebase Email Link', {
										email: emailForLink,
										callbackUrl: callbackUrlWithClaim,
										hasFirebase: !!window.cyaFirebase,
										sendFnType: typeof window.cyaFirebase.sendSignInLinkToEmail,
									});

									var actionCodeSettings = {
										url: callbackUrlWithClaim,
										handleCodeInApp: true
									};

									window.cyaFirebase.sendSignInLinkToEmail(emailForLink, actionCodeSettings).then(function() {
										console.log('[CYA] Firebase Email Link sendSignInLinkToEmail() resolved');
									}).catch(function(err) {
										console.error('[CYA] Failed to send Firebase sign-in link', err);
									});
								} catch (e) {
									console.error('[CYA] Firebase not available for Email Link', e);
								}
							}

							// Show success dialog with OK button.
							if (cyaForm) {
								cyaForm.reset();
								// Keep agency info if we have it in response.
								if (agencyNameEl) {
									agencyNameEl.value = json.data && json.data.agency_name ? json.data.agency_name : agencyNameEl.value;
								}
								if (agencyWebEl) {
									agencyWebEl.value = json.data && json.data.agency_website ? json.data.agency_website : agencyWebEl.value;
								}
								cyaForm.style.display = 'none';
							}
							if (cyaSuccess && cyaSuccessTxt) {
								cyaSuccessTxt.textContent = json.data && json.data.message ? json.data.message : '<?php echo esc_js( __( 'Your claim has been submitted. Please check your email for the next steps.', 'claim-your-agency' ) ); ?>';
								cyaSuccess.style.display = 'block';
							}
						} else {
							if (cyaMsg) {
								cyaMsg.style.display = 'block';
								cyaMsg.style.color = '#b32d2e';
								var err = (json && json.data && json.data.message) ? json.data.message : '<?php echo esc_js( __( 'There was a problem submitting your claim. Please try again later.', 'claim-your-agency' ) ); ?>';
								cyaMsg.textContent = err;
							}
						}
					}).catch(function() {
						if (submitBtn) {
							submitBtn.disabled = false;
						}
						if (cyaLoading) {
							cyaLoading.style.display = 'none';
						}
						if (cyaMsg) {
							cyaMsg.style.display = 'block';
							cyaMsg.style.color   = '#b32d2e';
							cyaMsg.textContent   = '<?php echo esc_js( __( 'Network error. Please try again.', 'claim-your-agency' ) ); ?>';
						}
					});
				});
			}
		})();
	</script>
	<?php
}
add_action( 'wp_footer', 'cya_output_claim_popup_js' );

/**
 * Helper: extract bare domain (no scheme, no www) from a URL/string.
 *
 * @param string $url Raw URL or domain.
 * @return string Normalised domain, e.g. "igproperty.co.uk".
 */
function cya_extract_domain( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	// Ensure we have a scheme so wp_parse_url can detect host reliably.
	$probe = ( strpos( $url, '://' ) === false ) ? 'http://' . $url : $url;
	$host  = wp_parse_url( $probe, PHP_URL_HOST );

	if ( ! $host ) {
		$host = $url;
	}

	// Strip leading www.
	$host = preg_replace( '/^www\./i', '', $host );

	return $host;
}

/**
 * AJAX: Get agency details (name, website) for the popup.
 */
function cya_get_agency_details() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya_claim_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'claim-your-agency' ) ) );
	}

	$agency_post_id = isset( $_POST['agency_post_id'] ) ? (int) $_POST['agency_post_id'] : 0;
	if ( ! $agency_post_id ) {
		wp_send_json_error( array( 'message' => __( 'Missing agency ID.', 'claim-your-agency' ) ) );
	}

	$agency_post = get_post( $agency_post_id );
	if ( ! $agency_post ) {
		wp_send_json_error( array( 'message' => __( 'Agency not found.', 'claim-your-agency' ) ) );
	}

	$agency_name    = $agency_post->post_title;
	$raw_website    = get_post_meta( $agency_post_id, 'agency_website', true );
	$agency_website = cya_extract_domain( $raw_website );

	wp_send_json_success(
		array(
			'agency_name'    => $agency_name,
			'agency_website' => $agency_website,
		)
	);
}
add_action( 'wp_ajax_cya_get_agency_details', 'cya_get_agency_details' );
add_action( 'wp_ajax_nopriv_cya_get_agency_details', 'cya_get_agency_details' );

/**
 * AJAX: Submit a new agency claim (Step 1 only – no Firebase yet).
 */
function cya_submit_claim() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya_claim_nonce' ) ) {
		cya_log( 'Submit claim failed: invalid nonce.' );
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'claim-your-agency' ) ) );
	}

	$agency_post_id = isset( $_POST['agency_post_id'] ) ? (int) $_POST['agency_post_id'] : 0;
	$listing_id     = isset( $_POST['listing_id'] ) ? (int) $_POST['listing_id'] : 0;

	if ( ! $agency_post_id ) {
		cya_log( 'Submit claim failed: missing agency ID.' );
		wp_send_json_error( array( 'message' => __( 'Missing agency ID.', 'claim-your-agency' ) ) );
	}

	$claimant_name  = isset( $_POST['claimant_name'] ) ? sanitize_text_field( wp_unslash( $_POST['claimant_name'] ) ) : '';
	$claimant_email = isset( $_POST['claimant_email'] ) ? sanitize_email( wp_unslash( $_POST['claimant_email'] ) ) : '';
	$agency_name    = isset( $_POST['agency_name'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_name'] ) ) : '';
	$agency_website = isset( $_POST['agency_website'] ) ? esc_url_raw( wp_unslash( $_POST['agency_website'] ) ) : '';

	$proof_staff    = isset( $_POST['proof_staff_page_url'] ) ? esc_url_raw( wp_unslash( $_POST['proof_staff_page_url'] ) ) : '';
	$proof_companies= isset( $_POST['proof_companies_house_url'] ) ? esc_url_raw( wp_unslash( $_POST['proof_companies_house_url'] ) ) : '';
	$proof_notes    = isset( $_POST['proof_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['proof_notes'] ) ) : '';

	if ( $claimant_name === '' || $claimant_email === '' ) {
		cya_log(
			'Submit claim failed: missing required fields.',
			array(
				'agency_post_id' => $agency_post_id,
				'listing_id'     => $listing_id,
				'claimant_email' => $claimant_email,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Name and work email are required.', 'claim-your-agency' ) ) );
	}

	// Optional: validate consent checkbox.
	if ( empty( $_POST['claim_consent'] ) ) {
		cya_log(
			'Submit claim failed: consent not confirmed.',
			array(
				'agency_post_id' => $agency_post_id,
				'listing_id'     => $listing_id,
				'claimant_email' => $claimant_email,
			)
		);
		wp_send_json_error( array( 'message' => __( 'You must confirm you are authorised to act on behalf of this agency.', 'claim-your-agency' ) ) );
	}

	// Optional: reCAPTCHA verification (only if explicitly enabled).
	$recaptcha_enabled = (int) get_option( 'cya_recaptcha_enabled', 0 );
	$recaptcha_secret  = get_option( 'cya_recaptcha_secret_key', '' );

	if ( $recaptcha_enabled && $recaptcha_secret ) {
		$recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
		if ( ! $recaptcha_response ) {
			cya_log(
				'Submit claim failed: missing reCAPTCHA response.',
				array(
					'agency_post_id' => $agency_post_id,
					'listing_id'     => $listing_id,
					'claimant_email' => $claimant_email,
				)
			);
			wp_send_json_error( array( 'message' => __( 'reCAPTCHA verification failed. Please try again.', 'claim-your-agency' ) ) );
		}

		$verify = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $recaptcha_secret,
					'response' => $recaptcha_response,
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $verify ) ) {
			cya_log(
				'Submit claim failed: could not verify reCAPTCHA.',
				array(
					'agency_post_id' => $agency_post_id,
					'listing_id'     => $listing_id,
					'claimant_email' => $claimant_email,
					'error'          => $verify->get_error_message(),
				)
			);
			wp_send_json_error( array( 'message' => __( 'Could not verify reCAPTCHA. Please try again.', 'claim-your-agency' ) ) );
		}

		$verify_body = json_decode( wp_remote_retrieve_body( $verify ), true );
		if ( empty( $verify_body['success'] ) ) {
			cya_log(
				'Submit claim failed: reCAPTCHA response invalid.',
				array(
					'agency_post_id' => $agency_post_id,
					'listing_id'     => $listing_id,
					'claimant_email' => $claimant_email,
					'verify_body'    => $verify_body,
				)
			);
			wp_send_json_error( array( 'message' => __( 'reCAPTCHA failed. Please try again.', 'claim-your-agency' ) ) );
		}
	}

	// Create the claim CPT entry.
	$title = $agency_name ? sprintf( __( 'Claim for %s', 'claim-your-agency' ), $agency_name ) : __( 'Agency Claim', 'claim-your-agency' );

	$postarr = array(
		'post_title'   => $title,
		'post_content' => $proof_notes,
		'post_status'  => 'publish',
		'post_type'    => 'cya_claim',
	);

	$claim_id = wp_insert_post( $postarr, true );
	if ( is_wp_error( $claim_id ) ) {
		cya_log(
			'Submit claim failed: could not create claim post.',
			array(
				'agency_post_id' => $agency_post_id,
				'listing_id'     => $listing_id,
				'claimant_email' => $claimant_email,
				'error'          => $claim_id->get_error_message(),
			)
		);
		wp_send_json_error( array( 'message' => __( 'Failed to create claim. Please try again later.', 'claim-your-agency' ) ) );
	}

	$claim_id = (int) $claim_id;

	// Store claim meta.
	update_post_meta( $claim_id, 'cya_status', 'pending' );
	update_post_meta( $claim_id, 'cya_email_verified', 0 );
	update_post_meta( $claim_id, 'agency_post_id', $agency_post_id );
	if ( $listing_id ) {
		update_post_meta( $claim_id, 'listing_id', $listing_id );
	}
	update_post_meta( $claim_id, 'claimant_name', $claimant_name );
	update_post_meta( $claim_id, 'claimant_email', $claimant_email );
	update_post_meta( $claim_id, 'agency_name', $agency_name );
	update_post_meta( $claim_id, 'agency_website', $agency_website );
	if ( $proof_staff ) {
		update_post_meta( $claim_id, 'proof_staff_page_url', $proof_staff );
	}
	if ( $proof_companies ) {
		update_post_meta( $claim_id, 'proof_companies_house_url', $proof_companies );
	}
	if ( $proof_notes ) {
		update_post_meta( $claim_id, 'proof_notes', $proof_notes );
	}

	cya_log(
		'Claim submitted successfully.',
		array(
			'claim_id'       => $claim_id,
			'agency_post_id' => $agency_post_id,
			'listing_id'     => $listing_id,
			'claimant_email' => $claimant_email,
		)
	);

	wp_send_json_success(
		array(
			'message'        => __( 'Your claim has been submitted. Please check your email after we start verification.', 'claim-your-agency' ),
			'agency_name'    => $agency_name,
			'agency_website' => $agency_website,
			'claim_id'       => $claim_id,
			'claimant_email' => $claimant_email,
		)
	);
}
add_action( 'wp_ajax_cya_submit_claim', 'cya_submit_claim' );
add_action( 'wp_ajax_nopriv_cya_submit_claim', 'cya_submit_claim' );

/**
 * AJAX: Mark a claim's email as verified after Firebase Email Link completion.
 */
function cya_mark_email_verified() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya_claim_nonce' ) ) {
		cya_log( 'Mark email verified failed: invalid nonce.' );
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'claim-your-agency' ) ) );
	}

	$claim_id       = isset( $_POST['claim_id'] ) ? (int) $_POST['claim_id'] : 0;
	$verified_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $verified_email ) {
		cya_log( 'Mark email verified failed: missing email.', array( 'claim_id' => $claim_id ) );
		wp_send_json_error( array( 'message' => __( 'Missing claim or email.', 'claim-your-agency' ) ) );
	}

	// If no claim_id (e.g. user opened magic link on another device), find most recent pending claim for this email.
	if ( ! $claim_id ) {
		$found = get_posts(
			array(
				'post_type'      => 'cya_claim',
				'post_status'     => 'any',
				'posts_per_page'  => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => 'claimant_email',
						'value'   => $verified_email,
						'compare' => '=',
					),
					array(
						'key'     => 'cya_status',
						'value'   => 'pending',
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
			)
		);
		$claim_id = ! empty( $found ) ? (int) $found[0] : 0;
		if ( $claim_id ) {
			cya_log(
				'Mark email verified: claim_id resolved by email lookup.',
				array( 'claim_id' => $claim_id, 'verified_email' => $verified_email )
			);
		}
	}

	if ( ! $claim_id ) {
		cya_log(
			'Mark email verified failed: no claim_id and no pending claim found for email.',
			array( 'verified_email' => $verified_email )
		);
		wp_send_json_error( array( 'message' => __( 'Missing claim or email.', 'claim-your-agency' ) ) );
	}

	$post = get_post( $claim_id );
	if ( ! $post || $post->post_type !== 'cya_claim' ) {
		cya_log(
			'Mark email verified failed: invalid claim.',
			array(
				'claim_id' => $claim_id,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Invalid claim.', 'claim-your-agency' ) ) );
	}

	$claimant_email = get_post_meta( $claim_id, 'claimant_email', true );
	if ( ! $claimant_email || strtolower( $claimant_email ) !== strtolower( $verified_email ) ) {
		cya_log(
			'Mark email verified failed: email mismatch.',
			array(
				'claim_id'       => $claim_id,
				'claimant_email' => $claimant_email,
				'verified_email' => $verified_email,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Email does not match the claim.', 'claim-your-agency' ) ) );
	}

	update_post_meta( $claim_id, 'cya_email_verified', 1 );
	update_post_meta( $claim_id, 'cya_email_verified_at', current_time( 'mysql' ) );

	cya_log(
		'Claim email marked as verified.',
		array(
			'claim_id'       => $claim_id,
			'verified_email' => $verified_email,
		)
	);

	wp_send_json_success( array( 'message' => __( 'Email verified for this claim.', 'claim-your-agency' ) ) );
}
add_action( 'wp_ajax_cya_mark_email_verified', 'cya_mark_email_verified' );
add_action( 'wp_ajax_nopriv_cya_mark_email_verified', 'cya_mark_email_verified' );

/**
 * Check if a user is an approved agency owner (owns at least one gsli_agency or has Agency package role).
 *
 * @param int $user_id WordPress user ID.
 * @return bool
 */
function cya_user_is_agency_owner( $user_id ) {
	$agency_posts = get_posts(
		array(
			'post_type'      => 'gsli_agency',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'agency_owner',
					'value' => (int) $user_id,
					'compare' => '=',
				),
			),
		)
	);
	if ( ! empty( $agency_posts ) ) {
		return true;
	}
	$agency_package = cya_get_agency_package_from_listinghub();
	if ( $agency_package ) {
		$user = get_user_by( 'id', $user_id );
		if ( $user && in_array( $agency_package['role'], (array) $user->roles, true ) ) {
			return true;
		}
	}
	return false;
}

/**
 * AJAX: Log in an agency owner after Firebase Email Link sign-in (no password).
 * Called from the callback page when there is no claim_id (login flow, not verification).
 */
function cya_agency_wp_login() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya_claim_nonce' ) ) {
		cya_log( 'Agency WP login failed: invalid nonce.' );
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'claim-your-agency' ) ) );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! $email ) {
		cya_log( 'Agency WP login failed: missing email.' );
		wp_send_json_error( array( 'message' => __( 'Email is required.', 'claim-your-agency' ) ) );
	}

	$user = get_user_by( 'email', $email );
	if ( ! $user ) {
		cya_log( 'Agency WP login failed: no user for email.', array( 'email' => $email ) );
		wp_send_json_error( array( 'message' => __( 'No agency account found for this email. Your claim may still be pending approval.', 'claim-your-agency' ) ) );
	}

	if ( ! cya_user_is_agency_owner( $user->ID ) ) {
		cya_log( 'Agency WP login failed: user is not agency owner.', array( 'user_id' => $user->ID, 'email' => $email ) );
		wp_send_json_error( array( 'message' => __( 'No agency account found for this email. Your claim may still be pending approval.', 'claim-your-agency' ) ) );
	}

	wp_clear_auth_cookie();
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	do_action( 'wp_login', $user->user_login, $user );

	$redirect = get_option( 'cya_agency_dashboard_url', '' );
	if ( ! $redirect ) {
		$redirect = home_url( '/' );
	}

	cya_log( 'Agency WP login success.', array( 'user_id' => $user->ID, 'email' => $email ) );

	wp_send_json_success( array( 'redirect' => $redirect ) );
}
add_action( 'wp_ajax_cya_agency_wp_login', 'cya_agency_wp_login' );
add_action( 'wp_ajax_nopriv_cya_agency_wp_login', 'cya_agency_wp_login' );

