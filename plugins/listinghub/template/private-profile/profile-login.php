






 
    
    <?php
wp_enqueue_script("jquery");
wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
wp_enqueue_style('listinghub_style-login', ep_listinghub_URLPATH . 'admin/files/css/login.css');

?>
<style>
  #login-2 .content-real{
    max-width: 100% !important;
    background: transparent !important;
  }
  #login-2 h3{
    text-align: left !important;
    margin: 40px 0 20px !important;
  }
  .login-row {
    display: flex;
    justify-content: space-between;
    gap: 10%;
    align-items: center;
    }
    .login-colmun{
      width: 45%;
    }
    img.login-logo {
        max-width: 60px;
    }
    #login-2 button.uppercase{
        width: 160px;
        padding: 12px !important;
    }
    #login-2 a#register-btn{
      padding: 14px !important;
    }
    #login-2 .btn-custom{
      font-family: 'Poppins';
      font-weight: 300 !important;
    }
    #login-2 .content-real{
      padding: 30px 20px 50px !important;
    }
    #login-2 .form-control-solid {
        background: transparent !important;
    }
    #login-2 .form-control-solid:focus {
        border: unset !important;
        border-bottom: 1px solid #808080 !important;
    }
    .login-image{
      border-radius: 10px !important;
    }
    @media(max-width: 767px){
      .content-real > .login-row{
        flex-direction: column;
      }
      .content-real > .login-row .login-colmun{
        width: 100%;
      }
      .hide-mobile {
            display: none !important;
        }
        #login-2 .login-form{
            margin-bottom: 0 !important;
        }
    }
    @media(max-width: 500px){
      .form-actions.login-row{
        flex-direction: column;
      }
      .form-actions.login-row .login-colmun{
        justify-content: left !important;
      }
    }
</style>
  <div id="login-2" class="bootstrap-wrapper">
   <div class="menu-toggler sidebar-toggler">
   </div>   
   <div class="content-real">
  
  <div class="login-row">

    <div class="login-colmun">
    <img class="login-logo" src="https://thebillsincluded.com/wp-content/uploads/2025/06/logo.png">
    
    <form id="login_form" class="login-form" action="" method="post">
      <h3 class="form-title">Welcome back,<br>Please enter your details</h3>
      <?php
        if(isset($_REQUEST['message-success'])){?>
        <div class="row alert alert-success alert-dismissable" id='loading-2'><a class="panel-close close" data-dismiss="alert">x</a> <?php  echo $_GET['message-success'] ?></div>
        <?php
        }
						?>
      <div class="display-hide" id="error_message">

      </div>
      <div class="form-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        <label class="control-label visible-ie8 visible-ie9"><?php   esc_html_e('Username','listinghub');?></label>
        <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Username" name="username" id="username"/>
      </div>
      <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9"><?php   esc_html_e('Password','listinghub');?></label>
        <div class="password-field">
          <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password" id="password"/>
          <div class="eye-icons">
              <i class="fa fa-eye" aria-hidden="true"></i>
              <i class="fa fa-eye-slash" aria-hidden="true"></i>
          </div>
        </div>
        <p class="pull-left margin-20 para col-md-12">
        <a href="javascript:;" class="forgot-link">I've forgotten my password </a>
        </p>
      </div>
      <div class="form-actions login-row">
      <div class="login-colmun">
        <button type="button" class="btn btn-custom uppercase pull-left" onclick="return listinghub_chack_login();" ><?php   esc_html_e('Login','listinghub');?></button>
      </div>
      <div class="login-colmun" style="display: flex;justify-content: end;">
      <a href="https://thebillsincluded.com/register-your-account/" id="register-btn" class="btn btn-custom uppercase pull-left">
            <?php esc_html_e('Create an account','listinghub'); ?>
        </a>
      </div>
        
      </div>
    <div class="create-account">
    <p>
        
    </p>
</div>


    </form>
    
    <form id="forget-password" name="forget-password" class="forget-form" action="" method="post" >
    <button type="button" id="back-btn" style="margin-top: 20px !important;" class="btn btn-border"><?php   esc_html_e('Back','listinghub');?> </button>  
    <h3 style="margin-top: 20px !important;"><?php   esc_html_e('Forgot Password','listinghub');?>  </h3>
	  <div id="forget_message">
		<p>
        <?php   esc_html_e('Enter your email address','listinghub');?>
      </p>

      </div>
      <div class="form-group">
        <input class="form-control form-control-solid placeholder-no-fix" type="text"  placeholder="Email" name="forget_email" id="forget_email"/>
      </div>
      <div class="">
        <button type="button" onclick="return listinghub_forget_pass();"  class="btn btn-custom uppercase pull-right margin-b-30"><?php   esc_html_e('Submit','listinghub');?> </button>
      </div>
    </form>
    </div>

    <div class="login-colmun hide-mobile" style="display: flex;justify-content: center;">
      <img class="login-image" src="https://thebillsincluded.com/wp-content/uploads/2025/06/my-account.jpg">
    </div>

    </div>
    
  </div>
    </div>
<?php
wp_enqueue_script('listinghub_login', ep_listinghub_URLPATH . 'admin/files/js/login.js');
wp_localize_script('listinghub_login', 'real_data', array(
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
		'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
		'current_user_id'	=>get_current_user_id(),
		'forget_sent'=> esc_html__('Password Sent. Please check your email.','listinghub'),
		'login_error'=> esc_html__('Invalid Username & Password.','listinghub'),
		'login_validator'=> esc_html__('Enter Username & Password.','listinghub'),
		'forget_validator'=> esc_html__('Enter Email Address','listinghub'),
		
		) );
  
?>	
  