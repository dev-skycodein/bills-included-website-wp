<?php
/**
 * ListingHub Search Bar (shortcode: listinghub_search_bar)
 * Single purple bar: search, radius, min/max price, beds, renter type, Search + Filters.
 * Filters panel: property type, bathrooms, locations.
 * Uses same params as main search (sf*, input-search, near_km, latitude, longitude) so listinghub_get_search_args works.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure constants and $atts exist (shortcode may pass empty string on some setups).
if ( ! defined( 'ep_listinghub_URLPATH' ) || ! defined( 'ep_listinghub_template' ) ) {
	return;
}
$atts = ( isset( $atts ) && is_array( $atts ) ? $atts : array() );

wp_enqueue_script( 'jquery' );
// Version by file mtime so cache updates when CSS/JS change (no stale assets).
$lh_css_path = defined( 'ep_listinghub_ABSPATH' ) ? ep_listinghub_ABSPATH . 'admin/files/css/listing_search_bar.css' : '';
$lh_js_path  = defined( 'ep_listinghub_ABSPATH' ) ? ep_listinghub_ABSPATH . 'admin/files/js/listing_search_bar.js' : '';
$lh_ver      = ( $lh_css_path !== '' && file_exists( $lh_css_path ) ) ? (string) filemtime( $lh_css_path ) : '';
$lh_ver_js   = ( $lh_js_path !== '' && file_exists( $lh_js_path ) ) ? (string) filemtime( $lh_js_path ) : $lh_ver;
wp_enqueue_style( 'listinghub_search_bar', ep_listinghub_URLPATH . 'admin/files/css/listing_search_bar.css', array(), $lh_ver );
wp_enqueue_script( 'listinghub_search_bar', ep_listinghub_URLPATH . 'admin/files/js/listing_search_bar.js', array( 'jquery' ), $lh_ver_js, true );
$dir_map_api = get_option( 'epjbdir_map_api' );
wp_localize_script( 'listinghub_search_bar', 'listinghub_sb_data', array(
	'places_api_key' => ( ! empty( $dir_map_api ) && is_string( $dir_map_api ) ? $dir_map_api : '' ),
) );

global $post, $wp;
$listinghub_directory_url = get_option( 'ep_listinghub_url' );
if ( $listinghub_directory_url === '' ) {
	$listinghub_directory_url = 'listing';
}

// Target URL: if shortcode has url=\"...\" use that, otherwise same page (without /page/N).
$form_action = '';
if ( ! empty( $atts['url'] ) ) {
	$form_action = esc_url_raw( $atts['url'] );
} else {
	$current_url = home_url( $wp->request );
	$pos         = strpos( $current_url, '/page' );
	$form_action = $pos !== false ? substr( $current_url, 0, $pos ) : $current_url;
}
$has_filter_url = ! empty( $atts['url'] );

// Preserve current search params for form values
$input_search     = isset( $_GET['input-search'] ) ? sanitize_text_field( wp_unslash( $_GET['input-search'] ) ) : '';
$near_km          = isset( $_REQUEST['near_km'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['near_km'] ) ) : '';
$price_min        = isset( $_REQUEST['sfsearch_price_min'] ) ? intval( $_REQUEST['sfsearch_price_min'] ) : '';
$price_max        = isset( $_REQUEST['sfsearch_price_max'] ) ? intval( $_REQUEST['sfsearch_price_max'] ) : '';
// For beds, keep raw values so empty truly means \"Any\" instead of 0.
$bedrooms_min     = isset( $_REQUEST['sfsearch_bedrooms_min'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfsearch_bedrooms_min'] ) ) : '';
$bedrooms_max     = isset( $_REQUEST['sfsearch_bedrooms_max'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfsearch_bedrooms_max'] ) ) : '';
$lat              = isset( $_REQUEST['latitude'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['latitude'] ) ) : '';
$lng              = isset( $_REQUEST['longitude'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['longitude'] ) ) : '';
$addr_lat         = isset( $_REQUEST['address_latitude'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['address_latitude'] ) ) : '';
$addr_lng         = isset( $_REQUEST['address_longitude'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['address_longitude'] ) ) : '';

$tax_category = $listinghub_directory_url . '-category';
$tax_tag      = $listinghub_directory_url . '-tag';
$tax_locations = $listinghub_directory_url . '-locations';

$categories = get_terms( array( 'taxonomy' => $tax_category, 'hide_empty' => true ) );
$locations  = get_terms( array( 'taxonomy' => $tax_locations, 'hide_empty' => true ) );
$tags       = get_terms( array( 'taxonomy' => $tax_tag, 'hide_empty' => true ) );

$selected_cats = array();
if ( ! empty( $_REQUEST[ 'sf' . $tax_category ] ) ) {
	$selected_cats = is_array( $_REQUEST[ 'sf' . $tax_category ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST[ 'sf' . $tax_category ] ) ) : array( sanitize_text_field( wp_unslash( $_REQUEST[ 'sf' . $tax_category ] ) ) );
}
$selected_locs = array();
if ( ! empty( $_REQUEST[ 'sf' . $tax_locations ] ) ) {
	$selected_locs = is_array( $_REQUEST[ 'sf' . $tax_locations ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST[ 'sf' . $tax_locations ] ) ) : array( sanitize_text_field( wp_unslash( $_REQUEST[ 'sf' . $tax_locations ] ) ) );
}
$selected_tags = array();
if ( ! empty( $_REQUEST[ 'sf' . $tax_tag ] ) ) {
	$selected_tags = is_array( $_REQUEST[ 'sf' . $tax_tag ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST[ 'sf' . $tax_tag ] ) ) : array( sanitize_text_field( wp_unslash( $_REQUEST[ 'sf' . $tax_tag ] ) ) );
}
$selected_property_types = array();
if ( ! empty( $_REQUEST['sfproperty_type'] ) ) {
	$selected_property_types = is_array( $_REQUEST['sfproperty_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['sfproperty_type'] ) ) : array( sanitize_text_field( wp_unslash( $_REQUEST['sfproperty_type'] ) ) );
}
$bathrooms_min = isset( $_REQUEST['sfbathrooms_min'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfbathrooms_min'] ) ) : '';
$bathrooms_max = isset( $_REQUEST['sfbathrooms_max'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfbathrooms_max'] ) ) : '';
$selected_bathrooms = array();
if ( ! empty( $_REQUEST['sfbathrooms'] ) ) {
	$selected_bathrooms = is_array( $_REQUEST['sfbathrooms'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['sfbathrooms'] ) ) : array( sanitize_text_field( wp_unslash( $_REQUEST['sfbathrooms'] ) ) );
}

// Radius options: use same unit as plugin Map setting (epjbdir_map_radius) so WP_GeoQuery distance matches.
$epjbdir_map_radius = get_option( 'epjbdir_map_radius' );
if ( $epjbdir_map_radius === 'Mile' ) {
	// Values in miles (haversine uses 3959 in plugin when Mile is set).
	$radius_options = array(
		''    => __( 'This area only', 'listinghub' ),
		'0.25' => __( 'Within ¼ mile', 'listinghub' ),
		'0.5'  => __( 'Within ½ mile', 'listinghub' ),
		'1'    => __( 'Within 1 mile', 'listinghub' ),
		'3'    => __( 'Within 3 miles', 'listinghub' ),
		'5'    => __( 'Within 5 miles', 'listinghub' ),
		'10'   => __( 'Within 10 miles', 'listinghub' ),
		'15'   => __( 'Within 15 miles', 'listinghub' ),
		'20'   => __( 'Within 20 miles', 'listinghub' ),
		'30'   => __( 'Within 30 miles', 'listinghub' ),
		'40'   => __( 'Within 40 miles', 'listinghub' ),
	);
} else {
	// Default: Km (haversine uses 6387.7 when Km is set).
	$radius_options = array(
		''    => __( 'This area only', 'listinghub' ),
		'0.4' => __( 'Within ¼ mile', 'listinghub' ),
		'0.8' => __( 'Within ½ mile', 'listinghub' ),
		'1.6' => __( 'Within 1 mile', 'listinghub' ),
		'4.8' => __( 'Within 3 miles', 'listinghub' ),
		'8'   => __( 'Within 5 miles', 'listinghub' ),
		'16'  => __( 'Within 10 miles', 'listinghub' ),
		'24'  => __( 'Within 15 miles', 'listinghub' ),
		'32'  => __( 'Within 20 miles', 'listinghub' ),
		'48'  => __( 'Within 30 miles', 'listinghub' ),
		'64'  => __( 'Within 40 miles', 'listinghub' ),
	);
}

// Default radius: "Within 5 miles" when not present in request.
if ( $near_km === '' ) {
	$near_km = ( $epjbdir_map_radius === 'Mile' ) ? '5' : '8';
}

// Price options: No min then 25 to 10000 (steps)
$price_options = array();
$price_options[] = array( 'value' => '', 'label' => __( 'No min', 'listinghub' ) );
for ( $p = 25; $p <= 10000; $p += ( $p < 500 ? 25 : ( $p < 1000 ? 50 : 500 ) ) ) {
	$price_options[] = array( 'value' => $p, 'label' => '£' . number_format( $p ) . ' pcm' );
}
$price_max_options = array( array( 'value' => '', 'label' => __( 'No max', 'listinghub' ) ) );
for ( $p = 25; $p <= 10000; $p += ( $p < 500 ? 25 : ( $p < 1000 ? 50 : 500 ) ) ) {
	$price_max_options[] = array( 'value' => $p, 'label' => '£' . number_format( $p ) . ' pcm' );
}

$beds_options = array( 0, 1, 2, 3, 4, 5 );

// Property type values from custom field meta (key: property_type), same pattern as old search meta dropdowns.
$property_type_values = array();
$args_metadata        = array(
	'post_type'      => $listinghub_directory_url,
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key' => 'property_type',
		),
	),
);
$args_metadata_arr     = new WP_Query( $args_metadata );
$args_metadata_arr_all = $args_metadata_arr->posts;
if ( ! empty( $args_metadata_arr_all ) ) {
	foreach ( $args_metadata_arr_all as $pt_post ) {
		$new_val = get_post_meta( $pt_post->ID, 'property_type', true );
		if ( is_array( $new_val ) ) {
			foreach ( $new_val as $one_val ) {
				if ( $one_val !== '' && ! in_array( $one_val, $property_type_values, true ) ) {
					$property_type_values[] = $one_val;
				}
			}
		} else {
			if ( $new_val !== '' && ! in_array( $new_val, $property_type_values, true ) ) {
				$property_type_values[] = $new_val;
			}
		}
	}
}
if ( ! empty( $property_type_values ) ) {
	asort( $property_type_values );
}
?>

<div class="listinghub-search-bar-wrap<?php echo $has_filter_url ? ' filter-url' : ''; ?>">
	<form class="listinghub-search-bar-form" id="listinghub_search_bar_form" action="<?php echo esc_url( $form_action ); ?>" method="get" role="search">
		<div class="filter-popup-close"><span class="listinghub-sb-icon listinghub-sb-icon-close" aria-hidden="true">x</span></div>
		<input type="hidden" name="latitude" id="listinghub_sb_latitude" value="<?php echo esc_attr( $lat ); ?>">
		<input type="hidden" name="longitude" id="listinghub_sb_longitude" value="<?php echo esc_attr( $lng ); ?>">
		<input type="hidden" name="address_latitude" id="listinghub_sb_address_latitude" value="<?php echo esc_attr( $addr_lat ); ?>">
		<input type="hidden" name="address_longitude" id="listinghub_sb_address_longitude" value="<?php echo esc_attr( $addr_lng ); ?>">

		<div class="listinghub-search-bar-inner">
			<!-- Search input -->
			<div class="listinghub-sb-field listinghub-sb-search-wrap">
				<label for="listinghub_sb_input_search" class="screen-reader-text"><?php esc_html_e( 'Search location or keyword', 'listinghub' ); ?></label>
				<span class="listinghub-sb-icon listinghub-sb-icon-search" aria-hidden="true"></span>
				<input type="text" name="input-search" id="listinghub_sb_input_search" class="listinghub-sb-input" placeholder="<?php esc_attr_e( 'Search location...', 'listinghub' ); ?>" value="<?php echo esc_attr( $input_search ); ?>">
				<button type="button" class="listinghub-sb-clear" id="listinghub_sb_clear" aria-label="<?php esc_attr_e( 'Clear search', 'listinghub' ); ?>" title="<?php esc_attr_e( 'Clear', 'listinghub' ); ?>">&times;</button>
			</div>

			<!-- Radius (Area) – simple dropdown like Renter Type (no checkboxes) -->
			<?php
			$radius_label = isset( $radius_options[ $near_km ] ) ? $radius_options[ $near_km ] : reset( $radius_options );
			?>
			<input type="hidden" name="near_km" id="listinghub_sb_near_km" value="<?php echo esc_attr( $near_km ); ?>">
			<div class="listinghub-sb-field listinghub-sb-dropdown-wrap listinghub-sb-simple-wrap listinghub-sb-radius-wrap<?php echo $has_filter_url ? ' f-d-none' : ''; ?>">
				<button type="button" class="listinghub-sb-simple-trigger" id="listinghub_sb_radius_trigger" aria-expanded="false" aria-controls="listinghub_sb_radius_panel">
					<span class="listinghub-sb-simple-label" data-default="<?php echo esc_attr( $radius_label ); ?>"><?php echo esc_html( $radius_label ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-simple-panel" id="listinghub_sb_radius_panel" hidden>
					<?php foreach ( $radius_options as $val => $label ) : ?>
						<button type="button" class="listinghub-sb-simple-option" data-target="near_km" data-value="<?php echo esc_attr( $val ); ?>">
							<?php echo esc_html( $label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Min price – simple dropdown like Renter Type -->
			<?php
			$price_min_label = '';
			foreach ( $price_options as $opt ) {
				if ( (string) $opt['value'] === (string) $price_min ) {
					$price_min_label = $opt['label'];
					break;
				}
			}
			if ( $price_min_label === '' && ! empty( $price_options ) ) {
				$price_min_label = $price_options[0]['label'];
			}
			?>
			<input type="hidden" name="sfsearch_price_min" id="listinghub_sb_price_min" value="<?php echo esc_attr( $price_min ); ?>">
			<div class="listinghub-sb-field listinghub-sb-dropdown-wrap listinghub-sb-simple-wrap listinghub-sb-price-min-wrap<?php echo $has_filter_url ? ' f-d-none' : ''; ?>">
				<button type="button" class="listinghub-sb-simple-trigger" id="listinghub_sb_price_min_trigger" aria-expanded="false" aria-controls="listinghub_sb_price_min_panel">
					<span class="listinghub-sb-simple-label" data-default="<?php echo esc_attr( $price_options[0]['label'] ); ?>"><?php echo esc_html( $price_min_label ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-simple-panel" id="listinghub_sb_price_min_panel" hidden>
					<?php foreach ( $price_options as $opt ) : ?>
						<button type="button" class="listinghub-sb-simple-option" data-target="sfsearch_price_min" data-value="<?php echo esc_attr( $opt['value'] ); ?>">
							<?php echo esc_html( $opt['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Max price – simple dropdown like Renter Type -->
			<?php
			$price_max_label = '';
			foreach ( $price_max_options as $opt ) {
				if ( (string) $opt['value'] === (string) $price_max ) {
					$price_max_label = $opt['label'];
					break;
				}
			}
			if ( $price_max_label === '' && ! empty( $price_max_options ) ) {
				$price_max_label = $price_max_options[0]['label'];
			}
			?>
			<input type="hidden" name="sfsearch_price_max" id="listinghub_sb_price_max" value="<?php echo esc_attr( $price_max ); ?>">
			<div class="listinghub-sb-field listinghub-sb-dropdown-wrap listinghub-sb-simple-wrap listinghub-sb-price-max-wrap<?php echo $has_filter_url ? ' f-d-none' : ''; ?>">
				<button type="button" class="listinghub-sb-simple-trigger" id="listinghub_sb_price_max_trigger" aria-expanded="false" aria-controls="listinghub_sb_price_max_panel">
					<span class="listinghub-sb-simple-label" data-default="<?php echo esc_attr( $price_max_options[0]['label'] ); ?>"><?php echo esc_html( $price_max_label ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-simple-panel" id="listinghub_sb_price_max_panel" hidden>
					<?php foreach ( $price_max_options as $opt ) : ?>
						<button type="button" class="listinghub-sb-simple-option" data-target="sfsearch_price_max" data-value="<?php echo esc_attr( $opt['value'] ); ?>">
							<?php echo esc_html( $opt['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Beds (dropdown with min/max inside) -->
			<div class="listinghub-sb-field listinghub-sb-beds-wrap<?php echo $has_filter_url ? ' f-d-none' : ''; ?>">
				<button type="button" class="listinghub-sb-beds-trigger" id="listinghub_sb_beds_trigger" aria-expanded="false" aria-controls="listinghub_sb_beds_panel">
					<span class="listinghub-sb-beds-label"><?php esc_html_e( 'Beds', 'listinghub' ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-beds-panel" id="listinghub_sb_beds_panel" hidden>
					<div class="listinghub-sb-beds-row">
						<label for="listinghub_sb_beds_min"><?php esc_html_e( 'Min beds', 'listinghub' ); ?></label>
						<select name="sfsearch_bedrooms_min" id="listinghub_sb_beds_min" class="listinghub-sb-select">
							<option value=""><?php esc_html_e( 'Any', 'listinghub' ); ?></option>
							<?php foreach ( $beds_options as $n ) : ?>
								<option value="<?php echo esc_attr( $n ); ?>" <?php selected( $bedrooms_min, $n ); ?>><?php echo esc_html( (string) $n ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="listinghub-sb-beds-row">
						<label for="listinghub_sb_beds_max"><?php esc_html_e( 'Max beds', 'listinghub' ); ?></label>
						<select name="sfsearch_bedrooms_max" id="listinghub_sb_beds_max" class="listinghub-sb-select">
							<option value=""><?php esc_html_e( 'Any', 'listinghub' ); ?></option>
							<?php foreach ( $beds_options as $n ) : ?>
								<option value="<?php echo esc_attr( $n ); ?>" <?php selected( $bedrooms_max, $n ); ?>><?php echo esc_html( (string) $n ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<!-- Renter type (uses same category taxonomy dropdown style as old \"Category\" field) -->
			<div class="listinghub-sb-field listinghub-sb-dropdown-wrap listinghub-sb-renter-wrap<?php echo $has_filter_url ? ' f-d-none' : ''; ?>">
				<button type="button" class="listinghub-sb-renter-trigger" id="listinghub_sb_renter_trigger" aria-expanded="false" aria-controls="listinghub_sb_renter_panel">
					<span class="listinghub-sb-renter-label"><?php esc_html_e( 'Renter type', 'listinghub' ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-renter-panel listinghub-sb-checklist-panel" id="listinghub_sb_renter_panel" hidden>
					<label class="listinghub-sb-check-item">
						<input type="radio" name="listinghub_sb_renter_any" value="1" class="listinghub-sb-renter-any" <?php checked( empty( $selected_cats ) ); ?>>
						<span><?php esc_html_e( 'Show all', 'listinghub' ); ?></span>
					</label>
					<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $cat ) : ?>
							<label class="listinghub-sb-check-item">
								<input type="checkbox" name="sf<?php echo esc_attr( $tax_category ); ?>[]" value="<?php echo esc_attr( $cat->slug ); ?>" class="listinghub-sb-renter-cb" <?php echo in_array( $cat->slug, $selected_cats, true ) ? ' checked' : ''; ?>>
								<span><?php echo esc_html( $cat->name ); ?></span>
							</label>
						<?php endforeach; ?>
					<?php else : ?>
						<label class="listinghub-sb-check-item"><input type="checkbox" name="sf<?php echo esc_attr( $tax_category ); ?>[]" value="private-lets"> <span><?php esc_html_e( 'Private lets', 'listinghub' ); ?></span></label>
						<label class="listinghub-sb-check-item"><input type="checkbox" name="sf<?php echo esc_attr( $tax_category ); ?>[]" value="student-lets"> <span><?php esc_html_e( 'Student lets', 'listinghub' ); ?></span></label>
					<?php endif; ?>
				</div>
			</div>

			<button type="submit" class="listinghub-sb-btn listinghub-sb-btn-search"><?php esc_html_e( 'Search', 'listinghub' ); ?></button>
			<button type="button" class="listinghub-sb-btn listinghub-sb-btn-mobile-filters">
			<span class="listinghub-sb-filters-icon" aria-hidden="true">
					<svg class="listinghub-sb-filters-icon-svg" width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<g stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
							<line x1="3" y1="4" x2="15" y2="4"/>
							<circle cx="8" cy="4" r="1.4" fill="currentColor" stroke="none"/>

							<line x1="3" y1="9" x2="15" y2="9"/>
							<circle cx="11" cy="9" r="1.4" fill="currentColor" stroke="none"/>

							<line x1="3" y1="14" x2="15" y2="14"/>
							<circle cx="6" cy="14" r="1.4" fill="currentColor" stroke="none"/>
						</g>
					</svg>
				</span>
			</button>
			<button type="button" class="listinghub-sb-btn listinghub-sb-btn-filters" id="listinghub_sb_filters_btn" aria-expanded="false" aria-controls="listinghub_sb_filters_panel">
			<span class="listinghub-sb-filters-label"><?php esc_html_e( 'Filters', 'listinghub' ); ?></span>	
			<span class="listinghub-sb-filters-icon" aria-hidden="true">
					<svg class="listinghub-sb-filters-icon-svg" width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<g stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
							<line x1="3" y1="4" x2="15" y2="4"/>
							<circle cx="8" cy="4" r="1.4" fill="currentColor" stroke="none"/>

							<line x1="3" y1="9" x2="15" y2="9"/>
							<circle cx="11" cy="9" r="1.4" fill="currentColor" stroke="none"/>

							<line x1="3" y1="14" x2="15" y2="14"/>
							<circle cx="6" cy="14" r="1.4" fill="currentColor" stroke="none"/>
						</g>
					</svg>
				</span>
			</button>
		</div>

		<!-- Filters panel (inside form so all inputs submit) -->
		<div class="listinghub-sb-filters-panel" id="listinghub_sb_filters_panel" hidden>
		<div class="listinghub-sb-filters-inner">
			<div class="listinghub-sb-filter-section listinghub-sb-property-section">
				<button type="button" class="listinghub-sb-property-trigger" id="listinghub_sb_property_trigger" aria-expanded="false" aria-controls="listinghub_sb_property_popup">
					<span class="listinghub-sb-property-label"><?php esc_html_e( 'Property type', 'listinghub' ); ?></span>
					<span class="listinghub-sb-arrow" aria-hidden="true"></span>
				</button>
				<div class="listinghub-sb-property-popup" id="listinghub_sb_property_popup" hidden>
					<input type="text" class="listinghub-sb-property-search" id="listinghub_sb_property_search" placeholder="<?php esc_attr_e( 'Search property types...', 'listinghub' ); ?>" autocomplete="off">
					<label class="listinghub-sb-check-item listinghub-sb-property-select-all">
						<input type="checkbox" id="listinghub_sb_property_select_all">
						<span><?php esc_html_e( 'Select all', 'listinghub' ); ?></span>
					</label>
					<div class="listinghub-sb-property-list" id="listinghub_sb_property_list">
						<?php if ( ! empty( $property_type_values ) ) : ?>
							<?php foreach ( $property_type_values as $val ) : ?>
								<label class="listinghub-sb-check-item">
									<input type="checkbox" name="sfproperty_type[]" value="<?php echo esc_attr( $val ); ?>" class="listinghub-sb-property-cb" <?php echo in_array( $val, $selected_property_types, true ) ? ' checked' : ''; ?>>
									<span><?php echo esc_html( $val ); ?></span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<label class="listinghub-sb-check-item"><input type="checkbox" name="sfproperty_type[]" value="Apartments" class="listinghub-sb-property-cb"> <span><?php esc_html_e( 'Apartments', 'listinghub' ); ?></span></label>
							<label class="listinghub-sb-check-item"><input type="checkbox" name="sfproperty_type[]" value="Studios" class="listinghub-sb-property-cb"> <span><?php esc_html_e( 'Studios', 'listinghub' ); ?></span></label>
							<label class="listinghub-sb-check-item"><input type="checkbox" name="sfproperty_type[]" value="Rooms" class="listinghub-sb-property-cb"> <span><?php esc_html_e( 'Rooms', 'listinghub' ); ?></span></label>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="listinghub-sb-filter-section listinghub-sb-bathrooms-section">
				<!-- <h4 class="listinghub-sb-filter-title"><?php //esc_html_e( 'Bathrooms', 'listinghub' ); ?></h4> -->
				<div class="listinghub-sb-field listinghub-sb-baths-wrap">
					<button type="button" class="listinghub-sb-beds-trigger" id="listinghub_sb_baths_trigger" aria-expanded="false" aria-controls="listinghub_sb_baths_panel">
						<span class="listinghub-sb-beds-label"><?php esc_html_e( 'Bathrooms', 'listinghub' ); ?></span>
						<span class="listinghub-sb-arrow" aria-hidden="true"></span>
					</button>
					<div class="listinghub-sb-beds-panel" id="listinghub_sb_baths_panel" hidden>
						<div class="listinghub-sb-beds-row">
							<label for="listinghub_sb_baths_min"><?php esc_html_e( 'Min bathrooms', 'listinghub' ); ?></label>
							<select name="sfbathrooms_min" id="listinghub_sb_baths_min" class="listinghub-sb-select">
								<option value=""><?php esc_html_e( 'Any', 'listinghub' ); ?></option>
								<?php
								$bathroom_options = array( 1, 2, 3, 4, 5 );
								foreach ( $bathroom_options as $b ) :
								?>
									<option value="<?php echo esc_attr( $b ); ?>" <?php selected( $bathrooms_min, $b ); ?>><?php echo esc_html( (string) $b ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="listinghub-sb-beds-row">
							<label for="listinghub_sb_baths_max"><?php esc_html_e( 'Max bathrooms', 'listinghub' ); ?></label>
							<select name="sfbathrooms_max" id="listinghub_sb_baths_max" class="listinghub-sb-select">
								<option value=""><?php esc_html_e( 'Any', 'listinghub' ); ?></option>
								<?php foreach ( $bathroom_options as $b ) : ?>
									<option value="<?php echo esc_attr( $b ); ?>" <?php selected( $bathrooms_max, $b ); ?>><?php echo esc_html( (string) $b ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="listinghub-sb-filter-section listinghub-sb-locations-section">
				<!-- <h4 class="listinghub-sb-filter-title"><?php //esc_html_e( 'Locations', 'listinghub' ); ?></h4> -->
				<button type="button" class="listinghub-sb-locations-trigger" id="listinghub_sb_locations_trigger" aria-expanded="false" aria-controls="listinghub_sb_locations_popup">
					<span class="listinghub-sb-locations-label"><?php esc_html_e( 'Select locations', 'listinghub' ); ?></span>
					<span class="listinghub-sb-arrow"></span>
				</button>
				<div class="listinghub-sb-locations-popup" id="listinghub_sb_locations_popup" hidden>
					<input type="text" class="listinghub-sb-locations-search" id="listinghub_sb_locations_search" placeholder="<?php esc_attr_e( 'Search locations...', 'listinghub' ); ?>" autocomplete="off">
					<label class="listinghub-sb-check-item listinghub-sb-select-all">
						<input type="checkbox" id="listinghub_sb_locations_select_all">
						<span><?php esc_html_e( 'Select all', 'listinghub' ); ?></span>
					</label>
					<div class="listinghub-sb-locations-list" id="listinghub_sb_locations_list">
						<?php if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) : ?>
							<?php foreach ( $locations as $loc ) : ?>
								<label class="listinghub-sb-check-item">
									<input type="checkbox" name="sf<?php echo esc_attr( $tax_locations ); ?>[]" value="<?php echo esc_attr( $loc->slug ); ?>" class="listinghub-sb-loc-cb" <?php echo in_array( $loc->slug, $selected_locs, true ) ? ' checked' : ''; ?>>
									<span><?php echo esc_html( $loc->name ); ?></span>
								</label>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="listinghub-sb-filters-actions">
				<button type="submit" class="listinghub-sb-btn listinghub-sb-btn-apply" id="listinghub_sb_apply_filters"><?php esc_html_e( 'Apply filters', 'listinghub' ); ?></button>
			</div>
		</div>
		</div>
	</form>
</div>
