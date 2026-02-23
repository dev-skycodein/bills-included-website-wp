<?php
/**
 * Create New Password – same design as login/forgot, used when user clicks reset link in email.
 * Expects GET (or POST) action=rp&key=...&login=...
 */
$reset_key   = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '';
$reset_login = isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : '';
$reset_user  = null;
$reset_error = '';
$reset_done  = false;
$profile_page_id = get_option( 'epjblistinghub_profile_page' );

if ( $reset_key && $reset_login ) {
	$reset_user = check_password_reset_key( $reset_key, $reset_login );
	if ( is_wp_error( $reset_user ) ) {
		$reset_error = $reset_user->get_error_message();
		if ( empty( $reset_error ) ) {
			$reset_error = __( 'This link is invalid or has expired. Please request a new password reset.', 'listinghub' );
		}
		$reset_user = null;
	}
}

// Process form submission
if ( $reset_user && isset( $_POST['listinghub_reset_password_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['listinghub_reset_password_nonce'] ) ), 'listinghub_reset_password' ) ) {
	$pass1 = isset( $_POST['pass1'] ) ? $_POST['pass1'] : '';
	$pass2 = isset( $_POST['pass2'] ) ? $_POST['pass2'] : '';
	if ( strlen( $pass1 ) < 6 ) {
		$reset_error = __( 'Password must be at least 6 characters long.', 'listinghub' );
	} elseif ( $pass1 !== $pass2 ) {
		$reset_error = __( 'Passwords do not match.', 'listinghub' );
	} else {
		reset_password( $reset_user, $pass1 );
		$reset_done = true;
		$redirect_url = $profile_page_id ? get_permalink( (int) $profile_page_id ) : home_url( '/' );
		$redirect_url = add_query_arg( 'message-success', urlencode( __( 'Your password has been reset. You can now log in with your new password.', 'listinghub' ) ), $redirect_url );
		wp_safe_redirect( $redirect_url );
		exit;
	}
}

wp_enqueue_script( 'jquery' );
wp_enqueue_style( 'bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css' );
wp_enqueue_style( 'listinghub_style-login', ep_listinghub_URLPATH . 'admin/files/css/login.css' );
?>
<style>
  #login-2 .content-real{ max-width: 100% !important; background: transparent !important; }
  #login-2 h3{ text-align: left !important; margin: 40px 0 20px !important; }
  .login-row { display: flex; justify-content: space-between; gap: 10%; align-items: center; }
  .login-colmun{ width: 45%; }
  img.login-logo { max-width: 60px; }
  #login-2 button.uppercase{ width: 160px; padding: 12px !important; }
  #login-2 .btn-custom{ font-family: 'Poppins'; font-weight: 300 !important; }
  #login-2 .content-real{ padding: 30px 20px 50px !important; }
  #login-2 .form-control-solid { background: transparent !important; }
  #login-2 .form-control-solid:focus { border: unset !important; border-bottom: 1px solid #808080 !important; }
  .login-image{ border-radius: 10px !important; }
  @media(max-width: 767px){
    .content-real > .login-row{ flex-direction: column; }
    .content-real > .login-row .login-colmun{ width: 100%; }
    .hide-mobile { display: none !important; }
  }
  @media(max-width: 500px){
    .form-actions.login-row{ flex-direction: column; }
    .form-actions.login-row .login-colmun{ justify-content: left !important; }
  }
</style>
<div id="login-2" class="bootstrap-wrapper">
  <div class="menu-toggler sidebar-toggler"></div>
  <div class="content-real">
    <div class="login-row">
      <div class="login-colmun">
        <img class="login-logo" src="https://thebillsincluded.com/wp-content/uploads/2025/06/logo.png" alt="">
        <?php if ( ! $reset_key || ! $reset_login ) : ?>
          <div class="alert alert-warning">
            <?php esc_html_e( 'Missing reset link parameters. Please use the link from your email.', 'listinghub' ); ?>
          </div>
          <a href="<?php echo esc_url( $profile_page_id ? get_permalink( (int) $profile_page_id ) : home_url( '/' ) ); ?>" class="btn btn-custom uppercase"><?php esc_html_e( 'Back to login', 'listinghub' ); ?></a>
        <?php elseif ( $reset_error && ! isset( $_POST['listinghub_reset_password_nonce'] ) ) : ?>
          <div class="alert alert-danger"><?php echo esc_html( $reset_error ); ?></div>
          <a href="<?php echo esc_url( $profile_page_id ? get_permalink( (int) $profile_page_id ) : home_url( '/' ) ); ?>" class="btn btn-custom uppercase"><?php esc_html_e( 'Back to login', 'listinghub' ); ?></a>
        <?php else : ?>
          <form id="reset-password-form" class="login-form" method="post" action="">
            <?php wp_nonce_field( 'listinghub_reset_password', 'listinghub_reset_password_nonce' ); ?>
            <input type="hidden" name="key" value="<?php echo esc_attr( $reset_key ); ?>">
            <input type="hidden" name="login" value="<?php echo esc_attr( $reset_login ); ?>">
            <h3 class="form-title"><?php esc_html_e( 'Create New Password', 'listinghub' ); ?></h3>
            <?php if ( $reset_error && isset( $_POST['listinghub_reset_password_nonce'] ) ) : ?>
              <div class="alert alert-danger"><?php echo esc_html( $reset_error ); ?></div>
            <?php endif; ?>
            <?php
            $display_name = $reset_user ? $reset_user->display_name : '';
            $intro = $display_name
              ? sprintf( __( '%s, your new password must be different from any of your previous passwords.', 'listinghub' ), esc_html( $display_name ) )
              : __( 'Your new password must be different from any of your previous passwords.', 'listinghub' );
            ?>
            <p class="margin-b-30"><?php echo esc_html( $intro ); ?></p>
            <div class="form-group">
              <label class="control-label"><?php esc_html_e( 'New Password', 'listinghub' ); ?></label>
              <div class="password-field">
                <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Enter Password', 'listinghub' ); ?>" name="pass1" id="pass1" required minlength="6"/>
                <div class="eye-icons">
                  <i class="fa fa-eye" aria-hidden="true"></i>
                  <i class="fa fa-eye-slash" aria-hidden="true"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label"><?php esc_html_e( 'Confirm Password', 'listinghub' ); ?></label>
              <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Re-enter Password', 'listinghub' ); ?>" name="pass2" id="pass2" required minlength="6"/>
            </div>
            <div class="form-actions login-row">
              <div class="login-colmun">
                <button type="submit" class="btn btn-custom uppercase pull-left"><?php esc_html_e( 'Reset Password', 'listinghub' ); ?></button>
              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
      <div class="login-colmun hide-mobile" style="display: flex;justify-content: center;">
        <img class="login-image" src="https://thebillsincluded.com/wp-content/uploads/2025/06/my-account.jpg" alt="">
      </div>
    </div>
  </div>
</div>
<?php
if ( $reset_user && ! $reset_done ) {
  wp_enqueue_script( 'listinghub_login', ep_listinghub_URLPATH . 'admin/files/js/login.js' );
  // Reuse login.js password visibility toggle for #pass1
  ?>
  <script>
  jQuery(document).ready(function($){
    $('.password-field').on('click', '.fa-eye', function(){ $(this).closest('.password-field').find('input').attr('type','text'); $(this).hide().siblings('.fa-eye-slash').show(); });
    $('.password-field').on('click', '.fa-eye-slash', function(){ $(this).closest('.password-field').find('input').attr('type','password'); $(this).hide().siblings('.fa-eye').show(); });
  });
  </script>
  <?php
}
