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
	} elseif ( $action === 'reject' ) {
		update_post_meta( $post_id, 'cya_status', 'rejected' );
	}

	// Redirect back to the list table or edit screen to avoid resubmission.
	wp_safe_redirect( remove_query_arg( array( 'cya_action', '_wpnonce' ) ) );
	exit;
}
add_action( 'admin_init', 'cya_handle_claim_status_change' );

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
	$callback_url= get_option( 'cya_firebase_callback_url', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Claim Your Agency – Settings', 'claim-your-agency' ); ?></h1>

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
 * Usage: create a WP page (e.g. /agency-login-callback) and place [agency_login_callback] in its content.
 * This will load the firebase-sdk.js module so the page is ready for Email Link completion logic.
 */
function cya_render_agency_login_callback() {
	$firebase_sdk_url = plugins_url( 'firebase-sdk.js', __FILE__ );

	ob_start();
	?>
	<div id="cya-agency-login-callback-root"></div>
	<script type="module" src="<?php echo esc_url( $firebase_sdk_url ); ?>"></script>
	<script>
		(function() {
			var cyaAjaxUrl    = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var cyaAjaxNonce  = <?php echo wp_json_encode( wp_create_nonce( 'cya_claim_nonce' ) ); ?>;

			function getQueryParam(name) {
				var params = new URLSearchParams(window.location.search);
				return params.get(name);
			}

			document.addEventListener('DOMContentLoaded', function() {
				if (!window.cyaFirebase || !window.cyaFirebase.auth || !window.cyaFirebase.isSignInWithEmailLink || !window.cyaFirebase.signInWithEmailLink) {
					return;
				}

				var auth = window.cyaFirebase.auth;

				if (!window.cyaFirebase.isSignInWithEmailLink(window.location.href)) {
					return;
				}

				var email = window.localStorage.getItem('cya_email_for_signin');
				if (!email) {
					email = window.prompt('Please confirm your email to finish login');
				}
				if (!email) {
					return;
				}

				window.cyaFirebase.signInWithEmailLink(email, window.location.href)
					.then(function() {
						// Mark the claim's email as verified, if claim_id is available in URL.
						var claimId = getQueryParam('claim_id') || window.localStorage.getItem('cya_claim_id');
						if (claimId) {
							var formData = new FormData();
							formData.append('action', 'cya_mark_email_verified');
							formData.append('nonce', cyaAjaxNonce);
							formData.append('claim_id', claimId);
							formData.append('email', email);

							fetch(cyaAjaxUrl, {
								method: 'POST',
								credentials: 'same-origin',
								body: formData
							}).then(function(resp) {
								return resp.json();
							}).then(function(json) {
								if (json && json.success) {
									console.log('Claim email marked verified');
								} else {
									console.warn('Could not mark claim verified', json);
								}
							}).catch(function(err) {
								console.error('Error marking claim verified', err);
							});
						}

						// TODO: in later steps, map this Firebase user to a WP user and redirect to /agency-admin.
						var root = document.getElementById('cya-agency-login-callback-root');
						if (root) {
							root.innerHTML = '<p><?php echo esc_js( __( 'Your email has been verified. An admin will review your claim shortly.', 'claim-your-agency' ) ); ?></p>';
						}
					})
					.catch(function(error) {
						console.error('Error completing Email Link sign-in', error);
						var root = document.getElementById('cya-agency-login-callback-root');
						if (root) {
							root.innerHTML = '<p style="color:#b32d2e;"><?php echo esc_js( __( 'There was a problem verifying your email. Please try again or contact support.', 'claim-your-agency' ) ); ?></p>';
						}
					});
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}

function cya_register_shortcodes() {
	add_shortcode( 'agency_login_callback', 'cya_render_agency_login_callback' );
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
	$firebase_sdk_url  = plugins_url( 'firebase-sdk.js', __FILE__ );
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
									window.cyaFirebase.sendSignInLinkToEmail(emailForLink, {
										url: cyaCallbackUrl,
										handleCodeInApp: true
									}).then(function() {
										// Store email and claim id locally to help complete sign-in on this device.
										try {
											window.localStorage.setItem('cya_email_for_signin', emailForLink);
											if (json.data && json.data.claim_id) {
												window.localStorage.setItem('cya_claim_id', String(json.data.claim_id));
											}
										} catch (e) {}
									}).catch(function(err) {
										console.error('Failed to send Firebase sign-in link', err);
									});
								} catch (e) {
									console.error('Firebase not available for Email Link', e);
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
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'claim-your-agency' ) ) );
	}

	$agency_post_id = isset( $_POST['agency_post_id'] ) ? (int) $_POST['agency_post_id'] : 0;
	$listing_id     = isset( $_POST['listing_id'] ) ? (int) $_POST['listing_id'] : 0;

	if ( ! $agency_post_id ) {
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
		wp_send_json_error( array( 'message' => __( 'Name and work email are required.', 'claim-your-agency' ) ) );
	}

	// Optional: validate consent checkbox.
	if ( empty( $_POST['claim_consent'] ) ) {
		wp_send_json_error( array( 'message' => __( 'You must confirm you are authorised to act on behalf of this agency.', 'claim-your-agency' ) ) );
	}

	// Optional: reCAPTCHA verification (only if explicitly enabled).
	$recaptcha_enabled = (int) get_option( 'cya_recaptcha_enabled', 0 );
	$recaptcha_secret  = get_option( 'cya_recaptcha_secret_key', '' );

	if ( $recaptcha_enabled && $recaptcha_secret ) {
		$recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
		if ( ! $recaptcha_response ) {
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
			wp_send_json_error( array( 'message' => __( 'Could not verify reCAPTCHA. Please try again.', 'claim-your-agency' ) ) );
		}

		$verify_body = json_decode( wp_remote_retrieve_body( $verify ), true );
		if ( empty( $verify_body['success'] ) ) {
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
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'claim-your-agency' ) ) );
	}

	$claim_id       = isset( $_POST['claim_id'] ) ? (int) $_POST['claim_id'] : 0;
	$verified_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $claim_id || ! $verified_email ) {
		wp_send_json_error( array( 'message' => __( 'Missing claim or email.', 'claim-your-agency' ) ) );
	}

	$post = get_post( $claim_id );
	if ( ! $post || $post->post_type !== 'cya_claim' ) {
		wp_send_json_error( array( 'message' => __( 'Invalid claim.', 'claim-your-agency' ) ) );
	}

	$claimant_email = get_post_meta( $claim_id, 'claimant_email', true );
	if ( ! $claimant_email || strtolower( $claimant_email ) !== strtolower( $verified_email ) ) {
		wp_send_json_error( array( 'message' => __( 'Email does not match the claim.', 'claim-your-agency' ) ) );
	}

	update_post_meta( $claim_id, 'cya_email_verified', 1 );
	update_post_meta( $claim_id, 'cya_email_verified_at', current_time( 'mysql' ) );

	wp_send_json_success( array( 'message' => __( 'Email verified for this claim.', 'claim-your-agency' ) ) );
}
add_action( 'wp_ajax_cya_mark_email_verified', 'cya_mark_email_verified' );
add_action( 'wp_ajax_nopriv_cya_mark_email_verified', 'cya_mark_email_verified' );

