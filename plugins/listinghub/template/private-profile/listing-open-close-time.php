<datalist id="day-time-all">
  <option value="08:00 AM">
  <option value="08:30 AM">
  <option value="09:00 AM">
  <option value="09:30 AM">
  <option value="10:00 AM">
  <option value="10:30 AM">
  <option value="11:00 AM">
  <option value="11:30 AM">
  <option value="12:00 PM">
  <option value="12:30 PM">
  <option value="01:00 PM">
  <option value="01:30 PM">
  <option value="02:00 PM">
  <option value="02:30 PM">
  <option value="03:00 PM">
  <option value="03:30 PM">
  <option value="04:00 PM">
  <option value="04:30 PM">
  <option value="05:00 PM">
  <option value="05:30 PM">
  <option value="06:00 PM">
  <option value="06:30 PM">
  <option value="07:00 PM">
  <option value="07:30 PM">
  <option value="08:00 PM">
  <option value="08:30 PM">
  <option value="09:30 PM">
  <option value="09:30 PM">
  <option value="10:00 PM">
  <option value="10:30 PM">
  <option value="11:00 PM">
  <option value="11:30 PM">
  <option value="12:00 AM">
   <option value="12:30 AM">
  <option value="01:00 AM">
  <option value="01:30 AM">
  <option value="2:00 AM">
  <option value="2:30 AM">
  <option value="3:00 AM">
  <option value="3:30 AM">
  <option value="4:00 AM">
   <option value="4:30 AM">
  <option value="5:00 AM">
   <option value="5:30 AM">
  <option value="6:00 AM">
   <option value="6:30 AM">
  <option value="7:00 AM">
   <option value="7:30 AM"> 
</datalist>

<?php
$dir_addedit_openingtime=get_option('dir_addedit_openingtime');
if($dir_addedit_openingtime==""){$dir_addedit_openingtime='yes';}
if($dir_addedit_openingtime=="yes"){

		$listing_id=(isset($post_edit->ID)?$post_edit->ID:0);
		$opeing_days = get_post_meta($listing_id ,'_opening_time',true);
		if($opeing_days!=''){?>
		<?php
			$i=1;
			if(is_array($opeing_days)){
				foreach($opeing_days as $key => $item){					
					foreach($item as $key2 => $item2){
						echo '<div class="row mb-3" id="old_days'. esc_attr($i) .'">
						<div class="col-md-4  control-label">'.esc_html($key).'</div> <div class="col-md-7"> : '.esc_html($key2).' - '.esc_html($item2).'</h5></div><div class="col-md-1"> <button type="button" onclick="remove_old_day('.esc_attr($i).');"  class="btn btn-small-ar"><span class="dashicons dashicons-trash"></span></button>
						</div>
						<input type="hidden" name="day_name[]" id="day_name[]" value="'.esc_attr($key).'">
						<input type="hidden" name="day_value1[]" id="day_value1[]" value="'.esc_attr($key2).'">
						<input type="hidden" name="day_value2[]" id="day_value2[]" value="'.esc_attr($item2).'">
						</div>';
					}				
					
					$i++;
				}
			}
		}
	?>
	<div id="day_field_div">
		<div class=" row form-group " id="day-row1" >
			<div class=" col-md-4">
				<select name="day_name[]" id="day_name[]" class="form-control">
					<option value=""></option>
					<option value="<?php esc_html_e('Mon','listinghub'); ?> "> <?php esc_html_e('Mon','listinghub'); ?>  </option>
					<option value="<?php esc_html_e('Tue','listinghub'); ?>"><?php esc_html_e('Tue','listinghub'); ?></option>
					<option value="<?php esc_html_e('Wed','listinghub'); ?>"><?php esc_html_e('Wed','listinghub'); ?></option>
					<option value="<?php esc_html_e('Thu','listinghub'); ?>"><?php esc_html_e('Thu','listinghub'); ?></option>
					<option value="<?php esc_html_e('Fri','listinghub'); ?>"><?php esc_html_e('Fri','listinghub'); ?></option>
					<option value="<?php esc_html_e('Sat','listinghub'); ?>"><?php esc_html_e('Sat','listinghub'); ?></option>
					<option value="<?php esc_html_e('Sun','listinghub'); ?>"><?php esc_html_e('Sun','listinghub'); ?></option>
				</select>
			</div>
			<div  class=" col-md-4">
				<input type="text" list="day-time-all"  placeholder="<?php esc_html_e('12:00 AM','listinghub'); ?> " name="day_value1[]" id="day_value1[]"  class="form-control" />
			</div>
			<div  class="col-md-4">
				<input type="text" list="day-time-all"   placeholder="<?php esc_html_e('12:00 PM','listinghub'); ?> " name="day_value2[]" id="day_value2[]"  class="form-control" />
			</div>
		</div>
	</div>
	<div class=" row  form-group ">
		<div class="col-md-12" >
			<button type="button" onclick="add_day_field();"  class="btn btn-small-ar"><?php esc_html_e('Add More','listinghub'); ?></button>
		</div>
	</div>

	

<?php
}
?>