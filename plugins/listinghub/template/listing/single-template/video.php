<?php	
$video_vimeo_id= esc_attr(get_post_meta($listingid,'vimeo',true));
$video_youtube_id=esc_attr(get_post_meta($listingid,'youtube',true));
if($video_vimeo_id!='' || $video_youtube_id!=''){
?>
<?php
	$v=0;
	$video_vimeo_id= get_post_meta($listingid,'vimeo',true);
	if($video_vimeo_id!=""){ $v=$v+1; ?>
	<div class="row col-md-12   mb-4 mt-3"><iframe src="<?php echo esc_url('//player.vimeo.com/video/');?><?php echo esc_attr($video_vimeo_id); ?>" width="100%" height="415px" class="w-100" frameborder="0"></iframe></div>
	<?php
	}
?>
<?php
	$video_youtube_id=get_post_meta($listingid,'youtube',true);
	if($video_youtube_id!=""){
		echo($v==1?'<hr>':'');
	?>
	<div class="row col-md-12 mb-4 mt-3"><iframe width="100%" height="415px" src="<?php echo esc_url('//www.youtube.com/embed/');?><?php echo esc_attr($video_youtube_id); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="w-100"></iframe></div>
	<?php
	}
}