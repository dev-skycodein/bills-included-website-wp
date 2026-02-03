<div class="border-bottom pb-15 mb-3 toptitle-sub"><?php esc_html_e('Message', 'listinghub'); ?>
</div>
<?php
	$args = array(
	'post_type' => 'listinghub_message', 
	'post_status' => 'private',
	'posts_per_page'=> '-1',
	'orderby' => 'date',
	'order'   => 'DESC',
	);
	$user_to = array(
	'relation' => 'AND',
	array(
	'key'     => 'user_to',
	'value'   => $current_user->ID,
	'compare' => '='
	),
	);			
	$args['meta_query'] = array(
	$user_to,
	);
	$the_query = new WP_Query( $args );
?>
<table id="all-bookmark" class="table tbl-epmplyer-bookmark" >
	<thead>
		<tr class="">
			<th><?php  esc_html_e('Message','listinghub');?></th>
		</tr>
	</thead>
	<?php
		$i=0;
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) : $the_query->the_post();					
			$id = get_the_ID();
		?>
		<tr id="companybookmark_<?php echo esc_html(trim($id));?>" >
			<td class="d-md-table-cell">
				<div class="listing-item bookmark">
					<div class="row align-items-center">										
						<div class="col-md-12 listing-info px-0">
							<div class="text px-0 text-left">	
								<span class="toptitle-sub"><?php echo esc_html($the_query->post->post_title); ?>
								</span>	
								<div class="table-content"><span class="location"><i class="fas fa-calendar-day mr-2"></i><?php  echo get_the_time('M d, Y h:m a', $id); ?></span>
								</div>
								<div class="location"><span class="location"><i class="far fa-envelope mr-2"></i><?php  echo get_post_meta( $id, 'from_email', true); ?></span> <i class="fas fa-phone-volume mr-2"></i><?php  esc_html_e('Phone','listinghub');?> : <?php echo esc_attr(get_post_meta($id,'from_phone',true)); ?></div>	
								<?php
									if(get_post_meta($id,'dir_url',true)!=''){
									?>
									<div class="table-content mt-2"><?php  esc_html_e('listing Listing','listinghub');?> : <?php  echo esc_url(get_post_meta( $id, 'dir_url', true)); ?></div>
									<?php
									}
								?>
								<div class="table-content mt-2">
									<?php												
										echo do_shortcode($the_query->post->post_content);
									?>
								</div>			
							</div>
							<div class="text-right">														
								<button class="btn btn-small" onclick="listinghub_delete_message_myaccount('<?php echo esc_attr($id);?>','companybookmark')"><i class="far fa-trash-alt"></i></button>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<?php
			endwhile;
		}	
	?>
</table>