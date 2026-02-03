"use strict";
var i=0;
jQuery( function() {
		var dirs =JSON.parse(listinghub_map_data.dirs_json);

		var top_image = listinghub_map_data.top_image;
		var infotitle = listinghub_map_data.infotitle;
		var infolocation = listinghub_map_data.infolocation;
		var indirection = listinghub_map_data.indirection;
		var direction_text= listinghub_map_data.direction_text;
		var infolinkdetail= listinghub_map_data.infolinkdetail;
		
		var map = L.map('map').setView([listinghub_map_data.ins_lat, listinghub_map_data.ins_lng], listinghub_map_data.dir_map_zoom);
		
		var markers = L.markerClusterGroup();
		if(dirs!=''){
			for (i = 0; i < dirs.length; i++) {
				var listingIcon = L.icon({
					iconUrl: dirs[i].marker_icon,
					iconSize:     [50], // size of the icon					
					iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location					
					popupAnchor:  [10, -76] // point from which the popup should open relative to the iconAnchor
				});
				var new_lat= parseFloat(dirs[i].lat) ;
				var new_lng= parseFloat(dirs[i].lng) ;
				L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
				
				
				var marker = L.marker(new L.LatLng(new_lat, new_lng), {icon: listingIcon});
				var title = dirs[i].title;
				
				var marker_html='<div class="bootstrap-wrapper card  ">'+(top_image=='yes'? '<img src="'+dirs[i].image+'" class="card-img-top">':'')+'<div class="card-body">'+(infolinkdetail=='yes'?'<a href="'+dirs[i].dlink +'">':'')+''+( infotitle=='yes'?'<h5 class="card-title">'+dirs[i].title+'</h5>':'')+''+(infolinkdetail=='yes'?'</a>':'')+''+( infolocation=='yes'?'<p class="card-text"><i class="fas fa-map-marker-alt mr-1"></i>'+dirs[i].locations+'</p>':'')+' '+( indirection=='yes'?'<p class="card-text-map"><a class="btn btn-outline-info btn-sm" href="https://www.google.com/maps/dir/?api=1&destination='+dirs[i].lat+','+dirs[i].lng+'"  target="_blank" >'+direction_text+'</a></p>':'')+'</div></div>';
				
				marker.bindPopup(marker_html).openPopup();				
				markers.addLayer(marker);
			}
		}	
		map.addLayer(markers);


	jQuery(".listingdata-col").on("mouseover", function () {  
		
		var id=this.id; 		
		var listingIcon = L.icon({
			iconUrl: dirs[id].marker_icon,
			iconSize:     [50], // size of the icon					
			iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location					
			popupAnchor:  [10, -76] // point from which the popup should open relative to the iconAnchor
		});
		var hovermarker = L.marker(new L.LatLng(dirs[id].lat, dirs[id].lng ),  {icon: listingIcon});
		 map.addLayer(hovermarker);
		
		var marker_html2='<div class="bootstrap-wrapper card  ">'+(top_image=='yes'? '<img src="'+dirs[id].image+'" class="card-img-top">':'')+'<div class="card-body">'+(infolinkdetail=='yes'?'<a href="'+dirs[id].dlink +'">':'')+''+( infotitle=='yes'?'<h5 class="card-title">'+dirs[id].title+'</h5>':'')+''+(infolinkdetail=='yes'?'</a>':'')+''+( infolocation=='yes'?'<p class="card-text"><i class="fas fa-map-marker-alt mr-1"></i>'+dirs[id].locations+'</p>':'')+' '+( indirection=='yes'?'<p class="card-text-map"><a class="btn btn-outline-info btn-sm" href="https://www.google.com/maps/dir/?api=1&destination='+dirs[id].lat+','+dirs[id].lng+'"  target="_blank" >'+direction_text+'</a></p>':'')+'</div></div>';
		hovermarker.bindPopup(marker_html2).openPopup();
		
		
	});
});
