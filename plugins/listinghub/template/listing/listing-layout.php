<?php
get_header(); 
$listinghub_archive_layout=get_option('listinghub_archive_layout');	
if($listinghub_archive_layout==""){$listinghub_archive_layout='archive-left-map';}	
if($listinghub_archive_layout=='archive-left-map'){
	echo do_shortcode('[listinghub_archive_grid]');
}elseif($listinghub_archive_layout=='archive-top-map'){
	echo do_shortcode('[listinghub_archive_grid_top_map]');
}elseif($listinghub_archive_layout=='archive-no-map'){
	echo do_shortcode('[listinghub_archive_grid_no_map]');
}else{
	echo do_shortcode('[listinghub_archive_grid]');
}	
get_footer();
 ?>
