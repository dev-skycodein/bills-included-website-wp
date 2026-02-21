    <?php
    $package_id=get_user_meta(get_current_user_id(),'listinghub_package_id',true);
    if($package_id!=""){
        $post_p = get_post($package_id);
    }
    $renter = false;
    $landlord = false;
    if($post_p->post_title == "Renter"){
        $renter = true;
    }
    else if($post_p->post_title == "Lanlords" || $post_p->post_title == "Essential" || $post_p->ID == 3347){
        $landlord = true;
    }

    $base_url = strtok( home_url( $_SERVER['REQUEST_URI'] ), '?' );
    ?>
	<style>
		ul#pills-tab {
			flex-direction: row-reverse !important;
		}
	</style>
<ul>
    <li class="<?php echo ($active=='dashboard'? 'active':''); ?> ">
			<a href="<?php echo $base_url.'?profile=dashboard'; ?>">
				<i class="fas fa-home"></i>
    			Your Dashboard  </a>
		</li>
	<?php
		$account_menu_check= '';
		if( get_option('epjblistinghub_menu_listinghome' ) ) {
			$account_menu_check= get_option('epjblistinghub_menu_listinghome');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="">
			<a href="<?php echo get_post_type_archive_link( $listinghub_directory_url ) ; ?>">
				<i class="fas fa-list-alt"></i>
			<?php  esc_html_e('Recent Properties','listinghub');	 ?> </a>
		</li>
		<?php
		}
	?>
	<?php
		$account_menu_check= '';
		if( get_option('epjblistinghub_messageboard' ) ) {
			$account_menu_check= get_option('epjblistinghub_messageboard');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="<?php echo ($active=='messageboard'? 'active':''); ?> ">
		<a href="<?php echo get_permalink(); ?>?&profile=messageboard">
			<i class="far fa-envelope"></i>
		<?php  esc_html_e('Message','listinghub');?></a>
	</li>
	<?php
		}
	?>
	<?php
	    if(!$renter){
	        $help_url = home_url().'/landlord-partnerships/';
	    }
	    else{
	        $help_url = home_url().'/throughout-your-journey/';
	    }
	?>
	<li class="<?php echo ($active=='messageboard'? 'active':''); ?> ">
		<a href="<?php echo $help_url; ?>">
			<i class="far fa-envelope"></i>
		<?php  esc_html_e('How We Can Help','listinghub');?></a>
	</li>

	<?php
	if(!$renter){
		$account_menu_check= '';
		if( get_option('epjblistinghub_mylevel' ) ) {
			$account_menu_check= get_option('epjblistinghub_mylevel');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="<?php echo ($active=='level'? 'active':''); ?> ">
			<a href="<?php echo get_permalink(); ?>?&profile=level">
				<i class="fas fa-user-clock"></i>
			<?php  esc_html_e('Membership','listinghub');	 ?> </a>
		</li>
		<?php
		}
	}
	?>
	
	<?php
		$account_menu_check= '';
		if( get_option('epjblistinghub_menusetting' ) ) {
			$account_menu_check= get_option('epjblistinghub_menusetting');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="<?php echo ($active=='setting'? 'active':''); ?> ">
			<a href="<?php echo get_permalink(); ?>?&profile=setting">
				<i class="fas fa-user-cog"></i>
			<?php esc_html_e( 'Edit Profile', 'listinghub' ); ?> </a>
		</li>
		<?php
		}
		// Edit agency profile: for claim-approved agency owners, link to CYA agency edit form.
		if ( function_exists( 'cya_user_is_agency_owner' ) && cya_user_is_agency_owner( get_current_user_id() ) ) {
			$cya_dashboard = get_option( 'cya_agency_dashboard_url', '' );
			if ( $cya_dashboard !== '' ) {
				$cya_edit_url = add_query_arg( 'agency_edit', '1', $cya_dashboard );
				?>
		<li>
			<a href="<?php echo esc_url( $cya_edit_url ); ?>">
				<i class="fas fa-building"></i>
			<?php esc_html_e( 'Edit agency profile', 'listinghub' ); ?></a>
		</li>
				<?php
			}
		}
	?>
	
		
	<?php
	if(!$renter){
		if( get_option('epjblistinghub_menuallpost' ) ) {
			$account_menu_check= get_option('epjblistinghub_menuallpost');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="<?php echo ($active=='all-post'? 'active':''); ?> ">
			<a href="<?php echo get_permalink(); ?>?&profile=all-post">
				<i class="far fa-copy"></i>
			<?php  esc_html_e('Manage Listings','listinghub');?>  </a>
		</li>
		<?php
		}
	}
	?>	
	
	<?php
	if($renter){
		$account_menu_check= '';
		if( get_option('epjblistinghub_notification' ) ) {
			$account_menu_check= get_option('epjblistinghub_notification');
		}
		if($account_menu_check!='yes'){
		?>
	<li class="<?php echo ($active=='notification'? 'active':''); ?> ">
		<a href="<?php echo get_permalink(); ?>?&profile=notification">
			<i class="far fa-bell"></i>
		<?php  esc_html_e('Listing Notifications','listinghub');?> </a>
	</li>
	<?php
		}
	}
	?>
	
	<?php
	if($renter){
		$account_menu_check= '';
		if( get_option('epjblistinghub_author_bookmarks' ) ) {
			$account_menu_check= get_option('epjblistinghub_author_bookmarks');
		}
		if($account_menu_check!='yes'){ 
		?>
		<li class="<?php echo ($active=='author_bookmarks'? 'active':''); ?> ">
			<a href="<?php echo get_permalink(); ?>?&profile=author_bookmarks">
				<i class="fas fa-user-check"></i>
			<?php   esc_html_e('Saved Author','listinghub');?> </a>
		</li>
		<?php
		}
	}
	?>
	<?php
	if($renter){
		$account_menu_check= '';
		if( get_option('epjblistinghub_listing_bookmarks' ) ) {
			$account_menu_check= get_option('epjblistinghub_listing_bookmarks');
		}
		if($account_menu_check!='yes'){
		?>
		<li class="<?php echo ($active=='listing_bookmark'? 'active':''); ?> ">
			<a href="<?php echo get_permalink(); ?>?&profile=listing_bookmark">
				<i class="fas fa-chalkboard-teacher"></i>
			<?php   esc_html_e('Saved Listing','listinghub');?> </a>
		</li>
		<?php
		}
	}
	?>
	
    <li class="<?php echo ($active=='log-out'? 'active':''); ?> ">
		<a href="<?php echo wp_logout_url( home_url() ); ?>" >
			<i class="fas fa-sign-out-alt"></i>
			<?php  esc_html_e('Sign out','listinghub');?>
		</a>
	</li>
</ul>