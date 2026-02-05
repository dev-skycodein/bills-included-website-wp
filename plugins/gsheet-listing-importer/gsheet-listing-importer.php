<?php
/**
 * Plugin Name: GSheet Listing Importer
 * Description: Imports listings from a Google Sheet CSV and creates/updates ListingHub listings.
 * Version: 1.0.0
 * Author: Brainscop
 */

if (!defined('ABSPATH')) {
	exit;
}

const GSLI_SETTINGS_GROUP = 'gsli_settings';
const GSLI_OPTION_SHEET_URL = 'gsli_sheet_url';
const GSLI_OPTION_SHEET_ID = 'gsli_sheet_id';
const GSLI_OPTION_SHEET_GID = 'gsli_sheet_gid';
const GSLI_OPTION_CSV_URL = 'gsli_csv_url';
const GSLI_OPTION_ENDPOINT_URL = 'gsli_endpoint_url';
const GSLI_OPTION_ENDPOINT_TOKEN = 'gsli_endpoint_token';

add_action('admin_menu', 'gsli_register_admin_menu');
add_action('admin_init', 'gsli_register_settings');
add_action('admin_post_gsli_import', 'gsli_handle_import');

function gsli_get_log_dir() {
	return plugin_dir_path(__FILE__) . 'logs' . DIRECTORY_SEPARATOR;
}

function gsli_init_log() {
	$dir = gsli_get_log_dir();
	if (!is_dir($dir)) {
		wp_mkdir_p($dir);
	}
	$filename = 'import-' . gmdate('Ymd-His') . '-' . wp_generate_password(6, false, false) . '.log';
	$GLOBALS['gsli_log_file'] = $dir . $filename;
	gsli_log('Import started.');
	return $filename;
}

function gsli_log($message, $context = array()) {
	if (empty($GLOBALS['gsli_log_file'])) {
		return;
	}
	$timestamp = gmdate('Y-m-d H:i:s');
	$line = '[' . $timestamp . '] ' . $message;
	if (!empty($context)) {
		$line .= ' | ' . wp_json_encode($context);
	}
	$line .= PHP_EOL;
	@file_put_contents($GLOBALS['gsli_log_file'], $line, FILE_APPEND);
}

function gsli_register_admin_menu() {
	add_menu_page(
		'GSheet Listings',
		'GSheet Listings',
		'manage_options',
		'gsli-import',
		'gsli_render_admin_page',
		'dashicons-database-import',
		58
	);
}

function gsli_register_settings() {
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_SHEET_URL, array(
		'type' => 'string',
		'sanitize_callback' => 'esc_url_raw',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_SHEET_ID, array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_SHEET_GID, array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '0',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_CSV_URL, array(
		'type' => 'string',
		'sanitize_callback' => 'esc_url_raw',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_ENDPOINT_URL, array(
		'type' => 'string',
		'sanitize_callback' => 'esc_url_raw',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_ENDPOINT_TOKEN, array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	));
}

function gsli_render_admin_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$sheet_id = get_option(GSLI_OPTION_SHEET_ID, '');
	$sheet_gid = get_option(GSLI_OPTION_SHEET_GID, '0');
	$csv_url = get_option(GSLI_OPTION_CSV_URL, '');
	$sheet_url = get_option(GSLI_OPTION_SHEET_URL, '');
	$endpoint_url = get_option(GSLI_OPTION_ENDPOINT_URL, '');
	$endpoint_token = get_option(GSLI_OPTION_ENDPOINT_TOKEN, '');

	$import_result = isset($_GET['gsli_import']) ? sanitize_text_field(wp_unslash($_GET['gsli_import'])) : '';
	$created = isset($_GET['created']) ? (int) $_GET['created'] : 0;
	$updated = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;
	$skipped = isset($_GET['skipped']) ? (int) $_GET['skipped'] : 0;
	$error = isset($_GET['error']) ? sanitize_text_field(wp_unslash($_GET['error'])) : '';
	$log_file = isset($_GET['log']) ? sanitize_text_field(wp_unslash($_GET['log'])) : '';

	?>
	<div class="wrap">
		<h1>GSheet Listing Importer</h1>

		<?php if ($import_result === 'success') : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html("Import completed. Created: $created, Updated: $updated, Skipped: $skipped."); ?></p>
				<?php if ($log_file) : ?>
					<p><?php echo esc_html('Log file: ' . $log_file); ?></p>
				<?php endif; ?>
			</div>
		<?php elseif ($import_result === 'error' && $error) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_html($error); ?></p>
				<?php if ($log_file) : ?>
					<p><?php echo esc_html('Log file: ' . $log_file); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields(GSLI_SETTINGS_GROUP); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gsli_sheet_url">Google Sheet URL (optional)</label></th>
					<td>
						<input type="url" id="gsli_sheet_url" name="<?php echo esc_attr(GSLI_OPTION_SHEET_URL); ?>" value="<?php echo esc_attr($sheet_url); ?>" class="regular-text" />
						<p class="description">Paste the full Google Sheet URL. If set, it overrides Sheet ID/GID and CSV URL. Sheet must be shared (Anyone with the link can view).</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_sheet_id">Google Sheet ID</label></th>
					<td>
						<input type="text" id="gsli_sheet_id" name="<?php echo esc_attr(GSLI_OPTION_SHEET_ID); ?>" value="<?php echo esc_attr($sheet_id); ?>" class="regular-text" />
						<p class="description">From the URL: https://docs.google.com/spreadsheets/d/ID/</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_sheet_gid">Sheet GID (tab id)</label></th>
					<td>
						<input type="text" id="gsli_sheet_gid" name="<?php echo esc_attr(GSLI_OPTION_SHEET_GID); ?>" value="<?php echo esc_attr($sheet_gid); ?>" class="small-text" />
						<p class="description">Defaults to 0 for the first tab.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_csv_url">CSV URL (optional)</label></th>
					<td>
						<input type="url" id="gsli_csv_url" name="<?php echo esc_attr(GSLI_OPTION_CSV_URL); ?>" value="<?php echo esc_attr($csv_url); ?>" class="regular-text" />
						<p class="description">If set, this overrides the Sheet ID/GID unless you provide a secure endpoint below.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_endpoint_url">Secure Endpoint URL (optional)</label></th>
					<td>
						<input type="url" id="gsli_endpoint_url" name="<?php echo esc_attr(GSLI_OPTION_ENDPOINT_URL); ?>" value="<?php echo esc_attr($endpoint_url); ?>" class="regular-text" />
						<p class="description">Apps Script or custom API that returns JSON or CSV. If set, it overrides all other sources.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_endpoint_token">Endpoint Token (optional)</label></th>
					<td>
						<input type="text" id="gsli_endpoint_token" name="<?php echo esc_attr(GSLI_OPTION_ENDPOINT_TOKEN); ?>" value="<?php echo esc_attr($endpoint_token); ?>" class="regular-text" />
						<p class="description">Sent as query param <code>token</code> and header <code>X-GSLI-Token</code>.</p>
					</td>
				</tr>
			</table>

			<?php submit_button('Save Settings'); ?>
		</form>

		<hr />

		<h2>Run Import</h2>
		<p>Use a secure endpoint or a public CSV. First row is treated as headers for CSV; JSON should be an array of objects.</p>
		<p>Required column: <code>title</code>. Optional: <code>content</code>, <code>excerpt</code>, <code>status</code>, <code>slug</code>, <code>listing_id</code>. All other columns are saved as post meta with matching keys.</p>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('gsli_import_action', 'gsli_import_nonce'); ?>
			<input type="hidden" name="action" value="gsli_import" />
			<?php submit_button('Import Listings', 'primary'); ?>
		</form>
	</div>
	<?php
}

function gsli_handle_import() {
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized', 403);
	}

	check_admin_referer('gsli_import_action', 'gsli_import_nonce');
	$log_filename = gsli_init_log();

	$source = gsli_fetch_source_rows();
	if (is_wp_error($source)) {
		gsli_log('Source fetch failed.', array('error' => $source->get_error_message()));
		gsli_redirect_with_error($source->get_error_message());
	}

	$rows = $source['rows'];
	$headers = $source['headers'];
	if (empty($headers) || !in_array('title', $headers, true)) {
		$headers_display = array();
		foreach ($headers as $header) {
			if ($header !== '') {
				$headers_display[] = $header;
			}
		}
		$preview = implode(', ', $headers_display);
		$message = 'Source must include a "title" column.';
		if ($preview !== '') {
			$message .= ' Headers found: ' . $preview;
		}
		if (!empty($rows)) {
			$sample = gsli_map_row($headers, $rows[0]);
			$sample_pairs = array();
			foreach ($sample as $key => $value) {
				if ($key === '') {
					continue;
				}
				$clean = wp_strip_all_tags((string) $value);
				$clean = preg_replace('/\s+/', ' ', $clean);
				$sample_pairs[] = $key . '=' . substr($clean, 0, 50);
				if (count($sample_pairs) >= 10) {
					break;
				}
			}
			if (!empty($sample_pairs)) {
				$message .= ' Sample row: ' . implode(', ', $sample_pairs) . ' ...';
			}
		}
		gsli_log('Missing title column.', array('headers' => $headers));
		gsli_redirect_with_error($message, $log_filename);
	}

	$post_type = gsli_get_listing_post_type();

	$created = 0;
	$updated = 0;
	$skipped = 0;

	foreach ($rows as $index => $row) {
		$data = gsli_map_row($headers, $row);
		if (gsli_row_is_empty($data)) {
			$skipped++;
			gsli_log('Row skipped (empty).', array('row' => $index + 1));
			continue;
		}

		$title = isset($data['title']) ? sanitize_text_field($data['title']) : '';
		if ($title === '') {
			$skipped++;
			gsli_log('Row skipped (missing title).', array('row' => $index + 1));
			continue;
		}

		$post_status = isset($data['status']) ? sanitize_key($data['status']) : 'publish';
		if (!in_array($post_status, array('publish', 'draft', 'pending', 'private'), true)) {
			$post_status = 'publish';
		}

		$description = isset($data['description']) ? $data['description'] : '';

		$postarr = array(
			'post_title'   => $title,
			'post_content' => gsli_format_rich_text($description),
			'post_excerpt' => isset($data['excerpt']) ? sanitize_textarea_field($data['excerpt']) : '',
			'post_status'  => $post_status,
			'post_type'    => $post_type,
		);

		if (!empty($data['slug'])) {
			$postarr['post_name'] = sanitize_title($data['slug']);
		}

		$existing_id = gsli_find_existing_post_id($post_type, $data, $postarr);
		if ($existing_id) {
			$postarr['ID'] = $existing_id;
			$result = wp_update_post($postarr, true);
			if (!is_wp_error($result)) {
				$updated++;
				gsli_log('Row updated.', array('row' => $index + 1, 'post_id' => $existing_id));
			} else {
				gsli_log('Row update failed.', array('row' => $index + 1, 'error' => $result->get_error_message()));
			}
		} else {
			$result = wp_insert_post($postarr, true);
			if (!is_wp_error($result)) {
				$existing_id = (int) $result;
				$created++;
				gsli_log('Row created.', array('row' => $index + 1, 'post_id' => $existing_id));
			} else {
				gsli_log('Row create failed.', array('row' => $index + 1, 'error' => $result->get_error_message()));
			}
		}

		if ($existing_id && !is_wp_error($result)) {
			gsli_apply_listinghub_mapping($existing_id, $data, $post_type);
		}
	}

	gsli_log('Import finished.', array('created' => $created, 'updated' => $updated, 'skipped' => $skipped));
	wp_safe_redirect(add_query_arg(array(
		'page' => 'gsli-import',
		'gsli_import' => 'success',
		'created' => $created,
		'updated' => $updated,
		'skipped' => $skipped,
		'log' => $log_filename,
	), admin_url('admin.php')));
	exit;
}

function gsli_get_csv_url() {
	$sheet_url = get_option(GSLI_OPTION_SHEET_URL, '');
	if (!empty($sheet_url)) {
		$export_url = gsli_build_export_csv_url($sheet_url);
		if ($export_url !== '') {
			return $export_url;
		}
	}

	$csv_url = get_option(GSLI_OPTION_CSV_URL, '');
	if (!empty($csv_url)) {
		return $csv_url;
	}

	$sheet_id = get_option(GSLI_OPTION_SHEET_ID, '');
	if (empty($sheet_id)) {
		return '';
	}

	$gid = get_option(GSLI_OPTION_SHEET_GID, '0');
	$gid = $gid !== '' ? $gid : '0';

	return sprintf(
		'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
		rawurlencode($sheet_id),
		rawurlencode($gid)
	);
}

function gsli_build_export_csv_url($sheet_url) {
	if ($sheet_url === '') {
		return '';
	}

	$id = '';
	if (preg_match('#/d/([a-zA-Z0-9-_]+)#', $sheet_url, $matches)) {
		$id = $matches[1];
	}
	if ($id === '') {
		return '';
	}

	$gid = '0';
	$parts = wp_parse_url($sheet_url);
	if (!empty($parts['query'])) {
		parse_str($parts['query'], $query);
		if (!empty($query['gid'])) {
			$gid = (string) $query['gid'];
		}
	}

	return sprintf(
		'https://docs.google.com/spreadsheets/d/%s/export?format=csv&id=%s&gid=%s',
		rawurlencode($id),
		rawurlencode($id),
		rawurlencode($gid)
	);
}

function gsli_fetch_source_rows() {
	$endpoint_url = get_option(GSLI_OPTION_ENDPOINT_URL, '');
	if (!empty($endpoint_url)) {
		return gsli_fetch_from_endpoint($endpoint_url);
	}

	$csv_url = gsli_get_csv_url();
	if (empty($csv_url)) {
		return new WP_Error('gsli_missing_source', 'Missing secure endpoint, Sheet URL, Sheet ID, or CSV URL.');
	}

	return gsli_fetch_csv_rows($csv_url);
}

function gsli_fetch_from_endpoint($endpoint_url) {
	$token = get_option(GSLI_OPTION_ENDPOINT_TOKEN, '');
	$request_url = gsli_append_token_to_url($endpoint_url, $token);
	$args = array('timeout' => 20);

	if ($token !== '') {
		$args['headers'] = array(
			'X-GSLI-Token' => $token,
		);
	}

	$response = wp_remote_get($request_url, $args);
	if (is_wp_error($response)) {
		return new WP_Error('gsli_endpoint_error', 'Failed to fetch endpoint: ' . $response->get_error_message());
	}

	$code = wp_remote_retrieve_response_code($response);
	if ((int) $code !== 200) {
		return new WP_Error('gsli_endpoint_status', 'Endpoint request failed with status: ' . $code);
	}

	$body = wp_remote_retrieve_body($response);
	if (trim($body) === '') {
		return new WP_Error('gsli_endpoint_empty', 'Endpoint response was empty.');
	}

	$content_type = wp_remote_retrieve_header($response, 'content-type');
	$is_json = stripos((string) $content_type, 'application/json') !== false;
	$trimmed = ltrim($body);
	if ($is_json || $trimmed[0] === '{' || $trimmed[0] === '[') {
		$decoded = json_decode($body, true);
		if (json_last_error() === JSON_ERROR_NONE) {
			$objects = isset($decoded['data']) ? $decoded['data'] : $decoded;
			if (is_array($objects)) {
				return gsli_rows_from_objects($objects);
			}
		}
	}

	return gsli_fetch_csv_rows_from_body($body);
}

function gsli_fetch_csv_rows($csv_url) {
	$response = wp_remote_get($csv_url, array('timeout' => 20));
	if (is_wp_error($response)) {
		return new WP_Error('gsli_csv_error', 'Failed to fetch CSV: ' . $response->get_error_message());
	}

	$code = wp_remote_retrieve_response_code($response);
	if ((int) $code !== 200) {
		return new WP_Error('gsli_csv_status', 'CSV request failed with status: ' . $code);
	}

	$headers = wp_remote_retrieve_headers($response);
	if (is_array($headers)) {
		$headers = array_change_key_case($headers, CASE_LOWER);
		if (!empty($headers['x-frame-options']) && strtolower($headers['x-frame-options']) === 'deny') {
			return new WP_Error('gsli_csv_private', 'Sheet is not public or shared.');
		}
	}

	$body = wp_remote_retrieve_body($response);
	if (trim($body) === '') {
		return new WP_Error('gsli_csv_empty', 'CSV response was empty.');
	}

	return gsli_fetch_csv_rows_from_body($body);
}

function gsli_fetch_csv_rows_from_body($body) {
	$rows = gsli_parse_csv($body);
	if (empty($rows)) {
		return new WP_Error('gsli_csv_rows', 'No rows found in CSV.');
	}

	$headers = array_shift($rows);
	$headers = gsli_normalize_headers($headers);

	return array(
		'headers' => $headers,
		'rows' => $rows,
	);
}

function gsli_rows_from_objects($objects) {
	if (empty($objects)) {
		return new WP_Error('gsli_json_rows', 'No rows found in JSON.');
	}

	$headers = array();
	foreach ($objects as $object) {
		if (!is_array($object)) {
			continue;
		}
		foreach ($object as $key => $value) {
			if (!in_array($key, $headers, true)) {
				$headers[] = $key;
			}
		}
	}

	if (empty($headers)) {
		return new WP_Error('gsli_json_headers', 'JSON response did not contain any fields.');
	}

	$normalized = gsli_normalize_headers($headers);
	$rows = array();

	foreach ($objects as $object) {
		if (!is_array($object)) {
			continue;
		}
		$row = array();
		foreach ($headers as $header) {
			$row[] = isset($object[$header]) ? $object[$header] : '';
		}
		$rows[] = $row;
	}

	return array(
		'headers' => $normalized,
		'rows' => $rows,
	);
}

function gsli_append_token_to_url($url, $token) {
	if ($token === '') {
		return $url;
	}

	$separator = strpos($url, '?') === false ? '?' : '&';
	return $url . $separator . 'token=' . rawurlencode($token);
}

function gsli_parse_csv($csv_body) {
	$handle = fopen('php://temp', 'r+');
	fwrite($handle, $csv_body);
	rewind($handle);

	$rows = array();
	while (($row = fgetcsv($handle)) !== false) {
		$rows[] = $row;
	}
	fclose($handle);

	return $rows;
}

function gsli_normalize_headers($headers) {
	$normalized = array();
	foreach ($headers as $header) {
		$key = strtolower(trim((string) $header));
		$key = preg_replace('/\s+/', '_', $key);
		$normalized[] = sanitize_key($key);
	}
	return $normalized;
}

function gsli_map_row($headers, $row) {
	$data = array();
	foreach ($headers as $index => $key) {
		$data[$key] = isset($row[$index]) ? $row[$index] : '';
	}
	return $data;
}

function gsli_row_is_empty($data) {
	foreach ($data as $value) {
		if (trim((string) $value) !== '') {
			return false;
		}
	}
	return true;
}

function gsli_get_listing_post_type() {
	$listinghub_directory_url = get_option('ep_listinghub_url');
	if ($listinghub_directory_url === '') {
		$listinghub_directory_url = 'listing';
	}
	return $listinghub_directory_url;
}

function gsli_find_existing_post_id($post_type, $data, $postarr) {
	$lookup_id = '';
	if (!empty($data['unique_id'])) {
		$lookup_id = sanitize_text_field($data['unique_id']);
	} elseif (!empty($data['listing_id'])) {
		$lookup_id = sanitize_text_field($data['listing_id']);
	}

	if ($lookup_id !== '') {
		$existing = get_posts(array(
			'post_type' => $post_type,
			'meta_key' => 'unique_id',
			'meta_value' => $lookup_id,
			'fields' => 'ids',
			'numberposts' => 1,
		));
		if (!empty($existing)) {
			return (int) $existing[0];
		}
	}

	if (!empty($postarr['post_name'])) {
		$existing = get_page_by_path($postarr['post_name'], OBJECT, $post_type);
		if ($existing) {
			return (int) $existing->ID;
		}
	}

	return 0;
}

function gsli_apply_listinghub_mapping($post_id, $data, $post_type) {
	if (!empty($data['unique_id'])) {
		$unique_id = sanitize_text_field($data['unique_id']);
		update_post_meta($post_id, 'unique_id', $unique_id);
	}

	// Force listing to use its own contact info fields
	update_post_meta($post_id, 'listing_contact_source', 'new_value');

	if (!empty($data['price'])) {
		update_post_meta($post_id, 'monthly_rent', sanitize_text_field($data['price']));
	}
	if (!empty($data['price_value'])) {
		update_post_meta($post_id, 'search_price', sanitize_text_field($data['price_value']));
	}

	$meta_map = array(
		'bedrooms' => 'bedrooms',
		'bathrooms' => 'bathrooms',
		'address' => 'address',
		'city' => 'city',
		'google_maps_link' => 'google_maps_link',
		'latitude' => 'latitude',
		'longitude' => 'longitude',
		'agency_name' => 'company_name',
		'agency_phone' => 'phone',
		'agency_email' => 'contact-email',
		'agency_website' => 'contact_web',
	);

	foreach ($meta_map as $source => $meta_key) {
		if (isset($data[$source]) && $data[$source] !== '') {
			update_post_meta($post_id, $meta_key, sanitize_text_field($data[$source]));
		}
	}

	if (!empty($data['let_type'])) {
		$let_type = strtolower(trim((string) $data['let_type']));
		if ($let_type === 'student') {
			$let_type = 'student-let';
		} elseif ($let_type === 'private') {
			$let_type = 'private-let';
		}
		gsli_assign_terms($post_id, $post_type . '-category', $let_type);
	}

	if (!empty($data['features_included'])) {
		gsli_assign_terms($post_id, $post_type . '-tag', $data['features_included']);
	}

	if (!empty($data['city'])) {
		gsli_assign_terms($post_id, $post_type . '-locations', $data['city']);
	}

	if (!empty($data['images'])) {
		$image_ids = gsli_import_images($post_id, $data['images']);
		if (!empty($image_ids)) {
			update_post_meta($post_id, 'image_gallery_ids', implode(',', $image_ids));
			if (!has_post_thumbnail($post_id)) {
				set_post_thumbnail($post_id, $image_ids[0]);
			}
		}
	}
}

function gsli_assign_terms($post_id, $taxonomy, $raw_value) {
	$values = gsli_split_list($raw_value);
	if (empty($values)) {
		return;
	}

	$term_ids = array();
	foreach ($values as $value) {
		$slug = sanitize_title($value);
		$term = get_term_by('slug', $slug, $taxonomy);
		if (!$term) {
			$term = get_term_by('name', $value, $taxonomy);
		}
		if (!$term) {
			$created = wp_insert_term($value, $taxonomy);
			if (!is_wp_error($created) && !empty($created['term_id'])) {
				$term_ids[] = (int) $created['term_id'];
			}
		} else {
			$term_ids[] = (int) $term->term_id;
		}
	}

	if (!empty($term_ids)) {
		wp_set_object_terms($post_id, $term_ids, $taxonomy);
	}
}

function gsli_split_list($raw_value) {
	if (is_array($raw_value)) {
		return array_filter(array_map('trim', $raw_value));
	}
	$clean = trim((string) $raw_value);
	if ($clean === '') {
		return array();
	}
	if (($clean[0] === '[' && substr($clean, -1) === ']') || ($clean[0] === '{' && substr($clean, -1) === '}')) {
		$decoded = json_decode($clean, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
			return array_filter(array_map('trim', $decoded));
		}
	}
	if ($clean[0] === '[' && substr($clean, -1) === ']') {
		$clean = trim($clean, "[]");
		$parts = explode(',', $clean);
		$normalized = array();
		foreach ($parts as $part) {
			$part = trim($part);
			$part = trim($part, "\"'");
			if ($part !== '') {
				$normalized[] = $part;
			}
		}
		return $normalized;
	}
	$parts = preg_split('/[,\|;]/', $clean);
	return array_filter(array_map('trim', $parts));
}

function gsli_import_images($post_id, $raw_value) {
	$urls = gsli_split_list($raw_value);
	if (empty($urls)) {
		gsli_log('No image URLs found for row.', array('post_id' => $post_id));
		return array();
	}

	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	$ids = array();
	foreach ($urls as $url) {
		$url = esc_url_raw($url);
		if ($url === '') {
			continue;
		}
		$tmp = download_url($url);
		if (is_wp_error($tmp)) {
			gsli_log('Image download failed.', array('post_id' => $post_id, 'url' => $url, 'error' => $tmp->get_error_message()));
			continue;
		}
		$file_array = array(
			'name' => basename(parse_url($url, PHP_URL_PATH)),
			'tmp_name' => $tmp,
		);
		$attachment_id = media_handle_sideload($file_array, $post_id);
		if (is_wp_error($attachment_id)) {
			gsli_log('Image upload failed.', array('post_id' => $post_id, 'url' => $url, 'error' => $attachment_id->get_error_message()));
			@unlink($file_array['tmp_name']);
			continue;
		}
		$ids[] = (int) $attachment_id;
		gsli_log('Image uploaded.', array('post_id' => $post_id, 'attachment_id' => (int) $attachment_id, 'url' => $url));
		usleep(750000);
	}

	return $ids;
}

function gsli_format_rich_text($description) {
	$description = trim((string) $description);
	if ($description === '') {
		return '';
	}
	if (strpos($description, '<!-- wp:') !== false) {
		return wp_kses_post($description);
	}
	$wrapped = '<!-- wp:paragraph -->' . "\n" .
		'<p>' . esc_html($description) . '</p>' . "\n" .
		'<!-- /wp:paragraph -->';
	return $wrapped;
}

function gsli_redirect_with_error($message, $log_filename = '') {
	wp_safe_redirect(add_query_arg(array(
		'page' => 'gsli-import',
		'gsli_import' => 'error',
		'error' => rawurlencode($message),
		'log' => $log_filename ? $log_filename : (isset($GLOBALS['gsli_log_file']) ? basename($GLOBALS['gsli_log_file']) : ''),
	), admin_url('admin.php')));
	exit;
}
