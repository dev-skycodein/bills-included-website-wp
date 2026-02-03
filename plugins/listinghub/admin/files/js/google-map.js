"use strict";
var markers = [];
var infowindow = new google.maps.InfoWindow();
var dirs =JSON.parse(listinghub_map_data.dirs_json);
var top_image = listinghub_map_data.top_image;
var infotitle = listinghub_map_data.infotitle;
var infolocation = listinghub_map_data.infolocation;
var indirection = listinghub_map_data.indirection;
var direction_text= listinghub_map_data.direction_text;
var infolinkdetail= listinghub_map_data.infolinkdetail;

function listinghub_initialize() {
	var center = new google.maps.LatLng(listinghub_map_data.ins_lat, listinghub_map_data.ins_lng);
	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: parseInt(listinghub_map_data.dir_map_zoom),
		center: center,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		backgroundColor: 'none',
	});
	
	var min = .999999;
	var max = 1.000002;
	if(dirs!=''){
		for (var i = 0; i < dirs.length; i++) {
			var new_lat= dirs[i].lat  * (Math.random() * (max - min) + min);
			var new_lng= dirs[i].lng  * (Math.random() * (max - min) + min);
			var custom_icon = {
				url: dirs[i].marker_icon, // url
				scaledSize: new google.maps.Size(50, 68), // scaled size	
			};
			
			var latLng = new google.maps.LatLng(new_lat,new_lng);
			var marker = new google.maps.Marker({
				position: latLng,
				map: map,
				icon: custom_icon,
			});
			markers.push(marker);
			google.maps.event.addListener(marker, 'mouseover', (function(marker, i) {
				return function() {	
				
					var marker_html='<div class="card-map">'+(top_image=='yes'? '<img src="'+dirs[i].image+'" class="top-img">':'')+'<div class="card-body">'+(infolinkdetail=='yes'?'<a href="'+dirs[i].dlink +'">':'')+''+( infotitle=='yes'?'<h5 class="card-title-map">'+dirs[i].title+'</h5>':'')+''+(infolinkdetail=='yes'?'</a>':'')+' '+( infolocation=='yes'?'<p class="card-text-map"><i class="fas fa-map-marker-alt mr-1"></i>'+dirs[i].locations+'</p>':'')+' '+( indirection=='yes'?'<p class="card-text-map"><a class="btn btn-outline-info btn-sm" href="https://www.google.com/maps/dir/?api=1&destination='+dirs[i].lat+','+dirs[i].lng+'"  target="_blank" >'+direction_text+'</a></p>':'')+'</div></div>';
										
					infowindow.setContent(marker_html);
									
					infowindow.open(map, marker);
				}
			})(marker, i));
			// Close infobox on click Map
			google.maps.event.addListener(map, "click", function(event) {
				infowindow.close();
			});
			marker.addListener('click', function() {
				infowindow.open(map, this);
			});
		}
	}
	var markerCluster = new MarkerClusterer(map, markers);
}
function listinghub_cs_toggle_street_view(btn) {
	"use strict";
	var toggle = panorama.getVisible();
	if (toggle == false) {
		if(btn == 'streetview'){
			panorama.setVisible(true);
		}
		} else {
		if(btn == 'mapview'){
			panorama.setVisible(false);
		}
	}
}
google.maps.event.addDomListener(window, 'load', listinghub_initialize);

jQuery( function() {
	jQuery(".listingdata-col").on("mouseover", function () { 
		var id=this.id;
			
		var marker_html='<div class="card-map">'+(top_image=='yes'? '<img src="'+dirs[id].image+'" class="top-img">':'')+'<div class="card-body">'+(infolinkdetail=='yes'?'<a href="'+dirs[id].dlink +'">':'')+''+( infotitle=='yes'?'<h5 class="card-title-map">'+dirs[id].title+'</h5>':'')+''+(infolinkdetail=='yes'?'</a>':'')+''+( infolocation=='yes'?'<p class="card-text-map"><i class="fas fa-map-marker-alt mr-1"></i>'+dirs[id].locations+'</p>':'')+' '+( indirection=='yes'?'<p class="card-text-map"><a class="btn btn-outline-info btn-sm" href="https://www.google.com/maps/dir/?api=1&destination='+dirs[id].lat+','+dirs[id].lng+'"  target="_blank" >'+direction_text+'</a></p>':'')+'</div></div>';
				
		infowindow.setContent(marker_html);	
		infowindow.open(map, markers[id]);
	});
});
jQuery( function() {		
	setTimeout(function(){	
	},500)
});
