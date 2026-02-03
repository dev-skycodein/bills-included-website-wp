<div id="update_message"> </div>		 
<form class="form-horizontal" role="form"  name='directory_settings' id='directory_settings'>
	<?php
		$listinghub_archive_layout=get_option('listinghub_archive_layout');	
		if($listinghub_archive_layout==""){$listinghub_archive_layout='archive-left-map';}	
	?>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Default All Listing Page Layout','listinghub');  ?></label>
		<div class="col-md-2">
			<label>												
				<input type="radio" name="listinghub_archive_layout" id="listinghub_archive_layout" value='archive-left-map' <?php echo ($listinghub_archive_layout=='archive-left-map' ? 'checked':'' ); ?> > <?php esc_html_e( 'Listing + Left Map', 'listinghub' );?>  
			</label>	
		</div>
		<div class="col-md-2">	
			<label>											
				<input type="radio"  name="listinghub_archive_layout" id="listinghub_archive_layout" value='archive-top-map' <?php echo ($listinghub_archive_layout=='archive-top-map' ? 'checked':'' );  ?> > <?php esc_html_e( 'Listing + Top Map', 'listinghub' );?>
			</label>
		</div>	
		<div class="col-md-2">	
			<label>											
				<input type="radio"  name="listinghub_archive_layout" id="listinghub_archive_layout" value='archive-no-map' <?php echo ($listinghub_archive_layout=='archive-no-map' ? 'checked':'' );  ?> > <?php esc_html_e( 'Listing Without Map', 'listinghub' );?>
			</label>
		</div>		
	</div>	
	<?php
		$listinghub_user_can_publish=get_option('listinghub_user_can_publish');	
		if($listinghub_user_can_publish==""){$listinghub_user_can_publish='yes';}	
	?>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Publish Listing','listinghub');  ?></label>
		<div class="col-md-2">
			<label>												
				<input type="radio" name="listinghub_user_can_publish" id="listinghub_user_can_publish" value='admin-can' <?php echo ($listinghub_user_can_publish=='admin-can' ? 'checked':'' ); ?> > <?php esc_html_e( 'Admin will Publish', 'listinghub' );?>  
			</label>	
		</div>
		<div class="col-md-2">	
			<label>											
				<input type="radio"  name="listinghub_user_can_publish" id="listinghub_user_can_publish" value='yes' <?php echo ($listinghub_user_can_publish=='yes' ? 'checked':'' );  ?> > <?php esc_html_e( 'All user can publish', 'listinghub' );?>
			</label>
		</div>	
	</div>
	<?php
		$listing_hide=get_option('listinghub_listing_hide_opt');	
		if($listing_hide==""){$listing_hide='package';}	
	?>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Listing hide','listinghub');  ?></label>
		<div class="col-md-2">
			<label>												
				<input type="radio" name="listing_hide" id="listing_hide" value='package' <?php echo ($listing_hide=='package' ? 'checked':'' ); ?> > <?php esc_html_e( 'When User Package Expire ', 'listinghub' );?>  
			</label>	
		</div>
		<div class="col-md-2">	
			<label>											
				<input type="radio"  name="listing_hide" id="listing_hide" value='deadline' <?php echo ($listing_hide=='deadline' ? 'checked':'' );  ?> > <?php esc_html_e( 'After Deadline of listing', 'listinghub' );?>
			</label>
		</div>	
		<div class="col-md-2">	
			<label>											
				<input type="radio"  name="listing_hide" id="listing_hide" value='admin' <?php echo ($listing_hide=='admin' ? 'checked':'' );  ?> > <?php esc_html_e( 'Admin will hide/delete', 'listinghub' );?>
			</label>
		</div>	
		
	</div>
	
		<?php
		$directoryprosinglepage=get_option('directoryprosinglepage');
		if($directoryprosinglepage==''){$directoryprosinglepage='plugintemplate';}
		?>
	
		<div class="form-group row">
			<label  class="col-md-3 control-label"><?php esc_html_e('Listing Detail Page','ivdirectories');  ?>
				
				</label>
			<div class="col-md-2">					
				<label>												
					<input type="radio" name="directoryprosinglepage"  value='plugintemplate' <?php echo ($directoryprosinglepage=='plugintemplate' ? 'checked':'' ); ?> >							
					<?php esc_html_e('Plugin Own Template','listinghub');  ?>							
				</label>	
			</div>
			<div class="col-md-6">	
						<label >											
							<input type="radio" name="directoryprosinglepage"  value='custompage' <?php echo ($directoryprosinglepage=='custompage' ? 'checked':'' ); ?> >							
							<?php esc_html_e('Your Custom Page. Sometime Block theme will not get header. You need to create a page & add the shortcode :  [listinghub_listing_detail_page] ','listinghub');  ?>							
						</label>
						<?php
						$single_custompag=get_option('listing_single_custompage'); 
							$args = array(
							'child_of'     => 0,
							'sort_order'   => 'ASC',
							'sort_column'  => 'post_title',
							'hierarchical' => 1,															
							'post_type' => 'page'
							);
						?>
						<?php											
						 if ( $pages = get_pages( $args ) ){
							echo "<select id='listing_single_custompage'  name='listing_single_custompage'  class=''>";
							 echo "<option value='' >".esc_html__('Select Your Custom listing Detail Page which has the shortcode','listinghub')."</option>";
							 
							foreach ( $pages as $page ) {
							  echo "<option value='{$page->ID}' ".($single_custompag==$page->ID ? 'selected':'').">{$page->post_title}</option>";
							}
							echo "</select>";
						  }
						?>
						
			</div>	
					
		</div>	
	<?php											
		$opt_style=	get_option('listinghub_archive_template');
		
	?>	
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Default listing Image','listinghub');  ?> 
		</label>
		<div class="col-md-2" id="listing_defaultimage">
				<?php
					if(get_option('listinghub_listing_defaultimage')!=''){
						$default_image= wp_get_attachment_image_src(get_option('listinghub_listing_defaultimage'));
						if(isset($default_image[0])){									
							$default_image=$default_image[0] ;
						}
						}else{
							$default_image=ep_listinghub_URLPATH."/assets/images/default-directory.jpg";
						}
					?>
				<img class="w80"   src="<?php echo esc_url($default_image);?>">
				
		</div>
		<div class="col-md-5">	
			
				<input type="hidden" name="listinghub_listing_defaultimage" id="listinghub_listing_defaultimage" >
				<button type="button" onclick="return  listinghub_listing_defaultimage_fun();" class="btn btn-primary btn-xs mt-1"><?php esc_html_e('Set Image','listinghub');  ?></button>			
				<p><?php esc_html_e('Best Fit 450px X 350px','listinghub');  ?> </p>
		</div>
	</div>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Default Location Image','listinghub');  ?> 
		</label>
		<div class="col-md-2" id="location_defaultimage">
			<?php
					if(get_option('listinghub_location_defaultimage')!=''){
						$default_image= wp_get_attachment_image_src(get_option('listinghub_location_defaultimage'));
						if(isset($default_image[0])){									
							$default_image=$default_image[0] ;
						}
						}else{
							$default_image=ep_listinghub_URLPATH."/assets/images/location.jpg";
						}
					?>
				<img class="w80"   src="<?php echo esc_url($default_image);?>">
				
		</div>
		<div class="col-md-5">	
				<input type="hidden" name="listinghub_location_defaultimage" id="listinghub_location_defaultimage" >
				<button type="button" onclick="return  listinghub_location_defaultimage_fun();" class="btn btn-primary btn-xs mt-1"><?php esc_html_e('Set Image','listinghub');  ?></button>	
				<p><?php esc_html_e('Best Fit 300px X 400px','listinghub');  ?> </p>
		</div>
	</div>
	
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Default Category Image','listinghub');  ?> 
		</label>
		<div class="col-md-2" id="category_defaultimage">
					<?php
					if(get_option('listinghub_category_defaultimage')!=''){
						$default_image= wp_get_attachment_image_src(get_option('listinghub_category_defaultimage'));
						if(isset($default_image[0])){									
							$default_image=$default_image[0] ;
						}
						}else{
							$default_image=ep_listinghub_URLPATH."/assets/images/category.png";
						}
					?>
				<img class="w80"  src="<?php echo esc_url($default_image);?>">
				
		</div>
		<div class="col-md-5">	
				<input type="hidden" name="listinghub_category_defaultimage" id="listinghub_category_defaultimage" >										
				<button type="button" onclick="return  listinghub_category_defaultimage_fun();" class="btn btn-primary btn-xs mt-1"><?php esc_html_e('Set Image','listinghub');  ?></button>			
				<p><?php esc_html_e('Best Fit 400px X 400px','listinghub');  ?> </p>
		</div>
	</div>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Default Listing Banner Image','listinghub');  ?> 
		</label>
		<div class="col-md-2" id="banner_defaultimage">
			<?php
					if(get_option('listinghub_banner_defaultimage')!=''){
						$default_image= wp_get_attachment_image_src(get_option('listinghub_banner_defaultimage'));
					if(isset($default_image[0])){									
						$default_image=$default_image[0] ;
					}
					}else{
						$default_image=ep_listinghub_URLPATH."/assets/images/banner.png";
					}
					?>
				<img class="w80"   src="<?php echo esc_url($default_image);?>">
				
		</div>
		
		<div class="col-md-5">	
			
				<input type="hidden" name="listinghub_banner_defaultimage" id="listinghub_banner_defaultimage" >
				<button type="button" onclick="return  listinghub_banner_defaultimage_fun();" class="btn btn-primary btn-xs mt-1"><?php esc_html_e('Set Image','listinghub');  ?></button>	
				<p><?php esc_html_e('Best Fit 1200px X 400px','listinghub');  ?> </p>
			
		</div>
	</div>
	
	<div class="form-group row">
		<?php
			$dir_style5_perpage='20';						
			$dir_style5_perpage=get_option('listinghub_dir_perpage');	
			if($dir_style5_perpage==""){$dir_style5_perpage=20;}
		?>	
		<label  class="col-md-3 control-label">	<?php esc_html_e('Load Per Page','listinghub');  ?> </label>					
		<div class="col-md-2">																	
			<input  class="form-control" type="input" name="listinghub_dir_perpage" id="listinghub_dir_perpage" value='<?php echo esc_attr($dir_style5_perpage); ?>'>
		</div>						
	</div>

	<?php
		$listinghub_url=get_option('ep_listinghub_url');					
		if($listinghub_url==""){$listinghub_url='listing';}
	?>
	<div class="form-group row">
		<label  class="col-md-3 control-label"> <?php esc_html_e('Custom Post Type','listinghub');  ?></label>					
		<div class="col-md-2">													
				<input  class="form-control"  type="input" name="listinghub_url" id="listinghub_url" value='<?php echo esc_attr($listinghub_url); ?>' >
			
		</div>
		<div class="col-md-5">
			<?php esc_html_e('No special characters, no upper case, no space','listinghub');  ?>
		</div>
	</div>
	<hr>
	

	
	<div class="form-group row">
		<label  class="col-md-3 control-label"> </label>
		<div class="col-md-8">
			<div id="update_message49"> </div>	
			<button type="button" onclick="return  listinghub_update_dir_setting();" class="button button-primary"><?php esc_html_e('Save & Update','listinghub');  ?></button>
		</div>
	</div>
</form>