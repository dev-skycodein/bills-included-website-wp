<?php
	$csv = file_get_contents( get_attached_file($csv_file_id) );
	if($csv === false){	
		$url=get_attached_file($csv_file_id);
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$csv = curl_exec($ch);
	}
	$csv_rows = explode( "\n", $csv );
	$total_rows = count( $csv_rows );
	$title_row = $csv_rows[ 0 ];
	update_option( 'eppro_total_row',$total_rows-1);	
	update_option( 'eppro_current_row','1');	
	$title_row_array= explode(",",$title_row);
	$main_fields =array('id','post_title','post_content','category','tag','locations','featured-image','image_gallery_urls','business_hours','listing_contact_source','company_name', 'address','local-area','latitude','longitude','city','postcode','state','country','phone','contact-email','contact_web','youtube');
	$maping='';

	
	$default_fields = array();
	$new_field_set=	get_option('listinghub_li_fields' );
	if($new_field_set!=""){
		 $default_fields=get_option('listinghub_li_fields' );
		}else{
		$default_fields['business_type']='Business Type';
		$default_fields['main_products']='Main Products';
		$default_fields['number_of_employees']='Number Of Employees';
		$default_fields['main_markets']='Main Markets';
		$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
	}
	
	if(is_array($default_fields)){
		foreach($default_fields  as $field_key => $field_value){
			array_push($main_fields, $field_key);
			
		}
	}
	
	
			
	$i=0;
	$maping=$maping.'<form id="csv_maping" name="csv_maping" ><table class="table  table-striped">
	<thead>
    <tr>    
	<th>'.esc_html__('Post Field/Map to field', 'listinghub' ).'</th>
	<th>'.esc_html__('CSV Column Title/Name', 'listinghub' ).'</th>      
    </tr>
	</thead>';
	foreach($title_row_array as $one_col){
		$sel_name= str_replace (' ','-', $one_col);
		$maping=$maping.'<tr><td><select name="'.trim($sel_name).'">';
		$maping=$maping.'<option value="">'.esc_html__('Email', 'listinghub' ).'</option>';
		$ii=0;
		foreach($main_fields as $main_one){		
			$maping=$maping.'<option value="'.esc_attr($main_one).'" '.($i==$ii?' selected':"").'>'.esc_html($main_one).'</option>';		
			$ii++;
		}	
		$maping=$maping.'</select></td>';
		$maping=$maping.'<td>'.$one_col.'<input type="hidden" name="column'.$i.'" value="'.esc_attr($one_col).'"></td>';
		$maping=$maping.'</tr>';	
		$i++;	
	}
	$maping=$maping.'</table></form>';
?>