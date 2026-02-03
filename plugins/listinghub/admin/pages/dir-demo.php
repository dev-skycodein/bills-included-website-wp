<?php
	global $wpdb;
	global $current_user;
	$ii=1;
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}			
?>
<div class="row">
	<div class="col-md-12">	
		<div class="progress ">							
			<div id="dynamic" class=" progress-bar progress-bar-success progress-bar-striped active " role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" >
				<span id="current-progress"></span>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4 none " id="cptlink12" > <a  class="btn btn-info " href="<?php echo get_post_type_archive_link( $listinghub_directory_url) ; ?>" target="_blank"><?php esc_html_e('View All Listing','listinghub');?>  </a>
			</div>
			<div class="col-md-4"></div>	
		</div>	
		<div class="row" id="importbutton">						
			<div class="col-md-12 "> 								
				<button type="button" onclick="return  listinghub_import_demo();" class="btn btn-info mt-3"><?php esc_html_e('Import Demo Listing','listinghub');?> </button>							
			</div>
		</div>					
	</div>			
</div>
<div class="row">
	<div class="col-md-12 mb-2 mt-3">	
		
		<?php esc_html_e('You can use the plugin: ','listinghub');?> 	
		<a class="button button-primary  " href="<?php echo esc_url('https://wordpress.org/plugins/depicter/');?>" target="_blank" ><?php esc_html_e('Depicter Plugin','listinghub');?></a>	
		
		<a class="button button-primary mr-3" href="<?php echo  ep_listinghub_URLPATH; ?>assets/depicter.zip" download ><?php esc_html_e('Demo Depicter Slider','listinghub');?>  </a>
		</p>
		<p><a class="button button-primary mr-3" href="<?php echo  ep_listinghub_URLPATH; ?>assets/mountain-parallax-header.zip" download ><?php esc_html_e('Slider Revolution Demo Slider','listinghub');?>  </a></p>
		<p>
	
	<p>
		<a class=" mr-2 btn btn-info btn-xs carspot-button" href="<?php echo  ep_listinghub_URLPATH; ?>assets/content.xml" download ><?php esc_html_e('Download full Demo Content XML File','listinghub'); ?></a>
	</p>
	</div>	
</div>
<div class="row mt-3">	
	<div class="col-md-12 mt-2">
		<label class="listinghub-settings-sub-section-title"> <?php esc_html_e('Setup Tutorial','listinghub');?></label>
	</div>
	<div class="col-md-6 col-12 mt-2">	
		
		<iframe width="100%" height="315" src="https://www.youtube.com/embed/H9SwqLLFVUg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
	</div>		
</div>