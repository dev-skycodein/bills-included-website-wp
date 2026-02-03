"use strict";
var ajaxurl = dirpro_data.ajaxurl;	

function listinghub_update_csv_file(){ 
		"use strict";
	   var ajaxurl = dirpro_data.ajaxurl;		  	  
	   var search_params={
		 "action"  :  "listinghub_finalerp_csv_product_upload",		
		 'csv_file_id': jQuery('#erp_csv_id').val(),
		 "_wpnonce":  	dirpro_data.dirwpnonce,
	   };
	   jQuery.ajax({
		 url : ajaxurl,
		 dataType : "json",
		 type : "post",
		 async:true,
		 data : search_params,
		 success : function(response){
			if(response.code=="success"){	
				jQuery("#ep1").hide();
				jQuery("#ep2").show();
				jQuery('#step-2').removeClass('disabled');
				jQuery('#step-2').addClass('complete active');
				jQuery('#data_maping').html(response.maping);				
				jQuery('#step-1').addClass('complete');
			}	
		}
	  });
}

var row_start=1;

function listinghub_save_csv_file_to_database(){
	"use strict";
	listinghub_upload_status_checker();
	
	jQuery('#csv-loading').val(dirpro_data.loading_image),
	jQuery("#ep2").hide();
	jQuery('#step-3').removeClass('disabled');
	jQuery('#step-3').addClass('complete active');
	jQuery('#step-2').removeClass('active');
	
	jQuery("#ep3").show();
	var search_params2={
		"action"  :  	"listinghub_save_csv_file_to_database",	
		"form_data":	jQuery("#csv_maping").serialize(), 	
		"csv_file_id": 	jQuery('#erp_csv_id').val(),
		"row_start": 	row_start,
		"_wpnonce":  	dirpro_data.dirwpnonce,
	};
	jQuery.ajax({
		 url : ajaxurl,
		 dataType : "json",		
		 type : "post",		 
		 data : search_params2,								 	
		 success : function(response){	
			
			
			if(response.code=='not-done'){ 
				row_start=response.row_done;
				listinghub_save_csv_file_to_database(); 	
				
			}else{
						
				jQuery("#ep3").hide();
				jQuery("#ep4").show();
				jQuery('#step-4').removeClass('disabled');
				jQuery('#step-3').removeClass('active');
				jQuery('#step-4').addClass('complete ');
			}
			
			console.log('...finalerp_import_files....');			  
		}
	  });
						
	
}
function listinghub_upload_status_checker(){
	"use strict";
	var importTimer;		
	/* Every second, we're going to poll the server to request for the
	 * value of the progress being made. This is using the get_import_status
	 * function on the server-side.
	 *
	 * If the response is -1, then the operation is done and we can stop the
	 * timer; otherwise, we can update the progressbar.
	 */
	importTimer = setInterval(function() {
		// Get the current status of the update
		
			var search_params3={
					"action"  :  "listinghub_eppro_get_import_status",					
			};
			jQuery.ajax({
				 url : ajaxurl,
				 dataType : "json",
				 type : "post",
				 
				 data : search_params3,
				 success : function(response){ 				
					if ( '-1' === response.code ) { // done****
						// Set the progress bar equal to 100 and clear the timer
						var ii='100';
						jQuery("#progress-bar-csv").css("width", ii + "%").text(ii + " %");						
							clearInterval(importTimer);

					} else {
						
						var i = response.progress; 						
						jQuery("#progress-bar-csv").css("width", i + "%").text(i + " %");						

					}						  
				}
			  });

	}, 6000 );

}



function listinghub_upload_csv_file(update_div_id){
	"use strict";

var product_csv_frame;
product_csv_frame = wp.media.frames.downloadable_file = wp.media({
  // Set the title of the modal.
  title:  'CSV File ',
  button: {
    text:  'Select',
  },
  multiple: false,
  
});
product_csv_frame.on( 'select', function() {
  var selection = product_csv_frame.state().get('selection');
  selection.map( function( attachment ) {
    attachment = attachment.toJSON();    
    if( attachment.id ) {		
      jQuery('#uploaded_csv_file_name').text(attachment.filename);     
      jQuery('#erp_csv_id').val(attachment.id);
	  // for create csv files 
	   listinghub_update_csv_file();
		
    }
  });

});
product_csv_frame.open();


 }