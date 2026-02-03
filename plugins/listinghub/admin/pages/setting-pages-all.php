
	<form class="form-horizontal" role="form"  name='listinghub_page_settings' id='listinghub_page_settings'>
		<?php
			$price_table=get_option('epjblistinghub_price_table'); 
			$registration=get_option('epjblistinghub_registration'); 
			$profile_page=get_option('epjblistinghub_profile_page'); 
			$login_page=get_option('epjblistinghub_login_page');  										
			$thank_you=get_option('epjblistinghub_thank_you_page'); 	
			$args = array(
			'child_of'     => 0,
			'sort_order'   => 'ASC',
			'sort_column'  => 'post_title',
			'hierarchical' => 1,															
			'post_type' => 'page'
			);
		?>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'Price Listing', 'listinghub' );?> : </label>
			<div class="col-md-10 ">
				
					<?php
						if ( $pages = get_pages( $args ) ){
							echo "<select id='pricing_page' name='pricing_page' class='form-control '>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($price_table==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
					<?php
						$reg_page= get_permalink( $price_table); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"><?php esc_html_e( 'View', 'listinghub' );?> </a>
				
				
			</div>	
		</div>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'User Sign Up:', 'listinghub' );?> </label>
			<div class="col-md-10 ">
				
					<?php
						if ( $pages = get_pages( $args ) ){
							echo "<select id='signup_page' name='signup_page' class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($registration==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
					<?php
						$reg_page= get_permalink( $registration); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"> <?php esc_html_e( 'View', 'listinghub' );?></a>
				</div>
		</div>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'Signup Thanks', 'listinghub' );?> : </label>
			<div class="col-md-10 ">
				
					<?php
						if ( $pages = get_pages( $args ) ){
							echo "<select id='thank_you_page'  name='thank_you_page'  class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($thank_you==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
				
				
					<?php
						$reg_page= get_permalink( $thank_you); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"> <?php esc_html_e( 'View', 'listinghub' );?></a>
				
			</div>	
		</div>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'Login Page:', 'listinghub' );?> </label>
			<div class="col-md-10 ">
				
					<?php
						if ( $pages = get_pages( $args ) ){
							echo "<select id='login_page'  name='login_page'  class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($login_page==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
				
					<?php
						$reg_page= get_permalink( $login_page); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"><?php esc_html_e( 'View', 'listinghub' );?> </a>
			
			</div>	
		</div>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'User My Account', 'listinghub' );?> : </label>
			<div class="col-md-10 ">
				
					<?php
						if ( $pages = get_pages( $args ) ){
							echo "<select id='profile_page'  name='profile_page'  class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($profile_page==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
				
					<?php
						$reg_page= get_permalink( $profile_page); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"> <?php esc_html_e( 'View', 'listinghub' );?></a>
				
			</div>	
		</div>
		
		<?php
		$profile_page=get_option('epjblistinghub_author_dir_page');
		?>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'Author Directory:', 'listinghub' );?> </label>
			<div class="col-md-10 ">
				
					<?php																				
						if ( $pages = get_pages( $args ) ){
							echo "<select id='employer_dir'  name='employer_dir'  class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($profile_page==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
				
					<?php																				
						$reg_page= get_permalink( $profile_page); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"><?php esc_html_e( 'View', 'listinghub' );?> </a>
				
			</div>	
		</div>
		
		
		
		
		
		
		<?php
		$profile_page=get_option('epjblistinghubr_public_profile_page');
		?>
		<div class="form-group row">
			<label  class="col-md-2   control-label"><?php esc_html_e( 'Author Public Profile:', 'listinghub' );?> </label>
			<div class="col-md-10 ">
				
					<?php																				
						if ( $pages = get_pages( $args ) ){
							echo "<select id='employer_public'  name='employer_public'  class='form-control'>";
							foreach ( $pages as $page ) {
								echo "<option value='{$page->ID}' ".($profile_page==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						}
					?>
				
					<?php																				
						$reg_page= get_permalink( $profile_page); 
					?>
					<a class="btn btn-info mt-2 " href="<?php  echo esc_url($reg_page); ?>"><?php esc_html_e( 'View', 'listinghub' );?> </a>
				
			</div>	
		</div>
		
		
		
		<div class="form-group row">
			<label  class="col-md-2   control-label"> </label>
			<div class="col-md-10 ">
					<hr/>
					<div id="page_all_setting_save"></div>
					<button type="button" onclick="return  listinghub_update_page_settings();" class="button button-primary"><?php esc_html_e( 'Update', 'listinghub' );?></button>
				
				<div class="checkbox col-md-1 ">
				</div>
			</div>	
		</div>	
	</form>
