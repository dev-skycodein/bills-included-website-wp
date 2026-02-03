<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php
	/*********** Open Status checker ************** */
	if (!function_exists('listinghub_check_time')) {
		function listinghub_check_time($listingid) {
			   $status = esc_html__('Day Off!', 'listinghub');
			$storeSchedule = get_post_meta($listingid, '_opening_time', true);
			
			if (is_array($storeSchedule)) {
				$getClosestTimezone = listinghub_getClosestTimezone(get_post_meta($listingid, 'latitude', true), get_post_meta($listingid, 'longitude', true));
				$timeObject = new DateTime($getClosestTimezone);
				$timestamp = $timeObject->getTimestamp();
				
				// Default status
				$status = esc_html__('Closed Now!', 'listinghub');
				// Get current time object
				$currentTime = (new DateTime())->setTimestamp($timestamp);
				$currentDay = date('D', $timestamp);

				// Check if there is a schedule for the current day
				if (isset($storeSchedule[$currentDay])) {
					foreach ($storeSchedule[$currentDay] as $startTime => $endTime) {
						// Attempt to create time objects from start/end times in both 12-hour and 24-hour formats
						$startTime12 = DateTime::createFromFormat('h:i A', $startTime);
						$endTime12 = DateTime::createFromFormat('h:i A', $endTime);
						$startTime24 = DateTime::createFromFormat('H:i', $startTime);
						$endTime24 = DateTime::createFromFormat('H:i', $endTime);

						// Check if current time is within a range
						if (
							($startTime12 && $endTime12 && $startTime12 < $currentTime && $currentTime < $endTime12) ||
							($startTime24 && $endTime24 && $startTime24 < $currentTime && $currentTime < $endTime24)
						) {
							$status = esc_html__('Open Now', 'listinghub');
							break;
						}
					}
				}
			}
			return $status;
		}
	}
	if (!function_exists('listinghub_getClosestTimezone')) {
		function listinghub_getClosestTimezone($lat, $lng)
		{
			if (!empty($lat) && !empty($lng)) {
				$diffs = array();
				foreach (DateTimeZone::listIdentifiers() as $timezoneID) {
					$timezone = new DateTimeZone($timezoneID);
					$location = $timezone->getLocation();
					$tLat = $location['latitude'];
					$tLng = $location['longitude'];
					$diffLat = abs($lat - $tLat);
					$diffLng = abs($lng - $tLng);
					$diff = $diffLat + $diffLng;
					$diffs[$timezoneID] = $diff;
				}
				$timezone = array_keys($diffs, min($diffs));
				$timestamp = time();
				date_default_timezone_set($timezone[0]);
				$zones_GMT = gmdate('O', $timestamp) / 100;
				if(isset($timezone[0])){
					return $timezone[0];
					}else{
					return 'America/New_York';
				}
			}else{
				return 'America/New_York';
			}
		}
	}	