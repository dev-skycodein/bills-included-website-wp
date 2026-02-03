<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?libraries=places&key=<?php echo esc_html($dir_map_api);?>&callback=listinghub_initialize'></script>
<?php 
$top_image =( isset($active_archive_fields['image'])?'yes':'no' );
$listinghub_infobox_image=get_option('listinghub_infobox_image');	
if($listinghub_infobox_image==""){$listinghub_infobox_image=$top_image;}	
if($listinghub_infobox_image=='yes'){
	$top_image='yes';	
}
$listinghub_infobox_title=get_option('listinghub_infobox_title');	
if($listinghub_infobox_title==""){$listinghub_infobox_title='yes';}	
$listinghub_infobox_location=get_option('listinghub_infobox_location');	
if($listinghub_infobox_location==""){$listinghub_infobox_location='yes';}	
$listinghub_infobox_direction=get_option('listinghub_infobox_direction');	
if($listinghub_infobox_direction==""){$listinghub_infobox_direction='yes';}	
$listinghub_infobox_linkdetail=get_option('listinghub_infobox_linkdetail');	
if($listinghub_infobox_linkdetail==""){$listinghub_infobox_linkdetail='yes';}
$listinghub_forcelocation=get_option('listinghub_forcelocation');
	
if($listinghub_forcelocation=='forcelocation'){
	$ins_lat=get_option('listinghub_defaultlatitude');
	$ins_lng=get_option('listinghub_defaultlongitude');
}	

wp_enqueue_style('listinghub-single-google-map', ep_listinghub_URLPATH . 'admin/files/css/single-google-map.css');	
wp_enqueue_script('markerclusterer',ep_listinghub_URLPATH . 'admin/files/js/markerclusterer.js');	
wp_enqueue_script('listinghub-google-map', ep_listinghub_URLPATH . 'admin/files/js/google-map.js');
	wp_localize_script('listinghub-google-map', 'listinghub_map_data', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'	=>get_current_user_id(),
	'Please_login'=>esc_html__('Please login', 'listinghub' ),
	'Add_to_Favorites'=>esc_html__('Add to Favorites', 'listinghub' ),
	'direction_text'=>esc_html__('Direction', 'listinghub' ),
	'marker_icon'=> '',
	'ins_lat'=> $ins_lat,
	'top_image'=> $top_image,
	'infotitle'=>$listinghub_infobox_title,
	'infolocation'=>$listinghub_infobox_location,
	'indirection'=>$listinghub_infobox_direction,
	'infolinkdetail'=> $listinghub_infobox_linkdetail,
	'ins_lng'=> $ins_lng,
	'dir_map_zoom'=>$dir_map_zoom,
	'dirs_json'=>$dirs_json_map,
	
	'ep_listinghub_URLPATH'=>ep_listinghub_URLPATH,
	) );
	
?>

 