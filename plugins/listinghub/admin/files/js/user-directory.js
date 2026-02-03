 "use strict";
 var listingdata_width= jQuery("#user_directory").width();
 
jQuery(function() {	
	jQuery('#resetmainpage').on('click', function () {    
		window.location = window.location.href.split("?")[0];
	});
});
jQuery(document).ready(function(){   
    jQuery( ".listingdata-col" ).removeClass( "col-xl-12 col-xl-6 col-xl-4 col-xl-3 col-xl-2 col-lg-6 col-lg-4 col-lg-3 col-lg-2  col-md-12 col-md-6 col-md-4 col-md-3 col-md-2 col-sm-12  col-sm-2 col-12" );
	
  if(listingdata_width>1500  ){ 
	jQuery( ".listingdata-col" ).addClass( "col-xl-2 col-md-3 col-lg-2 col-12" );
		jQuery('#map').removeClass('maphide');	
   }		
  if(listingdata_width>1000 && listingdata_width<1500 ){  
		jQuery( ".listingdata-col" ).addClass( "col-xl-3 col-md-4 col-lg-3 col-12" );
		jQuery('#map').removeClass('maphide');	
   }
  if(listingdata_width>600 && listingdata_width<1000  ){
		jQuery( ".listingdata-col" ).addClass( "col-xl-4 col-md-6 col-lg-4 col-12" );	
		jQuery('#map').addClass('map50');
   }
   if(listingdata_width<600 && listingdata_width>500  ){ 
		jQuery( ".listingdata-col" ).addClass( "col-xl-6 col-md-6 col-lg-6 col-12" );
		jQuery('#map').addClass('map50');
   }
   if(listingdata_width<500   ){
		jQuery( ".listingdata-col" ).addClass( "col-xl-12 col-md-12 col-lg-6 col-12" );	
		jQuery('#map').addClass('map50');
   }
  
   
});