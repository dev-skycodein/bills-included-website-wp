<div class="sidebar-border">
	<div class="toptitle mb-3"><?php esc_html_e('Business Hours', 'listinghub'); ?>
		<?php
		// Open/close status badge removed per client request.
		?>
	</div>
	<div class="sidebar-list-listing mb-3"></div>
	<?php	
		$opeing_days = get_post_meta($listingid ,'_opening_time',true);
		if($opeing_days!=''){?>
		
		<?php
			$i=1;
			if(is_array($opeing_days)){
				foreach($opeing_days as $key => $item){					
					foreach($item as $key2 => $item2){ 	?>		
					<div class=" row  mb-1" >						
						<span class="font-md  col-3"><?php echo esc_html($key); ?></span>  					
						
						<span class="card-time  col-9"> <?php echo esc_html($key2).' - '.esc_html($item2); ?><span> 
						
					</div>	
					<?php	
					}				
					
					$i++;
				}
			}
			
		}
	?>
</div>