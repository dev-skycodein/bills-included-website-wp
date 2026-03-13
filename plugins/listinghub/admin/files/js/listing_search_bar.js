/**
 * ListingHub Search Bar – behaviour
 * Shortcode: [listinghub_search_bar]
 */
(function ($) {
	'use strict';

	function init() {
		var $wrap = $('.listinghub-search-bar-wrap');
		if (!$wrap.length) return;

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

		// Clear search
		$clearBtn.on('click', function () {
			$searchInput.val('').focus();
			$wrap.find('.listinghub-sb-search-wrap').removeClass('has-text');
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
			var open = $bedsPanel.attr('hidden') === undefined;
			$bedsPanel.attr('hidden', open ? true : null);
			$bedsTrigger.attr('aria-expanded', !open);
			$wrap.find('.listinghub-sb-beds-wrap').toggleClass('is-open', !open);
			closeRenter();
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
			var open = $renterPanel.attr('hidden') === undefined;
			$renterPanel.attr('hidden', open ? true : null);
			$renterTrigger.attr('aria-expanded', !open);
			$wrap.find('.listinghub-sb-renter-wrap').toggleClass('is-open', !open);
			closeBeds();
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
			var $panel = $wrap.find('#listinghub_sb_property_panel');
			$panel.attr('hidden', true);
			if ($propertyTrigger.length) {
				$propertyTrigger.attr('aria-expanded', 'false');
			}
			$wrap.find('.listinghub-sb-property-wrap').removeClass('is-open');
		}

		// Close dropdowns when clicking outside any dropdown/trigger area
		$(document).on('click.listinghub_sb', function (e) {
			if ($(e.target).closest('.listinghub-sb-beds-wrap, .listinghub-sb-renter-wrap, .listinghub-sb-simple-wrap, .listinghub-sb-property-wrap, .listinghub-sb-baths-wrap, .listinghub-sb-locations-section').length) {
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

		// Locations popup
		function closeLocations() {
			$locationsPopup.attr('hidden', true);
			$locationsTrigger.attr('aria-expanded', 'false');
			$wrap.find('.listinghub-sb-locations-section').removeClass('is-open');
		}

		$locationsTrigger.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var open = $locationsPopup.attr('hidden') === undefined;
			$locationsPopup.attr('hidden', open ? true : null);
			$locationsTrigger.attr('aria-expanded', !open);
			$wrap.find('.listinghub-sb-locations-section').toggleClass('is-open', !open);
			if (!open) $locationsSearch.focus();
		});

		// Simple dropdowns (radius, min/max price)
		$simpleTriggers.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
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

		// Property type dropdown inside filters (checkbox list like Renter Type)
		if ($propertyTrigger.length) {
			$propertyTrigger.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $btn = $(this);
				var $panel = $('#listinghub_sb_property_panel');
				var open = $panel.attr('hidden') === undefined;
				closeBeds();
				closeRenter();
				closeLocations();
				closeSimple();
				$panel.attr('hidden', open ? true : null);
				$btn.attr('aria-expanded', !open);
				$wrap.find('.listinghub-sb-property-wrap').toggleClass('is-open', !open);
			});
		}

		// Bathrooms dropdown inside filters (min/max like Beds)
		if ($bathsTrigger.length && $bathsPanel.length) {
			$bathsTrigger.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
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
			$locationsList.find('.listinghub-sb-loc-cb').not('.hide-by-search').prop('checked', checked);
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
		});
		$wrap.find('.listinghub-sb-renter-cb').on('change', function () {
			if ($(this).prop('checked')) $wrap.find('.listinghub-sb-renter-any').prop('checked', false);
		});

		// Property type: "Show all" unchecks property_type checkboxes (custom field)
		$wrap.find('.listinghub-sb-property-any').on('change', function () {
			if ($(this).prop('checked')) $wrap.find('.listinghub-sb-filter-section input[name="sfproperty_type[]"]').prop('checked', false);
		});
		$wrap.find('.listinghub-sb-filter-section input[name="sfproperty_type[]"]').on('change', function () {
			if ($(this).prop('checked')) $wrap.find('.listinghub-sb-property-any').prop('checked', false);
		});
	}

	$(function () {
		init();
	});
})(jQuery);
