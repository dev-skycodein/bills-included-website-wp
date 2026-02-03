<div class="bootstrap-wrapper">
 	<div class="dashboard-eplugin container-fluid">
 		<?php	
			global $wpdb, $post,$current_user;	
			//*************************	plugin file *********
			$listinghub_approve= get_post_meta( $post->ID,'listinghub_approve', true );
			$listinghub_current_author= $post->post_author;
			$userId=$current_user->ID;
			if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
			?>
			<div class="row">
				<div class="col-md-12">
					<?php esc_html_e( 'User ID :', 'listinghub' )?>
					<select class="form-control" id="listinghub_author_id" name="listinghub_author_id">
						<?php	
							$sql="SELECT * FROM $wpdb->users ";		
							$products_rows = $wpdb->get_results($sql); 	
							if(sizeof($products_rows)>0){									
								foreach ( $products_rows as $row ) 
								{	
									echo '<option value="'.$row->ID.'"'. ($listinghub_current_author == $row->ID ? "selected" : "").' >'. $row->ID.' | '.$row->user_email.' </option>';
								}
							}	
						?>
					</select>
				</div>  
				<div class="col-md-12"> <label>
					<input type="checkbox" name="listinghub_approve" id="listinghub_approve" value="yes" <?php echo ($listinghub_approve=="yes" ? 'checked': "" )  ; ?> />  <strong><?php esc_html_e( 'Approve', 'listinghub' )?></strong>
				</label>
				</div> 
			</div>	  
			<?php
			}
		?>
 		<br/>
		<div class="row">
 			<div class="col-md-12">
				<label>
					<?php
						$listinghub_featured= get_post_meta( $post->ID,'listinghub_featured', true );
					?>
					<label><input type="radio" name="listinghub_featured" id="listinghub_featured" value="featured" <?php echo ($listinghub_featured=="featured" ? 'checked': "" )  ; ?> />  <strong><?php esc_html_e( 'Featured (display on top)', 'listinghub' )?></strong></label>
					<br/>
					<label><input type="radio" name="listinghub_featured" id="listinghub_featured" value="Not-featured" <?php echo ($listinghub_featured=="Not-featured" ? 'checked': "" )  ; ?> />  <strong><?php esc_html_e( 'Not Featured', 'listinghub' )?></strong></label>
				</label>
			</div>
		</div>		
	</div>
</div>		