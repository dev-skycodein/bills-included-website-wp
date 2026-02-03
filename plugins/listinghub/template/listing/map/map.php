<div id="map" class="map50"></div>
<?php	
$dir_map_api=get_option('epjbdir_map_api');	
if($dir_map_api==""){$dir_map_api='';}	
$dir_map_zoom=get_option('epjbdir_map_zoom');	
if($dir_map_zoom==""){$dir_map_zoom='7';}	
$dir_map_type=get_option('epjbdir_map_type');	
if($dir_map_type==""){$dir_map_type='OpenSteet';}

if($dir_map_type=='google-map'){
	include( ep_listinghub_template. 'listing/map/google-map.php');

}else{  
	include( ep_listinghub_template. 'listing/map/openstreet-map.php');
}

