"use strict";
function listinghub_initialize() {
	"use strict";
		var center = new google.maps.LatLng(realpro_data.ins_lat, realpro_data.ins_lng);
	var dir_map_zoom = parseInt(realpro_data.dir_map_zoom);
		
		var map = new google.maps.Map(document.getElementById('map'), {
			zoom: dir_map_zoom,
			center: center,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		var markers = [];
		var infowindow = new google.maps.InfoWindow();
		var dirs ='';
		var min = .999999;
		var max = 1.000002;
		var dirs = realpro_data.dirs;
		 
		if(dirs!=''){
			for (i = 0; i < dirs.length; i++) {
				var new_lat= dirs[i].lat  * (Math.random() * (max - min) + min);
				var new_lng= dirs[i].lng  * (Math.random() * (max - min) + min);
				var latLng = new google.maps.LatLng(new_lat,new_lng);	
				var marker = new google.maps.Marker({
					position: latLng,
					map: map,
					icon: dirs[i].marker_icon,
				});
				markers.push(marker);
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						infowindow.setContent('<div id="map-marker-info" style="overflow: auto; cursor: default; clear: both; position: relative; border-radius: 4px; padding: 15px; border-color: rgb(255, 255, 255); border-style: solid; background-color: rgb(255, 255, 255); border-width: 1px; width: 275px; height: 130px;"><div style="overflow: hidden;" class="map-marker-info"><a  style="text-decoration: none;" href="'+dirs[i].link +'">	<span style="background-image: url('+dirs[i].image+')" class="list-cover has-image"></span><span class="address"><strong>'+dirs[i].title +'</strong></span> <span class="address" style="margin-top:15px">'+dirs[i].address+'</span></a></div></div>');
						infowindow.open(map, marker);
					}
				})(marker, i));
			}
		}
		var markerCluster = new MarkerClusterer(map, markers);
	}	
	function listinghub_cs_toggle_street_view(btn) {
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
	setTimeout(function(){
		listinghub_initialize();	
		google.maps.event.trigger(map, 'resize');
	},500);

	function initialize_address() {
		var input = document.getElementById('address');
		var autocomplete = new google.maps.places.Autocomplete(input);
		google.maps.event.addListener(autocomplete, 'place_changed', function () {
			var place = autocomplete.getPlace();
			document.getElementById('latitude').value = place.geometry.location.lat();
			document.getElementById('longitude').value = place.geometry.location.lng(); 
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize_address); 