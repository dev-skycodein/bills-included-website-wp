/**
 * ListingHub Search Bar – behaviour
 * Shortcode: [listinghub_search_bar]
 * When Google API key is set, address/postcode autocomplete runs and sets lat/lng for radius search.
 */
(function ($) {
   'use strict';

   var placesAutocompleteAttached = false;

   function attachPlacesAutocomplete() {
	   if (placesAutocompleteAttached) return;
	   var input = document.getElementById('listinghub_sb_input_search');
	   if (!input || typeof google === 'undefined' || !google.maps || !google.maps.places) return false;
	   placesAutocompleteAttached = true;
	   var autocomplete = new google.maps.places.Autocomplete(input, { types: ['geocode'] });
	   var $form = $('.listinghub-search-bar-wrap').find('#listinghub_search_bar_form');
	   var $lat = $form.find('#listinghub_sb_latitude');
	   var $lng = $form.find('#listinghub_sb_longitude');
	   var $addrLat = $form.find('#listinghub_sb_address_latitude');
	   var $addrLng = $form.find('#listinghub_sb_address_longitude');
	   autocomplete.addListener('place_changed', function () {
		   var place = autocomplete.getPlace();
		   if (!place.geometry || !place.geometry.location) return;
		   var lat = place.geometry.location.lat();
		   var lng = place.geometry.location.lng();
		   $lat.val(lat);
		   $lng.val(lng);
		   if ($addrLat.length) $addrLat.val(lat);
		   if ($addrLng.length) $addrLng.val(lng);
		   input.value = place.formatted_address || place.name || input.value;
		   $('.listinghub-search-bar-wrap').find('.listinghub-sb-search-wrap').addClass('has-text');
	   });
	   return true;
   }

   // Called by Google Maps script when Places API is ready. Must be defined before we load the script.
   window.listinghub_sb_places_ready = function () {
	   var input = document.getElementById('listinghub_sb_input_search');
	   if (!input && typeof google !== 'undefined' && google.maps && google.maps.places) {
		   // Input not in DOM yet (e.g. shortcode in tab/lazy content); retry a few times.
		   var attempts = 0;
		   var maxAttempts = 30;
		   var interval = setInterval(function () {
			   attempts++;
			   if (attachPlacesAutocomplete() || attempts >= maxAttempts) clearInterval(interval);
		   }, 250);
		   return;
	   }
	   attachPlacesAutocomplete();
   };

   // Load Google Places script only after callback is defined (avoids "is not a function" when script loads first).
   var sbData = typeof window.listinghub_sb_data !== 'undefined' ? window.listinghub_sb_data : {};
   if (sbData.places_api_key) {
	   var script = document.createElement('script');
	   script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' + encodeURIComponent(sbData.places_api_key) + '&callback=listinghub_sb_places_ready';
	   script.async = true;
	   script.defer = true;
	   document.head.appendChild(script);
   }

   function init() {
	   var $wrap = $('.listinghub-search-bar-wrap');
	   if (!$wrap.length) return;

	   // If Google Places already loaded (e.g. callback ran), attach autocomplete now that DOM is ready.
	   if (sbData.places_api_key && typeof google !== 'undefined' && google.maps && google.maps.places) {
		   attachPlacesAutocomplete();
	   }

	   var $form = $wrap.find('#listinghub_search_bar_form');
	   var $searchInput = $wrap.find('#listinghub_sb_input_search');
	   var $clearBtn = $wrap.find('#listinghub_sb_clear');
	   var $bedsTrigger = $wrap.find('#listinghub_sb_beds_trigger');
	   var $bedsPanel = $wrap.find('#listinghub_sb_beds_panel');
	   var $bathsTrigger = $wrap.find('#listinghub_sb_baths_trigger');
	   var $bathsPanel = $wrap.find('#listinghub_sb_baths_panel');
	   var $renterTrigger = $wrap.find('#listinghub_sb_renter_trigger');
	   var $renterPanel = $wrap.find('#listinghub_sb_renter_panel');
	   var $filtersBtn = $wrap.find('#listinghub_sb_filters_btn');
	   var $filtersPanel = $wrap.find('#listinghub_sb_filters_panel');
	   var $locationsTrigger = $wrap.find('#listinghub_sb_locations_trigger');
	   var $locationsPopup = $wrap.find('#listinghub_sb_locations_popup');
	   var $locationsSearch = $wrap.find('#listinghub_sb_locations_search');
	   var $locationsList = $wrap.find('#listinghub_sb_locations_list');
	   var $locCbs = $wrap.find('.listinghub-sb-loc-cb');
	   var $selectAllLoc = $wrap.find('#listinghub_sb_locations_select_all');
	   var $chips = $wrap.find('.listinghub-sb-chip');
	   var $simpleTriggers = $wrap.find('.listinghub-sb-simple-trigger');
	   var $propertyTrigger = $wrap.find('#listinghub_sb_property_trigger');
	   var $propertyPopup = $wrap.find('#listinghub_sb_property_popup');
	   var $propertySearch = $wrap.find('#listinghub_sb_property_search');
	   var $propertyList = $wrap.find('#listinghub_sb_property_list');
	   var $propertySelectAll = $wrap.find('#listinghub_sb_property_select_all');
	   var $propertyLabel = $wrap.find('.listinghub-sb-property-label');
	   var $locationsLabel = $wrap.find('.listinghub-sb-locations-label');
	   var $renterLabel = $wrap.find('.listinghub-sb-renter-label');
	   var propertyLabelBase = $propertyLabel.text();
	   var locationsLabelBase = $locationsLabel.text();
	   var renterLabelBase = $renterLabel.text();

	   function updateCounts() {
		   if ($propertyLabel.length) {
			   var propCount = $propertyList.find('.listinghub-sb-property-cb:checked').length;
			   $propertyLabel.text(propCount > 0 ? (propertyLabelBase + ' (' + propCount + ')') : propertyLabelBase);
		   }
		   if ($locationsLabel.length) {
			   var locCount = $locationsList.find('.listinghub-sb-loc-cb:checked').length;
			   $locationsLabel.text(locCount > 0 ? (locationsLabelBase + ' (' + locCount + ')') : locationsLabelBase);
		   }
		   if ($renterLabel.length) {
			   var renterCount = $wrap.find('.listinghub-sb-renter-cb:checked').length;
			   $renterLabel.text(renterCount > 0 ? (renterLabelBase + ' (' + renterCount + ')') : renterLabelBase);
		   }
	   }

	   // Close archive sort dropdown (used when opening any search filter)
	   function closeArchiveSort() {
		   var sortWrap = document.getElementById('listinghub_archive_sort');
		   if (!sortWrap) return;
		   var sortTrigger = document.getElementById('listinghub_archive_sort_trigger');
		   var sortDropdown = document.getElementById('listinghub_archive_sort_dropdown');
		   if (sortDropdown && !sortDropdown.hasAttribute('hidden')) {
			   sortDropdown.setAttribute('hidden', '');
		   }
		   if (sortTrigger) {
			   sortTrigger.setAttribute('aria-expanded', 'false');
		   }
		   sortWrap.classList.remove('is-open');
	   }
	   var $propertyLabel = $wrap.find('.listinghub-sb-property-label');
	   var $locationsLabel = $wrap.find('.listinghub-sb-locations-label');
	   var $renterLabel = $wrap.find('.listinghub-sb-renter-label');
	   var propertyLabelBase = $propertyLabel.text();
	   var locationsLabelBase = $locationsLabel.text();
	   var renterLabelBase = $renterLabel.text();

	   function updateCounts() {
		   // Property type (count checked .listinghub-sb-property-cb)
		   if ($propertyLabel.length) {
			   var propCount = $propertyList.find('.listinghub-sb-property-cb:checked').length;
			   $propertyLabel.text(propCount > 0 ? (propertyLabelBase + ' (' + propCount + ')') : propertyLabelBase);
		   }
		   // Locations (count checked .listinghub-sb-loc-cb)
		   if ($locationsLabel.length) {
			   var locCount = $locationsList.find('.listinghub-sb-loc-cb:checked').length;
			   $locationsLabel.text(locCount > 0 ? (locationsLabelBase + ' (' + locCount + ')') : locationsLabelBase);
		   }
		   // Renter type (count checked .listinghub-sb-renter-cb)
		   if ($renterLabel.length) {
			   var renterCount = $wrap.find('.listinghub-sb-renter-cb:checked').length;
			   $renterLabel.text(renterCount > 0 ? (renterLabelBase + ' (' + renterCount + ')') : renterLabelBase);
		   }
	   }

	   // Clear search (and lat/lng so radius is not applied to old location)
	   $clearBtn.on('click', function () {
		   $searchInput.val('').focus();
		   $wrap.find('.listinghub-sb-search-wrap').removeClass('has-text');
		   $form.find('#listinghub_sb_latitude, #listinghub_sb_longitude, #listinghub_sb_address_latitude, #listinghub_sb_address_longitude').val('');
	   });

	   $searchInput.on('input', function () {
		   $wrap.find('.listinghub-sb-search-wrap').toggleClass('has-text', $(this).val().length > 0);
	   }).trigger('input');

	   // Search icon click = submit (form already has one submit button; extra submit is fine)
	   $wrap.find('.listinghub-sb-icon-search').on('click', function () {
		   $form.submit();
	   });

	   // Beds dropdown
	   $bedsTrigger.on('click', function (e) {
		   e.preventDefault();
		   e.stopPropagation();
		   closeArchiveSort();
		   var open = $bedsPanel.attr('hidden') === undefined;
		   $bedsPanel.attr('hidden', open ? true : null);
		   $bedsTrigger.attr('aria-expanded', !open);
		   $wrap.find('.listinghub-sb-beds-wrap').toggleClass('is-open', !open);
		   closeRenter();
		   closeLocations();
		   closeSimple();
		   closeProperty();
		   if ($bathsPanel.length) {
			   $bathsPanel.attr('hidden', true);
			   $bathsTrigger.attr('aria-expanded', 'false');
		   }
	   });

	   // Renter type dropdown
	   $renterTrigger.on('click', function (e) {
		   e.preventDefault();
		   e.stopPropagation();
		   closeArchiveSort();
		   var open = $renterPanel.attr('hidden') === undefined;
		   $renterPanel.attr('hidden', open ? true : null);
		   $renterTrigger.attr('aria-expanded', !open);
		   $wrap.find('.listinghub-sb-renter-wrap').toggleClass('is-open', !open);
		   closeBeds();
		   closeLocations();
		   closeSimple();
		   closeProperty();
		   if ($bathsPanel.length) {
			   $bathsPanel.attr('hidden', true);
			   $bathsTrigger.attr('aria-expanded', 'false');
		   }
	   });

	   function closeBeds() {
		   $bedsPanel.attr('hidden', true);
		   $bedsTrigger.attr('aria-expanded', 'false');
		   $wrap.find('.listinghub-sb-beds-wrap').removeClass('is-open');
		   if ($bathsPanel.length) {
			   $bathsPanel.attr('hidden', true);
			   $bathsTrigger.attr('aria-expanded', 'false');
		   }
	   }

	   function closeRenter() {
		   $renterPanel.attr('hidden', true);
		   $renterTrigger.attr('aria-expanded', 'false');
		   $wrap.find('.listinghub-sb-renter-wrap').removeClass('is-open');
	   }

	   function closeSimple() {
		   $wrap.find('.listinghub-sb-simple-panel').attr('hidden', true);
		   $simpleTriggers.attr('aria-expanded', 'false');
		   $wrap.find('.listinghub-sb-simple-wrap').removeClass('is-open');
	   }

	   function closeProperty() {
		   if ($propertyPopup.length) $propertyPopup.attr('hidden', true);
		   if ($propertyTrigger.length) $propertyTrigger.attr('aria-expanded', 'false');
		   $wrap.find('.listinghub-sb-property-section').removeClass('is-open');
	   }

	   // Close dropdowns when clicking outside any dropdown/trigger area
	   $(document).on('click.listinghub_sb', function (e) {
		   if ($(e.target).closest('.listinghub-sb-beds-wrap, .listinghub-sb-renter-wrap, .listinghub-sb-simple-wrap, .listinghub-sb-property-section, .listinghub-sb-baths-wrap, .listinghub-sb-locations-section').length) {
			   return;
		   }
		   closeBeds();
		   closeRenter();
		   closeLocations();
		   closeSimple();
		   closeProperty();
		   if ($bathsPanel.length) {
			   $bathsPanel.attr('hidden', true);
			   $bathsTrigger.attr('aria-expanded', 'false');
		   }
	   });

	   // Filters panel
	   $filtersBtn.on('click', function (e) {
		   e.preventDefault();
		   var open = $filtersPanel.attr('hidden') === undefined;
		   $filtersPanel.attr('hidden', open ? true : null);
		   $filtersBtn.attr('aria-expanded', !open);
	   });

	   // If shortcode has url param (.filter-url), animate/toggle field visibility on Filters click.
	   $wrap.find('#listinghub_sb_filters_btn').on('click', function () {
		   if (!$wrap.hasClass('filter-url')) return;
		   $wrap.css('opacity', '0');
		   $wrap.toggleClass('filter-url-open');
		   setTimeout(function () {
			   $wrap.find('.listinghub-sb-radius-wrap, .listinghub-sb-price-min-wrap, .listinghub-sb-price-max-wrap, .listinghub-sb-renter-wrap, .listinghub-sb-btn-search').toggleClass('f-d-none');
			   $wrap.css('opacity', '1');
		   }, 300);
	   });

	   // Mobile filters: add class so popup can be styled full-screen
	   $wrap.find('.listinghub-sb-btn-mobile-filters').on('click', function () {
		   $wrap.addClass('mobile-filter');
	   });
	   $wrap.find('.filter-popup-close').on('click', function () {
		   $wrap.removeClass('mobile-filter');
	   });

	   // Locations popup
	   function closeLocations() {
		   $locationsPopup.attr('hidden', true);
		   $locationsTrigger.attr('aria-expanded', 'false');
		   $wrap.find('.listinghub-sb-locations-section').removeClass('is-open');
	   }

	   $locationsTrigger.on('click', function (e) {
		   e.preventDefault();
		   e.stopPropagation();
		   closeArchiveSort();
		   var open = $locationsPopup.attr('hidden') === undefined;
		   closeBeds();
		   closeRenter();
		   closeSimple();
		   closeProperty();
		   if ($bathsPanel.length) {
			   $bathsPanel.attr('hidden', true);
			   $bathsTrigger.attr('aria-expanded', 'false');
			   $wrap.find('.listinghub-sb-baths-wrap').removeClass('is-open');
		   }
		   $locationsPopup.attr('hidden', open ? true : null);
		   $locationsTrigger.attr('aria-expanded', !open);
		   $wrap.find('.listinghub-sb-locations-section').toggleClass('is-open', !open);
		   if (!open) $locationsSearch.focus();
	   });

	   // Simple dropdowns (radius, min/max price)
	   $simpleTriggers.on('click', function (e) {
		   e.preventDefault();
		   e.stopPropagation();
		   closeArchiveSort();
		   var $btn = $(this);
		   var targetId = $btn.attr('aria-controls');
		   if (!targetId) return;
		   var $panel = $('#' + targetId);
		   // First close all other simple panels, then recompute open state for this one
		   var wasOpen = ($panel.attr('hidden') === undefined);
		   closeSimple();
		   closeBeds();
		   closeRenter();
		   closeLocations();
		   closeProperty();
		   var nowOpen = !wasOpen;
		   $panel.attr('hidden', nowOpen ? null : true);
		   $btn.attr('aria-expanded', nowOpen);
		   $btn.closest('.listinghub-sb-simple-wrap').toggleClass('is-open', nowOpen);
	   });

	   $wrap.find('.listinghub-sb-simple-option').on('click', function (e) {
		   e.preventDefault();
		   var $opt = $(this);
		   var target = $opt.data('target');
		   var value = $opt.data('value');
		   if (!target) return;
		   var $hidden = $('#' + (target === 'near_km' ? 'listinghub_sb_near_km' : (target === 'sfsearch_price_min' ? 'listinghub_sb_price_min' : 'listinghub_sb_price_max')));
		   if ($hidden.length === 0) {
			   // Fallback: find by name
			   $hidden = $form.find('input[name=\"' + target + '\"]');
		   }
		   $hidden.val(value);
		   var labelText = $opt.text();
		   var $wrapSimple = $opt.closest('.listinghub-sb-simple-wrap');
		   $wrapSimple.find('.listinghub-sb-simple-label').text(labelText);
		   var $btn = $wrapSimple.find('.listinghub-sb-simple-trigger');
		   var targetId = $btn.attr('aria-controls');
		   if (targetId) {
			   var $panel = $('#' + targetId);
			   $panel.attr('hidden', true);
			   $btn.attr('aria-expanded', 'false');
			   $wrapSimple.removeClass('is-open');
		   }
	   });

	   // Property type: same as Locations (search + Select all + list)
	   if ($propertyTrigger.length) {
		   $propertyTrigger.on('click', function (e) {
			   e.preventDefault();
			   e.stopPropagation();
			   closeArchiveSort();
			   var $btn = $(this);
			   var open = $propertyPopup.attr('hidden') === undefined;
			   closeBeds();
			   closeRenter();
			   closeLocations();
			   closeSimple();
			   $propertyPopup.attr('hidden', open ? true : null);
			   $btn.attr('aria-expanded', !open);
			   $wrap.find('.listinghub-sb-property-section').toggleClass('is-open', !open);
			   if (!open) $propertySearch.focus();
		   });
	   }

	   $propertySearch.on('input', function () {
		   var q = $(this).val().toLowerCase();
		   $propertyList.find('.listinghub-sb-check-item').each(function () {
			   var $item = $(this);
			   var text = $item.find('span').text().toLowerCase();
			   $item.toggleClass('hide-by-search', q.length > 0 && text.indexOf(q) === -1);
		   });
	   });

	   $propertySelectAll.on('change', function () {
		   var checked = $(this).prop('checked');
		   $propertyList.find('.listinghub-sb-check-item:not(.hide-by-search)').find('.listinghub-sb-property-cb').prop('checked', checked);
		   updateCounts();
	   });

	   // Bathrooms dropdown inside filters (min/max like Beds)
	   if ($bathsTrigger.length && $bathsPanel.length) {
		   $bathsTrigger.on('click', function (e) {
			   e.preventDefault();
			   e.stopPropagation();
			   // If archive sort dropdown is open, hide it when Bathrooms is opened
			   closeArchiveSort();
			   var open = $bathsPanel.attr('hidden') === undefined;
			   closeBeds();
			   closeRenter();
			   closeLocations();
			   closeSimple();
			   closeProperty();
			   $bathsPanel.attr('hidden', open ? true : null);
			   $bathsTrigger.attr('aria-expanded', !open);
			   $wrap.find('.listinghub-sb-baths-wrap').toggleClass('is-open', !open);
		   });
	   }

	   $locationsSearch.on('input', function () {
		   var q = $(this).val().toLowerCase();
		   $locationsList.find('.listinghub-sb-check-item').each(function () {
			   var $item = $(this);
			   var text = $item.find('span').text().toLowerCase();
			   $item.toggleClass('hide-by-search', q.length > 0 && text.indexOf(q) === -1);
		   });
	   });

	   $selectAllLoc.on('change', function () {
		   var checked = $(this).prop('checked');
		   $locationsList.find('.listinghub-sb-check-item:not(.hide-by-search)').find('.listinghub-sb-loc-cb').prop('checked', checked);
		   updateCounts();
	   });

	   // Chips: highlight when clicked
	   $chips.on('click', function () {
		   var $chip = $(this);
		   $chips.removeClass('highlight');
		   $chip.addClass('highlight');
	   });

	   // Animate arrows on radius / price selects (open / close)
	   $wrap.find('.listinghub-sb-dropdown-wrap .listinghub-sb-select').on('focus mousedown', function () {
		   $(this).closest('.listinghub-sb-dropdown-wrap').addClass('is-open');
	   }).on('blur change', function () {
		   $(this).closest('.listinghub-sb-dropdown-wrap').removeClass('is-open');
	   });

	   // Renter type: "Show all" unchecks tag checkboxes so no tag is sent
	   $wrap.find('.listinghub-sb-renter-any').on('change', function () {
		   if ($(this).prop('checked')) $wrap.find('.listinghub-sb-renter-cb').prop('checked', false);
		   updateCounts();
	   });
	   $wrap.find('.listinghub-sb-renter-cb').on('change', function () {
		   if ($(this).prop('checked')) $wrap.find('.listinghub-sb-renter-any').prop('checked', false);
		   updateCounts();
	   });

	   // Property type checkbox changes -> update count
	   $propertyList.on('change', '.listinghub-sb-property-cb', function () {
		   updateCounts();
	   });

	   // Individual locations checkbox changes -> update count
	   $locCbs.on('change', function () {
		   updateCounts();
	   });

	   // Initial counts on load (for pre-selected filters)
	   updateCounts();

   }

   $(function () {
	   init();
   });
})(jQuery);
