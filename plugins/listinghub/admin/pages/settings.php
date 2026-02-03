<?php
	global $wpdb , $listinghub_signup_fields_serial;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	include('header.php');
?>
<div class="listinghub-settings  mt-3"> 
	<div class="row">
		<div class="col-md-9 col-8">
			<h2 class="mb-3"><?php esc_html_e('Settings','listinghub'); ?> <a title="Video Tutorial" href="<?php echo esc_url('https://www.youtube.com/playlist?list=PLLRcfoNnzUb6z_9jEWVqw4XjPbhZ2E-LD');?>" target="_blank"><span class="listinghub-icon"><i class="fa-brands fa-youtube"></i></span>	</a></h2> 


		</div>
		<div class="col-md-3 col-4 text-right " id="admin-menu">
			<button class=" btn-border mb-2 " id="compose_adminmenu" ><i class="fa-solid fa-bars"></i></button>
		</div>
	</div>
	

	<div class="listinghub-settings-wrap row">	
		<div class="nav-tab-wrapper col-md-3" id="listinghub-left-menu">	
			<a href="#" class=" nav-tab tablinks "  id="defaultOpen" onclick="listinghub_tabopen(event, 'listing_publish')" >
			<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('Listing Settings/Layout','listinghub'); ?></a>
			
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'demo')" ><span class="dashicons dashicons-database-add"></span> <?php esc_html_e('Demo Data','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "  onclick="listinghub_tabopen(event, 'listing_search')" ><span class="dashicons dashicons-search"></span> <?php esc_html_e('Search Form Builder','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'color_setting')" ><span class="dashicons dashicons-color-picker"></span> <?php esc_html_e('Color','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'alllistinglayout')" ><span class="dashicons dashicons-list-view"></span> <?php esc_html_e('All Listing Data/Fields','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'singlelistinglayout')" ><span class="dashicons dashicons-welcome-write-blog"></span> <?php esc_html_e('Single Listing Fields','listinghub'); ?></a>	
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'map_setting')" ><span class="dashicons dashicons-location-alt"></span> <?php esc_html_e('Map','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'openai_setting')" ><span class="dashicons dashicons-buddicons-forums"></span> <?php esc_html_e('OpenAI ChatGPT','listinghub'); ?></a>
		
			<a href="#" class=" nav-tab tablinks "  onclick="listinghub_tabopen(event, 'my-account')" ><span class="dashicons dashicons-welcome-widgets-menus"></span> <?php esc_html_e('My Account Menu','listinghub'); ?></a>					
			<a href="#" class=" nav-tab tablinks "  onclick="listinghub_tabopen(event, 'registrationfields')" ><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e('Registration/Profile','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "  onclick="listinghub_tabopen(event, 'listingfields')" ><span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Listing Custom Fields','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'csv')" ><span class="dashicons dashicons-database-import"></span> <?php esc_html_e('CSV Importer','listinghub'); ?></a>		
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'payment_gateways')" ><span class="dashicons dashicons-cart"></span> <?php esc_html_e('Payment Gateways','listinghub'); ?></a>
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'packages')" ><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Packages','listinghub'); ?></a>
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'coupons')" ><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Coupons','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "  onclick="listinghub_tabopen(event, 'email_template')" ><span class="dashicons dashicons-email"></span> <?php esc_html_e('Email Template','listinghub'); ?></a>
			<a href="#" class=" nav-tab tablinks "   onclick="listinghub_tabopen(event, 'pagesall')" ><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Plugin Pages','listinghub'); ?></a>				
			<a href="#" class="nav-tab tablinks" onclick="listinghub_tabopen(event, 'mailchimp')" ><span class="dashicons dashicons-cart"></span> <?php esc_html_e('Mailchimp','listinghub'); ?></a>
		
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'user_settings')" ><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e('Users','listinghub'); ?></a>
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'payment_history')"><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Payment History','listinghub'); ?></a>
			
			<a href="#" class="nav-tab tablinks"  onclick="listinghub_tabopen(event, 'shortcodes')"><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e('Useful Shortcode','listinghub'); ?></a>
		</div> 
		<div class="metabox-holder col-md-9">
			
			<div id="demo" class="tabcontent group">				
					
					<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Demo Import ','listinghub');?>  </label></th></tr></thead></table> 						
					<div class="top-20 "><p></p>
						<?php include (ep_listinghub_DIR .'/admin/pages/dir-demo.php');?>			
					</div>
					
				
			</div>
			<div id="csv" class="tabcontent group">				
			
			
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Importing CSV Data ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>
							<?php
								include('csv-import.php');
							?>					
						</div>
					</div>
				</div>
			</div>
			<div id="user_settings" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Users Settings','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/user_directory_admin.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="payment_history" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Payment History','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/payment-history.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="my-account" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('My Account Menu ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/profile-fields.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="coupons" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Coupons ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/all_coupons.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="mailchimp" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Mailchimp ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/mailchimp.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="shortcodes" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Useful shortcode/ Widgets ','listinghub');?> 
						
						 <a href="<?php echo esc_url('https://www.youtube.com/playlist?list=PLLRcfoNnzUb6z_9jEWVqw4XjPbhZ2E-LD');?>" target="_blank">
                            <span class="listinghub-icon">
                                <i class="fa-brands fa-youtube"></i>
							</span>
							<?php  esc_html_e('Videos','listinghub'); ?>
						</a>
							
							</label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/shortcodes-sample.php');?>
						</div>
					</div>
				</div>
			</div>
			
			<div id="payment_gateways" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Payment Gateways ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/payment-settings.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="packages" class="tabcontent group">	
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Packages ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/package_all.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="alllistinglayout" class="tabcontent group">		
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Listing Archive (drag, drop & sort)','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php 
							include (ep_listinghub_DIR .'/admin/pages/archive_setting.php');?>
						</div>
					</div>
				</div>	
			</div>
			<div id="singlelistinglayout" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Listing Detail page (drag & drop)','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php 
							include (ep_listinghub_DIR .'/admin/pages/single_page_setting.php');?>
						</div>
					</div>
				</div>	
			</div>
			<div id="color_setting" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Color Settings ','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/color_setting.php');?>
						</div>
					</div>
				</div>				
			</div>	
			<div id="openai_setting" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Open AI -ChatGPT','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/open-ai.php');?>
						</div>
					</div>
				</div>
			</div>
			
			<div id="email_template" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Email Template ','listinghub');?>  </label></th></tr></thead></table> 						
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/email_template_all.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="map_setting" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Map Settings ','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/map_setting.php');?>
						</div>
					</div>
				</div>				
			</div>	
			<div id="listing_search" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Customize the search form (drag, drop & sort)','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/listing_search.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="listing_publish" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Listing Settings','listinghub');?>  <a class="button button-primary " href="<?php echo esc_url( get_post_type_archive_link( $listinghub_directory_url)) ; ?>" target="blank"><?php esc_html_e('View Page','listinghub');  ?></a></label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/listing_publish.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="pagesall" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Plugin Pages','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/setting-pages-all.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="registrationfields" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Registration / User Profile Fields','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/registration-fields.php');?>
						</div>
					</div>
				</div>
			</div>
			<div id="listingfields" class="tabcontent group">
				<div class="bootstrap-wrapper">
					<div class="container-fluid">
						<table class="form-table"><thead><tr class="listinghub-settings-field-type-sub_section"><th colspan="3" class="listinghub-settings-sub-section-title"><label><?php esc_html_e('Listing Fields','listinghub');?>  </label></th></tr></thead></table>
						<div class="top-20 "><p></p>				  
							<?php include (ep_listinghub_DIR .'/admin/pages/directory_fields.php');?>
						</div>
					</div>
				</div>
			</div>
			
		
		</div>
		</div>
</div>		
<?php
	include('footer.php');
?>