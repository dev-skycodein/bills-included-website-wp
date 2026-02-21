<?php
	$profile_url=get_permalink();
	global $current_user;
	$user = $current_user->ID;
	$message='';
	// Claim-approved agency owners: view-only (no add, edit, delete listings).
	$cya_agency_owner_view_only = ( function_exists( 'cya_user_is_agency_owner' ) && cya_user_is_agency_owner( $current_user->ID ) && ! current_user_can( 'manage_options' ) );
	if ( isset( $_GET['delete_id'] ) ) {
		$post_id = sanitize_text_field( wp_unslash( $_GET['delete_id'] ) );
		$post_edit = get_post( $post_id );
		if ( $post_edit && ! $cya_agency_owner_view_only ) {
			if ( $post_edit->post_author == $current_user->ID ) {
				wp_delete_post( $post_id );
				delete_post_meta( $post_id, true );
				$message = esc_html__( "Deleted Successfully", 'listinghub' );
			}
			if ( isset( $current_user->roles[0] ) && $current_user->roles[0] === 'administrator' ) {
				wp_delete_post( $post_id );
				delete_post_meta( $post_id, true );
				$message = esc_html__( "Deleted Successfully", 'listinghub' );
			}
		}
	}
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$main_class = new eplugins_listinghub;
?>

<div class="mt-3 row ">	
	<div class="col-md-6">
		<span class="toptitle-sub"><?php esc_html_e('My Listings', 'listinghub'); ?></span>
	</div>
	<div class="col-md-6">
		<ul class="nav nav-pills  float-right" id="pills-tab" role="tablist">
			<li class="nav-item">
				 <a class="nav-link active" id="pills-all-tab" data-toggle="pill" href="#tab_all" role="tab" aria-controls="pills-home" aria-selected="true"><?php esc_html_e( 'All listings', 'listinghub' ); ?></a>
			</li>
			<?php if ( ! $cya_agency_owner_view_only ) : ?>
			<li class="nav-item">
				 <a class="nav-link " id="pills-add-tab" data-toggle="pill" href="#tab_add_new" role="tab" aria-controls="pills-home"><?php esc_html_e( 'Add New', 'listinghub' ); ?></a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="col-md-12"> <p class="border-bottom"> </p></div>
</div>		

	
<div class="clearfix mb-3"></div>
<div class="tab-content">
	<div class="tab-pane active" id="tab_all">
			<div class="list">
			<?php
				global $wpdb;
				$per_page=10;$row_strat=0;$row_end=$per_page;
				$current_page=0 ;
				if(isset($_REQUEST['cpage']) and $_REQUEST['cpage']!=1 ){
					$current_page=$_REQUEST['cpage']; $row_strat =($current_page-1)*$per_page;
					$row_end=$per_page;
				}
				if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
					$sql="SELECT * FROM $wpdb->posts WHERE post_type IN ('".$listinghub_directory_url."')  and post_status IN ('publish','pending','draft' )  ORDER BY `ID` DESC";
					}else{
					$sql="SELECT * FROM $wpdb->posts WHERE post_type IN ('".$listinghub_directory_url."')  and post_author='".$current_user->ID."' and post_status IN ('publish','pending','draft' )  ORDER BY `ID` DESC";
				}
				$authpr_post = $wpdb->get_results($sql);
				$total_post=count($authpr_post);
				if($total_post>0){
				?>
				<table id="listing-manage" class="table tbl-listing" >
					<thead>
						<tr class="">
							<th><?php  esc_html_e('Title','listinghub');?></th>
						</tr>
					</thead>
					<?php
						$i=0;
						foreach ( $authpr_post as $row )
						{
						?>
						<tr class="my-listing-item">
							<td>
								<div class="align-item-center row">
									<div class="text-left col-md-9 col-9">
										<span class="toptitle-sub"><a href="<?php echo get_permalink($row->ID); ?>"><?php echo esc_html($row->post_title); ?></a></span>
										<div class="meta-listing"><span class="location"> 
										
										<i class="fas fa-calendar-alt"></i>
											<?php  esc_html_e('Posted','listinghub');?>
										<?php echo date('M d, Y',strtotime($row->post_date)); ?>
										<?php
											$exp_date= get_user_meta($current_user->ID, 'listing_exprie_date', true);
											if($exp_date!=''){
												$package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);
												$dir_hide= get_post_meta($package_id, 'listinghub_package_hide_exp', true);
												if($dir_hide=='yes'){?>
												<span> <i class="fas fa-calendar-alt"></i>		
													<?php
														esc_html_e('Expiring','listinghub'); echo" : ";
														echo date('M d, Y',strtotime($exp_date));
													}?>
											</span>
											<?php
											}
										?>
										</span>
										</div>
										
										<div class="location">
											<?php
											$currentCategory = $main_class->listinghub_get_categories_caching($row->ID,$listinghub_directory_url);
											$saved_icon='';
											$cat_name2='';
											$i=0;
											if(isset($currentCategory[0]->slug)){										
												foreach($currentCategory as $c){							
													if(trim($saved_icon)==''){
														$saved_icon= listinghub_get_cat_icon($c->term_id);
													}
													if($i==0){
														$cat_name2 = $c->name;
														}else{
														$cat_name2 = $cat_name2 .' / '.$c->name;
													}
													$i++;
												}
											}
											?>
											
										<span> <?php echo esc_html($cat_name2); ?> </span>
												
											
										</div>
										<div class="location">
											<i class="fas fa-eye"></i>
											<?php esc_html_e( 'View Count', 'listinghub' ); ?>: <?php echo esc_html( (string) get_post_meta( $row->ID, 'listing_views_count', true ) ); ?>
											| <?php esc_html_e( 'Session views', 'listinghub' ); ?>: <?php echo esc_html( (string) get_post_meta( $row->ID, 'listing_views_session_count', true ) ); ?>
										</div>
										<?php $post_ststus=get_post_status($row->ID);  ?>
										<span class="poststatus <?php echo ($post_ststus=='publish'?'greencolor-text':''); ?> "> 
												<?php 
												echo ucfirst($post_ststus);  ?>
										</span>
									</div>
									<div class="listing-func_manage_listing col-md-3 col-3 text-right">
										<?php if ( ! $cya_agency_owner_view_only ) :
											$edit_post = $profile_url . '?&profile=post-edit&post-id=' . $row->ID;
										?>
										<a href="<?php echo esc_url( $edit_post ); ?>" class="btn btn-small-ar mb-2"><i class="fas fa-pencil-alt"></i></a>
										<a href="<?php echo esc_url( $profile_url ); ?>?&profile=all-post&delete_id=<?php echo (int) $row->ID; ?>" onclick="return confirm('Are you sure to delete this post?');" class="btn btn-small-ar mb-2"><i class="far fa-trash-alt"></i></a>
										<?php endif; ?>
									</div>
								</div>
							</td>
						</tr>
						<?php
						}
					?>
				</table>
				<?php
				}
			?>
		</div>
	
	</div>
	<?php if ( ! $cya_agency_owner_view_only ) : ?>
	<div class="tab-pane" id="tab_add_new">
		<?php
		include( ep_listinghub_template . 'private-profile/profile-new-post-1.php' );
		?>
	</div>
	<?php endif; ?>
</div>	
