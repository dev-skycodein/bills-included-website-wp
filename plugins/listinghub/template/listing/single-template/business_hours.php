<div class="sidebar-border">
	<div class="toptitle mb-3"><?php esc_html_e('Business Hours', 'listinghub'); ?>
		<?php			
			if(array_key_exists('open_status',$active_single_fields_saved)){ 
				$openStatus = listinghub_check_time($listingid);
			?>	
			<span class="card-time ml-3"><?php
				$saved_icon= listinghub_get_icon($single_page_icon_saved,'open_status', 'single');
				?><i class=" <?php echo esc_html($saved_icon); ?>   <?php echo($openStatus=='Open Now'?" open-green":' close-red') ?>"></i><strong class="small-heading  <?php echo($openStatus=='Open Now'?" open-green":' close-red') ?>"><?php echo esc_html($openStatus) ; ?></strong>
			</span>		
			<?php
				}
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