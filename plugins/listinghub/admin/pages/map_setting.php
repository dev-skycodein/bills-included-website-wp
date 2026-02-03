<?php
	$dir_map_api=get_option('epjbdir_map_api');	
	if($dir_map_api==""){$dir_map_api='';}	
?>
<form class="form-horizontal" role="form"  name='map_settings' id='map_settings'>	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Google Map & Places API Key','listinghub');  ?>
			<br/><small><?php esc_html_e('Please set your own google map API key for your site( default key is for only demo)
			','listinghub');  ?> </small>
		</label>
		<div class="col-md-8">																		
			<input class="col-md-12 form-control" type="text" name="dir_map_api" id="dir_map_api" value='<?php echo esc_attr($dir_map_api); ?>' >
			<a  class="col-md-12" href="<?php echo esc_url('https://developers.google.com/maps/documentation/javascript/get-api-key');?>"> <?php esc_html_e( 'Get your Google Maps API Key here.', 'listinghub' );?>     </a>
		</div>
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Zoom','listinghub');  ?></label>
		<?php
			$dir_map_zoom=get_option('epjbdir_map_zoom');	
			if($dir_map_zoom==""){$dir_map_zoom='7';}	
		?>
		<div class="col-md-3">													
			<input  class="form-control" type="text" name="dir_map_zoom" id="dir_map_zoom" value='<?php echo esc_attr($dir_map_zoom); ?>' >
				<?php esc_html_e('20 is more Zoom, 1 is less zoom','listinghub');  ?> 
				
		</div>
		
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Type','listinghub');  ?></label>
		<div class="col-md-6">
			<?php
				$dir_map_type=get_option('epjbdir_map_type');	
				if($dir_map_type==""){$dir_map_type='OpenSteet';}	
			?>
			<select id='map_type' name='map_type' class='form-control'>
				<option value="google-map" <?php echo ($dir_map_type=='google-map'?' selected':''); ?>><?php esc_html_e('Google Map','listinghub');  ?></option>
				<option value="opensteet-map" <?php echo ($dir_map_type=='opensteet-map'?' selected':''); ?> ><?php esc_html_e('OpenSteet Map','listinghub');  ?></option>
			</select>
		</div>
		<div class="col-md-2">
			<label>	
			</label>	
		</div>
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Radius','listinghub');  ?></label>
		<div class="col-md-6">
			<?php
				$epjbdir_map_radius=get_option('epjbdir_map_radius');	
				if($epjbdir_map_radius==""){$epjbdir_map_radius='Km';}	
			?>
			<select id='epjbdir_map_radius' name='epjbdir_map_radius' class='form-control'>
				<option value="Km" <?php echo ($epjbdir_map_radius=='Km'?' selected':''); ?>><?php esc_html_e('Km','listinghub');  ?></option>
				<option value="Mile" <?php echo ($epjbdir_map_radius=='Mile'?' selected':''); ?> ><?php esc_html_e('Mile','listinghub');  ?></option>
			</select>
		</div>
		<div class="col-md-2">
			<label>	
			</label>	
		</div>
	</div>
	
	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Search Box Near to Me','listinghub');  ?></label>
		<div class="col-md-3">
			<?php
				$listinghub_near_to_me=get_option('listinghub_near_to_me');	
				if($listinghub_near_to_me==""){$listinghub_near_to_me='50';}	
			?>
			<input  class="form-control" type="text" name="listinghub_near_to_me" id="listinghub_near_to_me" value='<?php echo esc_attr($listinghub_near_to_me); ?>' >
		</div>
		<div class="col-md-2">
			<label>	
			</label>	
		</div>
	</div>
	
	
	
	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Force Default Location','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_forcelocation=get_option('listinghub_forcelocation');					
			?>
			<label class="switch">
			  <input name="listinghub_forcelocation" type="checkbox" value="forcelocation"  <?php echo ($listinghub_forcelocation=='forcelocation' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Default Latitude','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_defaultlatitude=get_option('listinghub_defaultlatitude');					
			?>
			<input  class="form-control" type="text" name="listinghub_defaultlatitude" id="listinghub_defaultlatitude" value='<?php echo esc_attr($listinghub_defaultlatitude); ?>' >
		</div>
		<div class="col-md-4">
			<label>	<a href="<?php echo esc_url('https://www.maps.ie/coordinates.html');?>" target="_blank" >
				<?php esc_html_e('You can find latitude here','listinghub');  ?></a> 
			</label>	
		</div>
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Default Longitude','listinghub');  ?></label>
		<div class="col-md-3">			<?php
				$listinghub_defaultlongitude=get_option('listinghub_defaultlongitude');					
			?>
			<input  class="form-control" type="text" name="listinghub_defaultlongitude" id="listinghub_defaultlongitude" value='<?php echo esc_attr($listinghub_defaultlongitude); ?>' >
		</div>
		<div class="col-md-4">
			<label>	<a href="<?php echo esc_url('https://www.maps.ie/coordinates.html');?>" target="_blank" >
				<?php esc_html_e('You can find longitude here','listinghub');  ?></a> 
			</label>	
		</div>
	</div>
	<hr/>
	 <label class="listinghub-settings-sub-section-title "> <?php esc_html_e('Map Popup/ Infobox settings','listinghub');  ?></label>
	
	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Popup/Infobox Image ','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_infobox_image=get_option('listinghub_infobox_image');	
				if($listinghub_infobox_image==""){$listinghub_infobox_image='yes';}	
			?>
			<label class="switch">
			  <input name="listinghub_infobox_image" type="checkbox" value="yes"  <?php echo ($listinghub_infobox_image=='yes' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Popup/Infobox Title ','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_infobox_title=get_option('listinghub_infobox_title');	
				if($listinghub_infobox_title==""){$listinghub_infobox_title='yes';}	
			?>
			<label class="switch">
			  <input name="listinghub_infobox_title" type="checkbox" value="yes"  <?php echo ($listinghub_infobox_title=='yes' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Popup/Infobox Location ','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_infobox_location=get_option('listinghub_infobox_location');		
				if($listinghub_infobox_location==""){$listinghub_infobox_location='yes';}	
			?>
			<label class="switch">
			  <input name="listinghub_infobox_location" type="checkbox" value="yes"  <?php echo ($listinghub_infobox_location=='yes' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Map Popup/Infobox Direction ','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_infobox_direction=get_option('listinghub_infobox_direction');
				if($listinghub_infobox_direction==""){$listinghub_infobox_direction='yes';}
			?>
			<label class="switch">
			  <input name="listinghub_infobox_direction" type="checkbox" value="yes"  <?php echo ($listinghub_infobox_direction=='yes' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Link to Detail page ','listinghub');  ?>			
			</label>
		<div class="col-md-3">			<?php
				$listinghub_infobox_linkdetail=get_option('listinghub_infobox_linkdetail');
				if($listinghub_infobox_linkdetail==""){$listinghub_infobox_linkdetail='yes';}
			?>
			<label class="switch">
			  <input name="listinghub_infobox_linkdetail" type="checkbox" value="yes"  <?php echo ($listinghub_infobox_linkdetail=='yes' ? ' checked':'');  ?> >
			  <span class="slider round"></span>
			</label>
		</div>		
	</div>
	
	<div class="row">
		
		<div class="col-md-12 col-12">
			<hr/>
			<div id="success_message_map_setting"></div>	
			<button type="button" onclick="return  listinghub_update_map_settings();" class="button button-primary"><?php esc_html_e( 'Update', 'listinghub' );?></button>
		</div>	
	</div>	
</form>