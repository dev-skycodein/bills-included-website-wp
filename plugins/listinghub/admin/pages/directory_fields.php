<?php
	global $wpdb;
	global $current_user;
	$ii=1;
	$main_category='';
	if(isset($_POST['main_category'])){$main_category=sanitize_text_field($_POST['main_category']);}	
	
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
?>
<div class="row">
	<div class="col-12">	
	</div>
</div>	
<div class="row">
	<div class="col-md-12 table-responsive mb-4">
		<form id="dir_fields_max" name="dir_fields_max" class="form-horizontal" role="form" onsubmit="return false;">			
			<table id="listing_fieldsdatatable" name="listing_fieldsdatatable"  class="display table" width="100%">					
				<thead>
					<tr>
						<th > <?php  esc_html_e('Input Detail','listinghub')	;?> </th>								
						<th> <?php  esc_html_e('Categories','listinghub')	;?> </th>	
						
					</tr>
				</thead>
				<tbody>							
					<?php
						$default_fields = array();
						$field_set=			get_option('listinghub_li_fields' );
						$field_type=  		get_option( 'listinghub_li_field_type' );
						$field_type_value=  get_option( 'listinghub_li_fieldtype_value' );
						$field_type_cat=  	get_option( 'listinghub_field_type_cat' );
						if($field_set!=""){
							$default_fields=get_option('listinghub_li_fields' );
							}else{
							$default_fields['business_type']='Business Type';
							$default_fields['main_products']='Main Products';
							$default_fields['number_of_employees']='Number Of Employees';
							$default_fields['main_markets']='Main Markets';
							$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
						}
						$i=0;								
						foreach ( $default_fields as $field_key => $field_value ) {
						?>
						<tr  id="wpdatatablelistingfield_<?php echo esc_attr($i);?>">
							<td >
								<div class="row mt-2">
									<label class="col-md-6 col-6"><?php  esc_html_e('Input Name','listinghub');?></label>
									<input type="text" class="form-control col-md-6 col-6" name="meta_name[]" id="meta_name[]" value="<?php echo esc_attr($field_key); ?>"> 	
								</div>
								<div class="row mt-2">
									<label class="col-md-6 col-6"><?php  esc_html_e('Label','listinghub')	;?></label>
									<input type="text" class="form-control col-md-6 col-6" name="meta_label[]" id="meta_label[]" value="<?php echo esc_attr($field_value);?>" >
								</div>
								<div class="row mt-2">
									<label class="col-md-6 col-6"><?php  esc_html_e('Type','listinghub');?></label>
									<?php $field_type_saved= (isset($field_type[$field_key])?$field_type[$field_key]:'' );?>
									<select class="form-control col-md-6 col-6" name="field_type[]" id="field_type[]">
										<option value="text" <?php echo ($field_type_saved=='text'? "selected":'');?> ><?php esc_html_e('Text','listinghub');?></option>
										<option value="textarea" <?php echo ($field_type_saved=='textarea'? "selected":'');?> ><?php esc_html_e('Text Area','listinghub');?></option>
										<option value="dropdown" <?php echo ($field_type_saved=='dropdown'? "selected":'');?> ><?php esc_html_e('Dropdown','listinghub');?></option>
										<option value="radio" <?php echo ($field_type_saved=='radio'? "selected":'');?> ><?php esc_html_e('Radio button','listinghub');?></option>
										<option value="datepicker" <?php echo ($field_type_saved=='datepicker'? "selected":'');?> ><?php esc_html_e('Date Picker','listinghub');?></option>
										<option value="checkbox" <?php echo ($field_type_saved=='checkbox'? "selected":'');?> ><?php esc_html_e('Checkbox','listinghub');?></option>
										<option value="url" <?php echo ($field_type_saved=='url'? "selected":'');?> ><?php esc_html_e('URL','listinghub');?></option>
									</select>
								</div>
								<div class="row mt-2">
									<label class="col-md-12 col-12"><?php  esc_html_e('Value[Dropdown,checkbox,Radio]','listinghub');?> </label>
									<textarea class="form-control col-md-12 col-12 ml-3" rows="3" name="field_type_value[]" id="field_type_value[]" placeholder="<?php  esc_html_e('Separated by comma','listinghub');?> "><?php echo esc_attr((isset($field_type_value[$field_key])?$field_type_value[$field_key]:''));?></textarea>
								</div>
								<div class="row mt-2">
									<div class="col-md-12">
									<button type="button" class="btn btn-primary btn-sm"  onclick="return listinghub_remove_listingfield('<?php echo esc_attr($i); ?>');"  ><span  class="dashicons dashicons-trash ml-1"></span></button>
									</div>	
								</div>	
							</td>
							<td id="categoriesmax_<?php echo esc_attr($i);?>"  >									
								<div class="row mt-2 p-3">
									<?php
										$field_type_cat_saved= (isset($field_type_cat[$field_key])?$field_type_cat[$field_key]:'' ) ;										
										if($field_type_cat_saved==''){$field_type_cat_saved=array('all');}
										$args =array();								
										$args2 = array(
										'type'                     => $listinghub_directory_url,									
										'orderby'                  => 'name',
										'order'                    => 'ASC',
										'hide_empty'               => 0,
										'hierarchical'             => 1,
										'exclude'                  => '',
										'include'                  => '',
										'number'                   => '',
										'taxonomy'                 => $listinghub_directory_url.'-category',
										'pad_counts'               => false
										);
										$main_tag = get_categories( $args2 );										
										if ( $main_tag && !is_wp_error( $main_tag ) ) :
										foreach ( $main_tag as $term_m ) {
											$checked= (in_array($term_m->slug,$field_type_cat_saved)? " checked":'');
											if($field_type_cat_saved=='all'){
												$checked=' checked';
											}
											if($term_m->name!=''){		
											?>
											<div class="col-md-12 col-xl-6">
												<label class="listing-field-cat " > <input type="checkbox"  name="field_categories<?php echo esc_attr($i);?>[]"  id="field_categories<?php echo esc_attr($i);?>[]" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>
											</div>
											<?php
											}
										}
										endif;
									?>
								</div>
							</td>
							
						</tr>	
						<?php
							$i++;
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th> <?php  esc_html_e('Input Detail','listinghub')	;?> </th>								
						<th> <?php  esc_html_e('Categories','listinghub');?> </th>	
						
					</tr>
				</tfoot>
			</table>
			<div id="custom_field_div">
			</div>
			<div class="col-xs-12">
				<button class="btn btn-warning " onclick="return listinghub_add_listingfield();"><?php esc_html_e('Add More Field','listinghub');?></button>
			</div>
			<div class="row">					
				<div class="col-md-12">	
					<hr>
					<div id="success_message-fields"></div>														
					<button class="button button-primary" onclick="return listinghub_update_dir_fields();"><?php esc_html_e( 'Update', 'listinghub' );?> </button>
					<p>&nbsp;</p>
				</div>
			</div>
		</form>					
	</div>
</div>	
<div id="fieldtypemainblank" class="none">
	<?php $field_type_saved= '' ;?>
	<select class="form-control col-md-6 col-6" name="field_type[]" id="field_type[]">
		<option value="text" <?php echo ($field_type_saved=='text'? "selected":'');?> ><?php esc_html_e('Text','listinghub');?></option>
		<option value="textarea" <?php echo ($field_type_saved=='textarea'? "selected":'');?> ><?php esc_html_e('Text Area','listinghub');?></option>
		<option value="dropdown" <?php echo ($field_type_saved=='dropdown'? "selected":'');?> ><?php esc_html_e('Dropdown','listinghub');?></option>
		<option value="radio" <?php echo ($field_type_saved=='radio'? "selected":'');?> ><?php esc_html_e('Radio button','listinghub');?></option>
		<option value="datepicker" <?php echo ($field_type_saved=='datepicker'? "selected":'');?> ><?php esc_html_e('Date Picker','listinghub');?></option>
		<option value="checkbox" <?php echo ($field_type_saved=='checkbox'? "selected":'');?> ><?php esc_html_e('Checkbox','listinghub');?></option>
		<option value="url" <?php echo ($field_type_saved=='url'? "selected":'');?> ><?php esc_html_e('URL','listinghub');?></option>
	</select>
</div>
<div id="fieldcat-main" class="none">
	<div class="row p-3">
		<?php																		
			$field_type_cat_saved=array('all');										
			$args =array();											
			$args2 = array(
			'type'                     => $listinghub_directory_url,									
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'hierarchical'             => 1,
			'exclude'                  => '',
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => $listinghub_directory_url.'-category',
			'pad_counts'               => false
			);
			$main_tag = get_categories( $args2 );										
			if ( $main_tag && !is_wp_error( $main_tag ) ) :
			foreach ( $main_tag as $term_m ) {											
				$checked=' checked';											
			?>										
			<div class="col-md-12 col-xl-6">
				<label class="listing-field-cat" > <input type="checkbox"  name="field_categories<?php echo esc_attr($i);?>[]"  id="field_categories<?php echo esc_attr($i);?>[]" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>
			</div>
			<?php
			}
			endif;										
		?>
	</div>
</div>
<?php
	wp_enqueue_script('eplugins_listinghub-dashboard5', ep_listinghub_URLPATH.'admin/files/js/profile-fields.js', array('jquery'), $ver = true, true );
	wp_localize_script('eplugins_listinghub-dashboard5', 'profile_data', array( 			'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',	
	'adminnonce'=> wp_create_nonce("admin"),
	'pii'	=>$ii,
	'pi'	=> $i,
	'signup_field_serial'	=> $listinghub_signup_fields_serial, 
	"sProcessing"=>  esc_html__('Processing','listinghub'),
	"InputName"=>  esc_html__('Input Name','listinghub'),
	"Label"=>  esc_html__('Label','listinghub'),
	"Type"=>  esc_html__('Type','listinghub'),
	"Value"=>  esc_html__('Value','listinghub'),	
	"sProcessing"=>  esc_html__('Processing','listinghub'),
	"sSearch"=>   esc_html__('Search','listinghub'),
	"lengthMenu"=>   esc_html__('Display _MENU_ records per page','listinghub'),
	"zeroRecords"=>  esc_html__('Nothing found - sorry','listinghub'),
	"info"=>  esc_html__('Showing page _PAGE_ of _PAGES_','listinghub'),
	"infoEmpty"=>   esc_html__('No records available','listinghub'),
	"infoFiltered"=>  esc_html__('(filtered from _MAX_ total records)','listinghub'),
	"sFirst"=> esc_html__('First','listinghub'),
	"sLast"=>  esc_html__('Last','listinghub'),
	"sNext"=>     esc_html__('Next','listinghub'),
	"sPrevious"=>  esc_html__('Previous','listinghub'),
	) );
?>	