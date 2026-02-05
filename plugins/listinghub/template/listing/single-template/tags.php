<div class=" border-bottom pb-15 mb-3 toptitle"><i class="<?php echo esc_html($saved_icon); ?>"></i> <?php esc_html_e('What’s Included', 'listinghub'); ?></div>
<div class="inluded-section">
	<?php
		$tag_array= wp_get_object_terms( $listingid,  $listinghub_directory_url.'-tag');
		$i=0;
		foreach($tag_array as $one_tag){	
		?>	
		<!-- <a href="<?php //echo get_tag_link($one_tag->term_id); ?>" class="btn btn-small mr-1 mt-1"><?php //echo esc_attr($one_tag->name); ?></a> -->
		<p class="included-section-item"><i class="fa fa-check icon-color"></i> <?php echo esc_attr($one_tag->name); ?></p>
		<?php
		$i++;
		}	
	?>
</div>
	
