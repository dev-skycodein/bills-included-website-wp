

 
    
    "use strict";
var ajaxurl = search_data.ajaxurl;
var loader_image = search_data.loading_image;
var neartome = search_data.neartome;
var epjbdir_map_radius = search_data.epjbdir_map_radius;
jQuery(function() {
    jQuery('#reset').on('click', function () {		
		window.location = window.location.href.split("?")[0];
	});
});
jQuery( document ).ready(function() {		
	if(jQuery('#map_address').length){
		// Initialize an empty map without layers (invisible map)
		var map = L.map('map_address', {
			center: [40.7259, -73.9805], // Map loads with this location as center
			zoom: 12,
			scrollWheelZoom: true,
			zoomControl: false,
			attributionControl: false,
		});   
		//Geocoder options
		var geocoderControlOptions = {
			bounds: false,          //To not send viewbox
			markers: false,         //To not add markers when we geocoder
			attribution: null,      //No need of attribution since we are not using maps
			expanded: true,         //The geocoder search box will be initialized in expanded mode
			panToPoint: false,       //Since no maps, no need to pan the map to the geocoded-selected location
			params: {               //Set dedupe parameter to remove duplicate results from Autocomplete
				dedupe: 1,
			}
		}
		//Initialize the geocoder
		var geocoderControl = new L.control.geocoder('pk.87f2d9fcb4fdd8da1d647b46a997c727', geocoderControlOptions).addTo(map).on('select', function (e) {		
			jQuery('#address_longitude').val(e.latlng.lng);
			jQuery('#address_latitude').val(e.latlng.lat);
		});
		//Get the "search-box" div
		var searchBoxControl = document.getElementById("search-box");
		//Get the geocoder container from the leaflet map
		var geocoderContainer = geocoderControl.getContainer();
		//Append the geocoder container to the "search-box" div
		searchBoxControl.appendChild(geocoderContainer);        
	}
});

jQuery( document ).ready(function() { 
	jQuery("#near_km").slider({ id: "slider12a", min: 10, max: 1000, value: neartome ,tooltip: 'always',formatter: function(value) {
		return value+' '+epjbdir_map_radius;
	}});
});
jQuery( document ).ready(function() { 
	for (var key in search_data.active_search_fields) {
		if (search_data.active_search_fields.hasOwnProperty(key)) {	
			
			var place_holder_text= search_data.data_for_translate[key];
			if(search_data.active_search_fields[key]=='multi-checkbox'){
				let str = key;
				let strresult = str.replace("_", " ");
				let strresult2 = strresult.replace("-", " ");					
				jQuery('#'+key+'id').multiselect({						
					columns: 1,					
					search   : true,	
					selectAll :true,
					placeholder:search_data.select_text+' '+place_holder_text ,
					texts   : {								
						search : search_data.search,
						selectAll: search_data.selectAll,
						unselectAll:search_data.unselectAll,
					}
				});
			}
			if(search_data.active_search_fields[key]=='multi-checkbox-group'){					
				let str = key;
				let strresult = str.replace("_", " ");
				let strresult2 = strresult.replace("-", " ");					
				jQuery('#'+key+'id').multiselect({
					columns: 1,					
					search   : true,	
					selectGroup:true,	
					showOptGroups :true,
					placeholder:search_data.select_text+' '+place_holder_text ,
					texts   : {								
						search : search_data.search,
						selectAll: search_data.selectAll,
						unselectAll:search_data.unselectAll,
						searchNoResult :search_data.searchNoResult, 
						selectedOptions :search_data.selectedOptions,
						noneSelected :search_data.noneSelected,	
					}
				});
			}
			if(search_data.active_search_fields[key]=='datefield'){
				if (jQuery( "#"+key+'id' )[0]){	
					jQuery( "#"+key+'id' ).datepicker( );
				}
			}
		}
	}
});

function listinghub_getLocation() {
	if(jQuery('#neartome' ).is(":checked")){
		if (navigator.geolocation) { 
			navigator.geolocation.getCurrentPosition(
				// Success function
				listinghub_showPosition, 
				// Error function
				null, 
				// Options. See MDN for details.
				{
					enableHighAccuracy: true,
					timeout: 5000,
					maximumAge: 0
				});
		} 
		if (jQuery("#sort_listing_div")[0]){	
			jQuery("#sort_listing_div").hide();
		}
		
	}else{
		jQuery('#latitude').val('');
		jQuery('#longitude').val('');
		if (jQuery("#sort_listing_div")[0]){	
			jQuery("#sort_listing_div").show();
		}
	}
}
function listinghub_showPosition(position) { 	 
	jQuery('#latitude').val(position.coords.latitude );
	jQuery('#longitude').val(position.coords.longitude );
}
function listinghub_contact_close(){
	jQuery.colorbox.close();
}
jQuery(document).ready(function(){   
	listinghub_search_resize();	
	jQuery(window).on('resize', function(){
		listinghub_search_resize();
	});   
})
function listinghub_search_resize(){	
		var listingdata_width= jQuery("#ep_search_fields_all").width();			
		jQuery( ".ep_search_field" ).removeClass( "col-md-3" );		
	  if(listingdata_width>1500  ){ 
		jQuery( ".ep_search_field" ).addClass( "col-md-3  " );			
	   }else if(listingdata_width>1000 && listingdata_width<1500 ){  
			jQuery( ".ep_search_field" ).addClass( " col-md-4 " );			
	   }else if(listingdata_width>600 && listingdata_width<1000  ){
			jQuery( ".ep_search_field" ).addClass( "col-md-6" );			
	   }else if(listingdata_width>500 && listingdata_width<600  ){ 
			jQuery( ".ep_search_field" ).addClass( "col-md-6 " );			
	  }else { 
			jQuery( ".ep_search_field" ).addClass( "col-md-12 " );				
	   }
}