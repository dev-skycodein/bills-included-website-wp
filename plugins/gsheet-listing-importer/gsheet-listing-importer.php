<?php
/**
 * Plugin Name: GSheet Listing Importer
 * Description: Imports listings from a Google Sheet (service account) and creates/updates ListingHub listings.
 * Version: 1.0.0
 * Author: Brainscop
 */

if (!defined('ABSPATH')) {
	exit;
}

const GSLI_SETTINGS_GROUP = 'gsli_settings';
const GSLI_IMAGE_ONLY_TEST = false;
// When true: only log counts and per-sheet row counts; do not create or update any posts. Set to false when testing is done.
const GSLI_DRY_RUN_IMPORT = false;
// Prevent overlapping image batch runs (seconds).
const GSLI_IMAGE_BATCH_LOCK_TTL = 600;
// Throttle between image downloads/uploads (microseconds).
const GSLI_IMAGE_THROTTLE_USLEEP = 250000;
// Default logo for all agencies and listings (company logo).
const GSLI_DEFAULT_AGENCY_LOGO = 'https://thebillsincluded.com/wp-content/uploads/2026/02/purple-house-clipart.jpg';
const GSLI_OPTION_SHEET_ID = 'gsli_sheet_id';
const GSLI_OPTION_SHEET_GID = 'gsli_sheet_gid';
const GSLI_OPTION_SHEET_GIDS = 'gsli_sheet_gids';
const GSLI_OPTION_SERVICE_ACCOUNT_JSON = 'gsli_service_account_json';
const GSLI_OPTION_CRON_TOKEN = 'gsli_cron_token';
/** Default filename for service account JSON when option is empty (relative to plugin dir). */
const GSLI_DEFAULT_SERVICE_ACCOUNT_JSON = 'bills-included-scraping-0fcadb7076ac.json';

add_action('admin_menu', 'gsli_register_admin_menu');
add_action('admin_init', 'gsli_register_settings');
add_action('admin_post_gsli_import', 'gsli_handle_import');
add_action('admin_post_gsli_stop_queue', 'gsli_handle_stop_queue');
add_action('admin_post_gsli_delete_listings', 'gsli_handle_delete_listings');
add_action('gsli_process_images_batch', 'gsli_process_images_batch');
add_action('gsli_run_import', 'gsli_cron_run_import');
add_action('init', 'gsli_maybe_run_cron_url', 1);

add_filter('cron_schedules', 'gsli_add_cron_schedules');
add_action('init', 'gsli_register_queue_post_type');
add_action('init', 'gsli_register_agency_post_type');
add_filter('query_vars', 'gsli_register_listing_agency_query_var');
add_filter('template_include', 'gsli_single_agency_template', 20);

function gsli_get_log_dir() {
	return plugin_dir_path(__FILE__) . 'logs' . DIRECTORY_SEPARATOR;
}

/**
 * Human-readable server date and datetime (for display and logs).
 *
 * @return array{date: string, datetime: string, timezone: string, utc: string}
 */
function gsli_server_datetime_strings() {
	$tz = wp_timezone_string();
	if ($tz === '') {
		$tz = date_default_timezone_get();
	}
	$now = time();
	return array(
		'date'      => wp_date('l, j F Y', $now),
		'datetime'  => wp_date('l, j F Y, H:i:s', $now),
		'timezone'  => $tz,
		'utc'       => gmdate('l, j F Y, H:i:s', $now) . ' UTC',
	);
}

function gsli_init_log() {
	$dir = gsli_get_log_dir();
	if (!is_dir($dir)) {
		wp_mkdir_p($dir);
	}
	$filename = 'import-' . gmdate('Ymd-His') . '-' . wp_generate_password(6, false, false) . '.log';
	$GLOBALS['gsli_log_file'] = $dir . $filename;
	gsli_log('Import started.');
	$dt = gsli_server_datetime_strings();
	gsli_log('Server datetime.', array('datetime' => $dt['datetime'], 'timezone' => $dt['timezone'], 'utc' => $dt['utc']));
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

function gsli_add_cron_schedules($schedules) {
	if (!isset($schedules['gsli_2min'])) {
		$schedules['gsli_2min'] = array(
			'interval' => 120,
			'display' => 'Every 2 minutes (GSLI)',
		);
	}
	return $schedules;
}

/**
 * Handle system-cron URLs: ?gsli_cron=import|images&token=SECRET
 * Does not rely on WordPress cron; call these URLs from system cron (e.g. crontab) on your schedule.
 */
function gsli_maybe_run_cron_url() {
	$action = isset($_GET['gsli_cron']) ? sanitize_text_field(wp_unslash($_GET['gsli_cron'])) : '';
	if ($action === '') {
		return;
	}

	$token = get_option(GSLI_OPTION_CRON_TOKEN, '');
	if ($token === '' || !isset($_GET['token']) || !hash_equals($token, sanitize_text_field(wp_unslash($_GET['token'])))) {
		status_header(403);
		wp_send_json(array('ok' => false, 'error' => 'Invalid or missing cron token.'), 403);
	}

	if ($action === 'import') {
		gsli_init_log();
		$result = gsli_do_import(false);
		if (is_wp_error($result)) {
			status_header(500);
			wp_send_json(array('ok' => false, 'error' => $result->get_error_message()), 500);
		}
		wp_send_json(array(
			'ok'      => true,
			'created' => $result['created'],
			'updated' => $result['updated'],
			'skipped' => $result['skipped'],
		));
	}

	if ($action === 'images') {
		gsli_init_log();
		do_action('gsli_process_images_batch');
		wp_send_json(array('ok' => true, 'message' => 'Image batch run completed.'));
	}

	status_header(400);
	wp_send_json(array('ok' => false, 'error' => 'Unknown action. Use import or images.'), 400);
}

function gsli_register_queue_post_type() {
	register_post_type('gsli_queue', array(
		'label' => 'GSLI Queue',
		'public' => false,
		'show_ui' => false,
		'show_in_menu' => false,
		'supports' => array('title'),
	));
}

function gsli_register_agency_post_type() {
	register_post_type('gsli_agency', array(
		'label' => 'Agencies',
		'labels' => array(
			'name'          => __('Agencies', 'gsheet-listing-importer'),
			'singular_name' => __('Agency', 'gsheet-listing-importer'),
		),
		'public' => true,
		'has_archive' => true,
		'rewrite' => array(
			'slug' => 'agencies',
		),
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 57,
		'supports' => array('title', 'editor', 'thumbnail'),
	));
}

/**
 * Register query var so ListingHub archive can filter by agency via URL (e.g. ?listing-agency=123).
 *
 * @param array $vars Existing query vars.
 * @return array
 */
function gsli_register_listing_agency_query_var( $vars ) {
	$vars[] = 'listing-agency';
	return $vars;
}

/**
 * Use custom template for single gsli_agency so agency page shows listings grid (same as all-listings).
 *
 * @param string $template Current template path.
 * @return string
 */
function gsli_single_agency_template( $template ) {
	if ( is_singular( 'gsli_agency' ) ) {
		$single = plugin_dir_path( __FILE__ ) . 'templates/single-gsli_agency.php';
		if ( file_exists( $single ) ) {
			return $single;
		}
	}
	return $template;
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
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_SHEET_GIDS, array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_SERVICE_ACCOUNT_JSON, array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_file_name',
		'default' => '',
	));
	register_setting(GSLI_SETTINGS_GROUP, GSLI_OPTION_CRON_TOKEN, array(
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
	$sheet_gids = get_option(GSLI_OPTION_SHEET_GIDS, '');
	$service_account_json = get_option(GSLI_OPTION_SERVICE_ACCOUNT_JSON, '');
	$cron_token = get_option(GSLI_OPTION_CRON_TOKEN, '');
	$json_path = gsli_get_service_account_json_path();

	$import_result = isset($_GET['gsli_import']) ? sanitize_text_field(wp_unslash($_GET['gsli_import'])) : '';
	$created = isset($_GET['created']) ? (int) $_GET['created'] : 0;
	$updated = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;
	$skipped = isset($_GET['skipped']) ? (int) $_GET['skipped'] : 0;
	$error = isset($_GET['error']) ? sanitize_text_field(wp_unslash($_GET['error'])) : '';
	$log_file = isset($_GET['log']) ? sanitize_text_field(wp_unslash($_GET['log'])) : '';
	$dry_run = isset($_GET['dry_run']) && $_GET['dry_run'] === '1';
	$delete_result = isset($_GET['gsli_deleted']) ? (int) $_GET['gsli_deleted'] : 0;

	$server_dt = gsli_server_datetime_strings();
	?>
	<div class="wrap">
		<h1>GSheet Listing Importer</h1>
		<p class="description" style="margin-bottom:16px;"><strong>Server time:</strong> <?php echo esc_html($server_dt['datetime']); ?> — Timezone: <?php echo esc_html($server_dt['timezone']); ?> | <strong>UTC:</strong> <?php echo esc_html($server_dt['utc']); ?></p>

		<?php if ($import_result === 'success') : ?>
			<div class="notice notice-success is-dismissible">
				<?php if ($dry_run) : ?>
					<p><?php echo esc_html("Dry run: would create $created, would update $updated, skipped $skipped. No posts were created or updated."); ?></p>
				<?php else : ?>
					<p><?php echo esc_html("Import completed. Created: $created, Updated: $updated, Skipped: $skipped."); ?></p>
				<?php endif; ?>
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

		<?php if ($delete_result > 0) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html(sprintf(_n('%d listing deleted.', '%d listings deleted.', $delete_result, 'gsheet-listing-importer'), $delete_result)); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields(GSLI_SETTINGS_GROUP); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gsli_sheet_id">Google Sheet ID</label></th>
					<td>
						<input type="text" id="gsli_sheet_id" name="<?php echo esc_attr(GSLI_OPTION_SHEET_ID); ?>" value="<?php echo esc_attr($sheet_id); ?>" class="regular-text" />
						<p class="description">From the URL: https://docs.google.com/spreadsheets/d/<strong>ID</strong>/edit</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_sheet_gids">Sheet GIDs (optional)</label></th>
					<td>
						<input type="text" id="gsli_sheet_gids" name="<?php echo esc_attr(GSLI_OPTION_SHEET_GIDS); ?>" value="<?php echo esc_attr($sheet_gids); ?>" class="large-text" placeholder="Leave empty for all tabs" />
						<p class="description">Leave empty to import from <strong>all tabs</strong> (first sheet, GID 0, is always skipped as intro/Welcome). Row 1 = header, rest = data. Or enter comma-separated GIDs to limit/reorder tabs.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_service_account_json">Service account JSON filename</label></th>
					<td>
						<input type="text" id="gsli_service_account_json" name="<?php echo esc_attr(GSLI_OPTION_SERVICE_ACCOUNT_JSON); ?>" value="<?php echo esc_attr($service_account_json); ?>" class="regular-text" placeholder="<?php echo esc_attr(GSLI_DEFAULT_SERVICE_ACCOUNT_JSON); ?>" />
						<p class="description">Filename of the service account JSON file in this plugin's directory (e.g. <?php echo esc_html(GSLI_DEFAULT_SERVICE_ACCOUNT_JSON); ?>). Share the Google Sheet with the service account email (Viewer). Leave empty to use default. No Composer or terminal required.</p>
						<?php if ($json_path !== '' && !is_readable($json_path)) : ?>
							<p class="description" style="color:#b32d2e;">File not found or not readable: <?php echo esc_html(basename($json_path)); ?></p>
						<?php elseif ($json_path !== '') : ?>
							<p class="description" style="color:#00a32a;">Using: <?php echo esc_html(basename($json_path)); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gsli_cron_token">System cron token</label></th>
					<td>
						<input type="text" id="gsli_cron_token" name="<?php echo esc_attr(GSLI_OPTION_CRON_TOKEN); ?>" value="<?php echo esc_attr($cron_token); ?>" class="regular-text" autocomplete="off" placeholder="e.g. my-secret-token" />
						<p class="description"><strong>Recommended.</strong> Set a secret token, then call the URLs below from <strong>system cron</strong> (crontab / Task Scheduler). Two processes: (1) <strong>Import</strong> – fetch sheet and create/update listings, queue images. (2) <strong>Images</strong> – process queued images in batches with intervals. Does not rely on WordPress cron or site visits.</p>
					</td>
				</tr>
				<?php if ($cron_token !== '') :
					$import_url = add_query_arg(array('gsli_cron' => 'import', 'token' => $cron_token), home_url('/'));
					$images_url = add_query_arg(array('gsli_cron' => 'images', 'token' => $cron_token), home_url('/'));
				?>
				<tr>
					<th scope="row">System cron URLs</th>
					<td>
						<p class="description" style="margin-bottom:6px;"><strong>1. Import</strong> (e.g. daily) – run first to sync listings and queue images:</p>
						<p><code style="word-break:break-all;"><?php echo esc_html($import_url); ?></code></p>
						<p class="description" style="margin-bottom:6px;"><strong>2. Images</strong> (e.g. every 2 min) – run repeatedly to drain the image queue:</p>
						<p><code style="word-break:break-all;"><?php echo esc_html($images_url); ?></code></p>
						<p class="description">Example crontab: <code>0 3 * * * curl -s "IMPORT_URL"</code> (daily at 3am) and <code>*/2 * * * * curl -s "IMAGES_URL"</code> (every 2 min).</p>
						<details style="margin-top:12px;">
							<summary style="cursor:pointer;"><strong>Hostinger setup</strong></summary>
							<ol style="margin:8px 0 0 18px; padding:0;">
								<li>In Hostinger: <strong>Websites → your site → Cron Jobs</strong> (or search “Cron” in the dashboard).</li>
								<li><strong>Job 1 – Import</strong>: Create a new cron job. Type: <strong>Custom</strong>. Command: <code>curl -s "<?php echo esc_html($import_url); ?>"</code>. Schedule: e.g. <strong>Once per day</strong> (or set time, e.g. 3:00 AM – Hostinger uses UTC). Save.</li>
								<li><strong>Job 2 – Images</strong>: Create another cron job. Type: <strong>Custom</strong>. Command: <code>curl -s "<?php echo esc_html($images_url); ?>"</code>. Schedule: <strong>Every 2 minutes</strong> (or “Every 5 minutes” if you prefer). Save.</li>
							</ol>
							<p class="description" style="margin-top:8px;">If <code>curl</code> is not available, use <code>wget -q -O - "URL"</code> with the same URLs. You can check <strong>View output</strong> in the cron list to confirm runs.</p>
						</details>
					</td>
				</tr>
				<?php endif; ?>
			</table>

			<?php submit_button('Save Settings'); ?>
		</form>

		<hr />

		<h2>Run Import</h2>
		<p>Data is fetched via Google Sheets API (service account). Dry run is <?php echo GSLI_DRY_RUN_IMPORT ? 'on' : 'off'; ?> (see constant GSLI_DRY_RUN_IMPORT).</p>
		<p>Required column: <code>title</code>. Optional: <code>content</code>, <code>excerpt</code>, <code>availability_status</code> (available / unavailable), <code>slug</code>, <code>listing_id</code>. All other columns are saved as post meta with matching keys.</p>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('gsli_import_action', 'gsli_import_nonce'); ?>
			<input type="hidden" name="action" value="gsli_import" />
			<?php submit_button('Import Listings', 'primary'); ?>
		</form>

		<h2>Stop Image Queue</h2>
		<p>Clears queued image imports and stops the cron schedule.</p>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('gsli_stop_queue_action', 'gsli_stop_queue_nonce'); ?>
			<input type="hidden" name="action" value="gsli_stop_queue" />
			<?php submit_button('Stop Image Queue', 'secondary'); ?>
		</form>

		<hr />

		<h2>Imported Listings</h2>
		<p>Listings added by this plugin (identified by <code>unique_id</code>). Delete removes the post, all meta, and associated media (featured + gallery images).</p>
		<?php
		$imported = gsli_get_imported_listings();
		if (!empty($imported)) :
			$post_type = gsli_get_listing_post_type();
			$edit_base = admin_url('post.php?post=%d&action=edit');
		?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="gsli-delete-listings-form">
			<?php wp_nonce_field('gsli_delete_listings_action', 'gsli_delete_listings_nonce'); ?>
			<input type="hidden" name="action" value="gsli_delete_listings" />
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td class="check-column">
							<input type="checkbox" id="gsli-select-all" />
						</td>
						<th scope="col">ID</th>
						<th scope="col">Title</th>
						<th scope="col">Unique ID</th>
						<th scope="col">Status</th>
						<th scope="col">Date</th>
						<th scope="col">Images</th>
						<th scope="col">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($imported as $post) :
						$unique_id = get_post_meta($post->ID, 'unique_id', true);
						$edit_url = sprintf($edit_base, $post->ID);
						$view_url = get_permalink($post->ID);
						$gallery_ids = get_post_meta($post->ID, 'image_gallery_ids', true);
						$image_count = 0;
						$ids = array();
						if (!empty($gallery_ids)) {
							$ids = array_filter(array_map('intval', explode(',', $gallery_ids)));
							$image_count = count($ids);
						}
						$thumb_id = get_post_thumbnail_id($post->ID);
						if ($thumb_id) {
							if (empty($ids) || !in_array((int) $thumb_id, $ids, true)) {
								$image_count++;
							}
						}
					?>
					<tr>
						<th scope="row" class="check-column">
							<input type="checkbox" name="gsli_delete_ids[]" value="<?php echo esc_attr($post->ID); ?>" class="gsli-row-checkbox" />
						</th>
						<td><?php echo esc_html($post->ID); ?></td>
						<td><strong><?php echo esc_html($post->post_title); ?></strong></td>
						<td><code><?php echo esc_html($unique_id); ?></code></td>
						<td><?php echo esc_html($post->post_status); ?></td>
						<td><?php echo esc_html(get_the_date('', $post)); ?></td>
						<td><?php echo esc_html($image_count); ?></td>
						<td>
							<a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'gsheet-listing-importer'); ?></a>
							| <a href="<?php echo esc_url($view_url); ?>" target="_blank"><?php esc_html_e('View', 'gsheet-listing-importer'); ?></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<button type="submit" class="button button-secondary" name="gsli_delete_confirm" value="1" onclick="return confirm('<?php echo esc_js(__('Permanently delete selected listings and all their images?', 'gsheet-listing-importer')); ?>');"><?php esc_html_e('Delete selected', 'gsheet-listing-importer'); ?></button>
			</p>
		</form>
		<script>
		document.getElementById('gsli-select-all').addEventListener('change', function() {
			document.querySelectorAll('.gsli-row-checkbox').forEach(function(cb) { cb.checked = this.checked; }, this);
		});
		</script>
		<?php else : ?>
		<p><?php esc_html_e('No listings imported by this plugin yet.', 'gsheet-listing-importer'); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Run the import (fetch sheet, validate, create/update listings).
 * Call gsli_init_log() before this so logs are written. Used by manual import and by cron.
 *
 * @param bool|null $dry_run true = dry run only; false = real import (e.g. cron); null = use GSLI_DRY_RUN_IMPORT constant.
 * @return array{created: int, updated: int, skipped: int, log_filename: string}|WP_Error
 */
function gsli_do_import($dry_run = null) {
	$import_start = microtime(true);
	$log_filename = isset($GLOBALS['gsli_log_file']) ? basename($GLOBALS['gsli_log_file']) : '';

	$source = gsli_fetch_source_rows();
	if (is_wp_error($source)) {
		gsli_log('Source fetch failed.', array('error' => $source->get_error_message()));
		return $source;
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
		return new WP_Error('gsli_no_title', $message);
	}

	$post_type = gsli_get_listing_post_type();
	$created = 0;
	$updated = 0;
	$skipped = 0;

	if ($dry_run === null) {
		$dry_run = GSLI_DRY_RUN_IMPORT;
	}
	if ($dry_run) {
		gsli_log('Dry run: no posts will be created or updated.', array());
		gsli_log('Dry run data: headers.', array('headers' => $headers));
		$sample_size = min(5, count($rows));
		for ($i = 0; $i < $sample_size; $i++) {
			$data = gsli_map_row($headers, $rows[$i]);
			$truncated = array();
			foreach ($data as $key => $val) {
				if ($key === '') {
					continue;
				}
				$s = wp_strip_all_tags((string) $val);
				$truncated[$key] = strlen($s) > 80 ? substr($s, 0, 80) . '…' : $s;
			}
			gsli_log('Dry run data: row ' . ($i + 1) . '.', array('data' => $truncated));
		}
		if (count($rows) > $sample_size) {
			gsli_log('Dry run data: ... and ' . (count($rows) - $sample_size) . ' more rows.', array());
		}
	}

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

		if (GSLI_IMAGE_ONLY_TEST) {
			$queue_post_id = gsli_get_queue_post_id();
			if (!empty($data['images'])) {
				gsli_queue_images($queue_post_id, $data['images']);
				gsli_log('Row queued (image-only test).', array('row' => $index + 1, 'queue_post_id' => $queue_post_id));
			} else {
				gsli_log('Row has no images to queue (image-only test).', array('row' => $index + 1));
			}
			$skipped++;
			continue;
		}

		// Availability status: available → publish; unavailable or missing → draft.
		$availability = strtolower(trim((string) (isset($data['availability_status']) ? $data['availability_status'] : '')));
		$is_available = ($availability === 'available');

		$post_status = $is_available ? 'publish' : 'draft';
		$defer_publish = false;
		if ($post_status === 'publish' && !empty($data['images'])) {
			$post_status = 'draft';
			$defer_publish = true;
		}

		$description = isset($data['description']) ? $data['description'] : '';
		$excerpt_raw  = isset($data['excerpt']) ? sanitize_textarea_field($data['excerpt']) : '';

		$postarr = array(
			'post_title'   => $title,
			'post_content' => gsli_format_rich_text($description),
			'post_excerpt' => $excerpt_raw,
			'post_status'  => $post_status,
			'post_type'    => $post_type,
		);
		if (!empty($data['slug'])) {
			$postarr['post_name'] = sanitize_title($data['slug']);
		}

		$existing_id = gsli_find_existing_post_id($post_type, $data, $postarr);
		$is_update = (bool) $existing_id;

		if ($dry_run) {
			if ($existing_id) {
				$updated++;
				gsli_log('Dry run: would update.', array('row' => $index + 1, 'post_id' => $existing_id, 'title' => $title));
			} else {
				$created++;
				gsli_log('Dry run: would create.', array('row' => $index + 1, 'title' => $title));
			}
			continue;
		}

		if ($existing_id) {
			// On update: set post_status from availability_status (available → publish; unavailable or missing → draft).
			$update_arr = array(
				'ID'          => $existing_id,
				'post_type'   => $post_type,
				'post_title'  => $title,
				'post_status' => $is_available ? 'publish' : 'draft',
			);
			if (trim((string) $description) !== '') {
				$update_arr['post_content'] = gsli_format_rich_text($description);
			}
			if (trim((string) $excerpt_raw) !== '') {
				$update_arr['post_excerpt'] = $excerpt_raw;
			}
			if (!empty($data['slug'])) {
				$update_arr['post_name'] = sanitize_title($data['slug']);
			}
			$result = wp_update_post($update_arr, true);
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
			gsli_apply_listinghub_mapping($existing_id, $data, $post_type, $is_update);
			if (!$is_update && $defer_publish) {
				update_post_meta($existing_id, '_gsli_target_status', 'publish');
				gsli_log('Listing set to draft pending images.', array('row' => $index + 1, 'post_id' => $existing_id));
			} elseif (!$is_update) {
				delete_post_meta($existing_id, '_gsli_target_status');
			}
		}
	}

	if ($dry_run) {
		gsli_log('Dry run finished.', array('would_create' => $created, 'would_update' => $updated, 'skipped' => $skipped));
	} else {
		gsli_log('Import finished.', array('created' => $created, 'updated' => $updated, 'skipped' => $skipped));
	}
	$import_duration = microtime(true) - $import_start;
	gsli_log('Import duration.', array('seconds' => round($import_duration, 2)));

	return array(
		'created'       => $created,
		'updated'       => $updated,
		'skipped'       => $skipped,
		'log_filename'  => $log_filename,
		'dry_run'       => $dry_run,
	);
}

function gsli_handle_import() {
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized', 403);
	}
	check_admin_referer('gsli_import_action', 'gsli_import_nonce');

	gsli_init_log();
	$result = gsli_do_import(null);

	if (is_wp_error($result)) {
		$log_filename = isset($GLOBALS['gsli_log_file']) ? basename($GLOBALS['gsli_log_file']) : '';
		gsli_redirect_with_error($result->get_error_message(), $log_filename);
	}

	wp_safe_redirect(add_query_arg(array(
		'page' => 'gsli-import',
		'gsli_import' => 'success',
		'created' => $result['created'],
		'updated' => $result['updated'],
		'skipped' => $result['skipped'],
		'log' => $result['log_filename'],
		'dry_run' => $result['dry_run'] ? '1' : '0',
	), admin_url('admin.php')));
	exit;
}

/**
 * Cron callback: run import (always real import, not dry run). Logs to plugin logs directory.
 */
function gsli_cron_run_import() {
	gsli_init_log();
	gsli_log('Auto import started (cron).', array());
	$result = gsli_do_import(false);
	if (is_wp_error($result)) {
		gsli_log('Auto import failed.', array('error' => $result->get_error_message()));
		return;
	}
	gsli_log('Auto import completed.', array('created' => $result['created'], 'updated' => $result['updated'], 'skipped' => $result['skipped']));
}

function gsli_handle_stop_queue() {
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized', 403);
	}

	check_admin_referer('gsli_stop_queue_action', 'gsli_stop_queue_nonce');
	gsli_init_log();
	gsli_log('Stop queue requested.');

	wp_clear_scheduled_hook('gsli_process_images_batch');
	delete_transient('gsli_image_batch_lock');

	$cleared = 0;
	$query = new WP_Query(array(
		'post_type' => array('gsli_queue', 'listing'),
		'posts_per_page' => 200,
		'post_status' => 'any',
		'meta_key' => '_pending_image_urls',
		'orderby' => 'ID',
		'order' => 'ASC',
	));

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();
			delete_post_meta($post_id, '_pending_image_urls');
			delete_post_meta($post_id, '_pending_image_index');
			$cleared++;
		}
		wp_reset_postdata();
	}

	gsli_log('Stop queue completed.', array('cleared_posts' => $cleared));

	wp_safe_redirect(add_query_arg(array(
		'page' => 'gsli-import',
		'gsli_import' => 'success',
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'log' => isset($GLOBALS['gsli_log_file']) ? basename($GLOBALS['gsli_log_file']) : '',
	), admin_url('admin.php')));
	exit;
}

function gsli_handle_delete_listings() {
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized', 403);
	}

	check_admin_referer('gsli_delete_listings_action', 'gsli_delete_listings_nonce');

	if (empty($_POST['gsli_delete_ids']) || !is_array($_POST['gsli_delete_ids'])) {
		wp_safe_redirect(add_query_arg('page', 'gsli-import', admin_url('admin.php')));
		exit;
	}

	$ids = array_map('intval', $_POST['gsli_delete_ids']);
	$ids = array_filter($ids);
	$deleted = 0;

	foreach ($ids as $post_id) {
		if (!$post_id) {
			continue;
		}
		$post = get_post($post_id);
		if (!$post || get_post_meta($post_id, 'unique_id', true) === '') {
			continue;
		}

		$thumb_id = get_post_thumbnail_id($post_id);
		if ($thumb_id) {
			wp_delete_attachment($thumb_id, true);
		}

		$gallery_ids = get_post_meta($post_id, 'image_gallery_ids', true);
		if (!empty($gallery_ids)) {
			$gallery_ids = array_filter(array_map('intval', explode(',', $gallery_ids)));
			foreach ($gallery_ids as $att_id) {
				if ($att_id) {
					wp_delete_attachment($att_id, true);
				}
			}
		}

		if (wp_delete_post($post_id, true)) {
			$deleted++;
		}
	}

	wp_safe_redirect(add_query_arg(array(
		'page' => 'gsli-import',
		'gsli_deleted' => $deleted,
	), admin_url('admin.php')));
	exit;
}

/**
 * Full path to the service account JSON file (plugin dir + option or default filename).
 *
 * @return string Full path, or '' if not set or not readable.
 */
function gsli_get_service_account_json_path() {
	$filename = get_option(GSLI_OPTION_SERVICE_ACCOUNT_JSON, '');
	$filename = trim((string) $filename);
	if ($filename === '') {
		$filename = GSLI_DEFAULT_SERVICE_ACCOUNT_JSON;
	}
	$filename = basename($filename);
	if ($filename === '' || preg_match('/[^a-zA-Z0-9._-]/', $filename)) {
		return '';
	}
	$path = plugin_dir_path(__FILE__) . $filename;
	return is_readable($path) ? $path : '';
}

function gsli_fetch_source_rows() {
	$sheet_id = get_option(GSLI_OPTION_SHEET_ID, '');
	$sheet_gids_raw = get_option(GSLI_OPTION_SHEET_GIDS, '');
	$sheet_gids_trimmed = trim((string) $sheet_gids_raw);
	$json_path = gsli_get_service_account_json_path();

	if ($sheet_id === '') {
		return new WP_Error('gsli_missing_source', 'Set Google Sheet ID.');
	}
	if ($json_path === '') {
		return new WP_Error('gsli_missing_json', 'Service account JSON file not found. Place the JSON file in the plugin directory and set its filename in settings.');
	}

	return gsli_fetch_sheet_rows_via_api($sheet_id, $sheet_gids_trimmed, $json_path);
}

/**
 * Get OAuth2 access token for Google service account (no Composer; plain PHP + wp_remote_*).
 *
 * @param string $credentials_path Full path to service account JSON file.
 * @return string|WP_Error Access token or error.
 */
function gsli_gsa_get_access_token($credentials_path) {
	$json = @file_get_contents($credentials_path);
	if ($json === false) {
		return new WP_Error('gsli_gsa_json', 'Could not read service account JSON.');
	}
	$creds = json_decode($json, true);
	if (empty($creds['client_email']) || empty($creds['private_key'])) {
		return new WP_Error('gsli_gsa_json', 'Invalid service account JSON (missing client_email or private_key).');
	}

	$now = time();
	$payload = array(
		'iss'   => $creds['client_email'],
		'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
		'aud'   => 'https://oauth2.googleapis.com/token',
		'iat'   => $now,
		'exp'   => $now + 3600,
	);
	$header = array('alg' => 'RS256', 'typ' => 'JWT');
	$segments = array(
		gsli_gsa_base64url_encode(wp_json_encode($header)),
		gsli_gsa_base64url_encode(wp_json_encode($payload)),
	);
	$signature_input = implode('.', $segments);

	$key = openssl_pkey_get_private($creds['private_key']);
	if ($key === false) {
		return new WP_Error('gsli_gsa_key', 'Invalid private key in service account JSON.');
	}
	$sig = '';
	openssl_sign($signature_input, $sig, $key, OPENSSL_ALGO_SHA256);
	openssl_free_key($key);

	$segments[] = gsli_gsa_base64url_encode($sig);
	$jwt = implode('.', $segments);

	$response = wp_remote_post('https://oauth2.googleapis.com/token', array(
		'timeout' => 15,
		'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
		'body'   => array(
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion'  => $jwt,
		),
	));

	if (is_wp_error($response)) {
		return new WP_Error('gsli_gsa_token', 'Token request failed: ' . $response->get_error_message());
	}
	$code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	if ((int) $code !== 200 || empty($data['access_token'])) {
		$msg = isset($data['error_description']) ? $data['error_description'] : 'Status ' . $code;
		return new WP_Error('gsli_gsa_token', 'Could not get access token: ' . $msg);
	}
	return $data['access_token'];
}

function gsli_gsa_base64url_encode($data) {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Fetch spreadsheet metadata (all tabs in order) via Sheets API.
 *
 * @param string $sheet_id Spreadsheet ID.
 * @param string $access_token OAuth2 access token.
 * @return array|WP_Error List of array('gid' => string, 'title' => string) in tab order.
 */
function gsli_gsa_sheets_get_metadata($sheet_id, $access_token) {
	$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($sheet_id) . '?fields=sheets(properties(sheetId,title))';
	$response = wp_remote_get($url, array(
		'timeout' => 15,
		'headers' => array('Authorization' => 'Bearer ' . $access_token),
	));
	if (is_wp_error($response)) {
		return new WP_Error('gsli_sheets_meta', 'Metadata request failed: ' . $response->get_error_message());
	}
	$code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	if ((int) $code !== 200) {
		$msg = isset($data['error']['message']) ? $data['error']['message'] : 'Status ' . $code;
		return new WP_Error('gsli_sheets_meta', $msg);
	}
	$ordered = array();
	if (!empty($data['sheets'])) {
		foreach ($data['sheets'] as $sheet) {
			if (isset($sheet['properties']['sheetId'], $sheet['properties']['title'])) {
				$ordered[] = array(
					'gid'   => (string) $sheet['properties']['sheetId'],
					'title' => $sheet['properties']['title'],
				);
			}
		}
	}
	return $ordered;
}

/**
 * Fetch sheet values for a range via Sheets API.
 *
 * @param string $sheet_id Spreadsheet ID.
 * @param string $range A1 notation, e.g. 'Sheet1'!A:ZZ.
 * @param string $access_token OAuth2 access token.
 * @return array|WP_Error Array of rows (each row is array of cell values).
 */
function gsli_gsa_sheets_get_values($sheet_id, $range, $access_token) {
	$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($sheet_id) . '/values/' . rawurlencode($range);
	$response = wp_remote_get($url, array(
		'timeout' => 15,
		'headers' => array('Authorization' => 'Bearer ' . $access_token),
	));
	if (is_wp_error($response)) {
		return new WP_Error('gsli_sheets_values', 'Values request failed: ' . $response->get_error_message());
	}
	$code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	if ((int) $code !== 200) {
		$msg = isset($data['error']['message']) ? $data['error']['message'] : 'Status ' . $code;
		return new WP_Error('gsli_sheets_values', $msg);
	}
	return isset($data['values']) ? $data['values'] : array();
}

/**
 * Fetch and merge rows from multiple sheet tabs via Google Sheets API (service account).
 * Uses plain PHP and wp_remote_* only — no Composer required.
 * When $sheet_gids_comma is empty, imports from all tabs in the spreadsheet (in order).
 *
 * @param string $sheet_id Google Spreadsheet ID.
 * @param string $sheet_gids_comma Optional comma-separated GIDs to limit/reorder tabs; empty = all tabs.
 * @param string $credentials_path Full path to service account JSON file.
 * @return array{headers: array, rows: array}|WP_Error
 */
function gsli_fetch_sheet_rows_via_api($sheet_id, $sheet_gids_comma, $credentials_path) {
	$token = gsli_gsa_get_access_token($credentials_path);
	if (is_wp_error($token)) {
		return $token;
	}

	$metadata = gsli_gsa_sheets_get_metadata($sheet_id, $token);
	if (is_wp_error($metadata)) {
		return $metadata;
	}

	$gid_to_title = array();
	foreach ($metadata as $item) {
		$gid_to_title[$item['gid']] = $item['title'];
	}

	$gids_requested = array_filter(array_map('trim', explode(',', $sheet_gids_comma)));
	if (empty($gids_requested)) {
		// No GIDs specified: use all tabs in spreadsheet order.
		$gids = array_column($metadata, 'gid');
	} else {
		$gids = $gids_requested;
	}

	// Always skip GID 0 (typically "Welcome" or intro sheet); row 1 = header, rest = data.
	$gids = array_values(array_filter($gids, function ($gid) {
		return $gid !== '0';
	}));

	if (empty($gids)) {
		return new WP_Error('gsli_sheets_empty', 'Spreadsheet has no sheets (or only GID 0 was present).');
	}

	$headers = null;
	$all_rows = array();

	foreach ($gids as $gid) {
		$title = isset($gid_to_title[$gid]) ? $gid_to_title[$gid] : null;
		if ($title === null) {
			gsli_log('Sheet GID skipped (no matching tab in spreadsheet).', array('gid' => $gid));
			continue;
		}

		$safe_title = str_replace("'", "''", $title);
		$range = preg_match('/[\s,]/', $safe_title) ? "'" . $safe_title . "'!A:ZZ" : $safe_title . '!A:ZZ';

		$values = gsli_gsa_sheets_get_values($sheet_id, $range, $token);
		if (is_wp_error($values)) {
			gsli_log('Sheets API error for GID.', array('gid' => $gid, 'error' => $values->get_error_message()));
			return new WP_Error('gsli_sheets_api', 'Sheets API error for GID ' . $gid . ': ' . $values->get_error_message());
		}

		if (empty($values)) {
			gsli_log('Sheet GID rows (empty).', array('gid' => $gid, 'title' => $title));
			continue;
		}

		$sheet_headers = gsli_normalize_headers(array_shift($values));
		$sheet_rows = $values;

		if ($headers === null) {
			$headers = $sheet_headers;
			$all_rows = $sheet_rows;
		} else {
			// Each sheet can have columns in a different order. Map by header name, not position,
			// then align to the first sheet's header order so data goes to the right meta keys.
			foreach ($sheet_rows as $row) {
				$keyed = gsli_map_row($sheet_headers, $row);
				$aligned = array();
				foreach ($headers as $h) {
					$aligned[] = isset($keyed[$h]) ? $keyed[$h] : '';
				}
				$all_rows[] = $aligned;
			}
		}

		gsli_log('Sheet GID rows.', array('gid' => $gid, 'title' => $title, 'rows' => count($sheet_rows)));
	}

	if ($headers === null || empty($headers)) {
		return new WP_Error('gsli_sheets_empty', 'No rows or headers found in any sheet.');
	}

	foreach ($all_rows as $i => $row) {
		$all_rows[$i] = array_pad(is_array($row) ? $row : array(), count($headers), '');
	}

	gsli_log('Total rows from all sheets.', array('total_rows' => count($all_rows)));

	return array(
		'headers' => $headers,
		'rows' => $all_rows,
	);
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

function gsli_get_imported_listings() {
	$post_type = gsli_get_listing_post_type();
	$query = new WP_Query(array(
		'post_type' => $post_type,
		'post_status' => 'any',
		'posts_per_page' => 500,
		'meta_key' => 'unique_id',
		'meta_compare' => 'EXISTS',
		'orderby' => 'date',
		'order' => 'DESC',
	));
	if (!$query->have_posts()) {
		return array();
	}
	return $query->posts;
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

function gsli_get_or_create_agency($data) {
	$agency_key = '';

	if (!empty($data['agency_id'])) {
		$agency_key = sanitize_key($data['agency_id']);
	} elseif (!empty($data['agency_website'])) {
		$host = wp_parse_url($data['agency_website'], PHP_URL_HOST);
		if (!empty($host)) {
			$host = preg_replace('/^www\./i', '', (string) $host);
			$agency_key = sanitize_key($host);
		}
	} elseif (!empty($data['agency_name'])) {
		$agency_key = sanitize_title($data['agency_name']);
	}

	if ($agency_key === '') {
		return 0;
	}

	$existing = get_posts(array(
		'post_type'   => 'gsli_agency',
		'post_status' => 'any',
		'meta_key'    => 'agency_id',
		'meta_value'  => $agency_key,
		'fields'      => 'ids',
		'numberposts' => 1,
	));

	if (!empty($existing)) {
		return (int) $existing[0];
	}

	$title = !empty($data['agency_name']) ? sanitize_text_field($data['agency_name']) : $agency_key;

	$postarr = array(
		'post_title'  => $title,
		'post_name'   => $agency_key,
		'post_type'   => 'gsli_agency',
		'post_status' => 'publish',
	);

	$agency_post_id = wp_insert_post($postarr, true);
	if (is_wp_error($agency_post_id)) {
		gsli_log('Agency create failed.', array('agency_id' => $agency_key, 'error' => $agency_post_id->get_error_message()));
		return 0;
	}

	$agency_post_id = (int) $agency_post_id;

	update_post_meta($agency_post_id, 'agency_id', $agency_key);

	if (!empty($data['agency_email'])) {
		update_post_meta($agency_post_id, 'agency_email', sanitize_text_field($data['agency_email']));
	}
	if (!empty($data['agency_phone'])) {
		update_post_meta($agency_post_id, 'agency_phone', sanitize_text_field($data['agency_phone']));
	}
	if (!empty($data['agency_website'])) {
		update_post_meta($agency_post_id, 'agency_website', esc_url_raw($data['agency_website']));
	}
	if (!empty($data['address'])) {
		update_post_meta($agency_post_id, 'agency_address', sanitize_text_field($data['address']));
	}
	if (!empty($data['city'])) {
		update_post_meta($agency_post_id, 'agency_city', sanitize_text_field($data['city']));
	}

	// For now, assign the default logo to all agency profiles (independent of sheet data).
	$agency_logo = esc_url_raw(GSLI_DEFAULT_AGENCY_LOGO);
	if ($agency_logo !== '') {
		update_post_meta($agency_post_id, 'agency_logo', $agency_logo);
	}

	// Placeholder for future claim flow – real owner will be set after "Claim my agency".
	update_post_meta($agency_post_id, 'agency_owner', 0);

	gsli_log('Agency created.', array('agency_post_id' => $agency_post_id, 'agency_id' => $agency_key));

	return $agency_post_id;
}

/**
 * Apply sheet data to listing post meta and terms.
 * Only updates when the sheet has a non-empty value (does not overwrite existing with blank).
 * On update ($is_update true), images are not queued so existing gallery is left unchanged.
 */
function gsli_apply_listinghub_mapping($post_id, $data, $post_type, $is_update = false) {
	if (!empty($data['unique_id'])) {
		$unique_id = sanitize_text_field($data['unique_id']);
		update_post_meta($post_id, 'unique_id', $unique_id);
	}

	// Force listing to use its own contact info fields
	update_post_meta($post_id, 'listing_contact_source', 'new_value');

	// Store the scraped agency identifier on the listing so it can be grouped later.
	if (!empty($data['agency_id'])) {
		$agency_key = sanitize_key($data['agency_id']);
		update_post_meta($post_id, 'agency_id', $agency_key);
	} else {
		$agency_key = '';
	}

	// Ensure an agency profile (CPT) exists and is linked to this scraped agency.
	$agency_post_id = gsli_get_or_create_agency($data);
	if ($agency_post_id) {
		update_post_meta($post_id, 'agency_post_id', $agency_post_id);
	}

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
		'url' => 'source_listing_url',
		'availability_status' => 'availability_status',
	);

	foreach ($meta_map as $source => $meta_key) {
		if (isset($data[$source]) && $data[$source] !== '') {
			update_post_meta($post_id, $meta_key, sanitize_text_field($data[$source]));
		}
	}

	// Agency logo: for now always use a single default logo for all listings.
	$logo_url = esc_url_raw(GSLI_DEFAULT_AGENCY_LOGO);
	if ($logo_url !== '') {
		update_post_meta($post_id, 'company_logo', $logo_url);
	}

	if (!empty($data['let_type'])) {
		// let_type can now be a list/array (e.g. both private and student).
		$let_values  = gsli_split_list($data['let_type']);
		$normalized  = array();
		foreach ($let_values as $value) {
			$let_type = strtolower(trim((string) $value));
			if ($let_type === 'student') {
				$let_type = 'student-let';
			} elseif ($let_type === 'private') {
				$let_type = 'private-let';
			}
			if ($let_type !== '') {
				$normalized[] = $let_type;
			}
		}
		if (!empty($normalized)) {
			gsli_assign_terms($post_id, $post_type . '-category', $normalized);
		}
	}

	if (!empty($data['features_included'])) {
		gsli_assign_terms($post_id, $post_type . '-tag', $data['features_included']);
	}

	// Property type: stored as post meta; value can be a list/array like features_included.
	if (!empty($data['property_type'])) {
		$types = gsli_split_list($data['property_type']);
		if (!empty($types)) {
			update_post_meta($post_id, 'property_type', $types);
		}
	}

	if (!empty($data['city'])) {
		gsli_assign_terms($post_id, $post_type . '-locations', $data['city']);
	}

	// Only queue images for new listings; on update leave existing images unchanged.
	if (!$is_update && !empty($data['images'])) {
		gsli_queue_images($post_id, $data['images']);
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
	$urls = is_array($raw_value) ? $raw_value : gsli_split_list($raw_value);
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

		// First, try to reuse an existing attachment for this URL.
		$existing = get_posts(array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'meta_key'    => '_gsli_source_url',
			'meta_value'  => $url,
			'fields'      => 'ids',
			'numberposts' => 1,
		));
		if (!empty($existing)) {
			$attachment_id = (int) $existing[0];
			$ids[] = $attachment_id;
			gsli_log('Image reused.', array('post_id' => $post_id, 'attachment_id' => $attachment_id, 'url' => $url));
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
		$attachment_id = (int) $attachment_id;
		$ids[] = $attachment_id;
		// Store the source URL on the attachment so future imports can reuse it.
		update_post_meta($attachment_id, '_gsli_source_url', $url);
		gsli_log('Image uploaded.', array('post_id' => $post_id, 'attachment_id' => $attachment_id, 'url' => $url));
		if (GSLI_IMAGE_THROTTLE_USLEEP > 0) {
			usleep(GSLI_IMAGE_THROTTLE_USLEEP);
		}
	}

	return $ids;
}

function gsli_queue_images($post_id, $raw_value) {
	$urls = gsli_split_list($raw_value);
	if (empty($urls)) {
		gsli_log('No images to queue.', array('post_id' => $post_id));
		return;
	}

	$existing = get_post_meta($post_id, '_pending_image_urls', true);
	if (!is_array($existing)) {
		$existing = array();
	}
	$merged = array_values(array_unique(array_merge($existing, $urls)));
	update_post_meta($post_id, '_pending_image_urls', $merged);
	if (!get_post_meta($post_id, '_pending_image_index', true)) {
		update_post_meta($post_id, '_pending_image_index', 0);
	}
	gsli_log('Queued images for background import.', array('post_id' => $post_id, 'count' => count($urls), 'total_pending' => count($merged)));

	gsli_schedule_image_cron();
}

function gsli_schedule_image_cron() {
	if (!wp_next_scheduled('gsli_process_images_batch')) {
		$start = time() + 120;
		wp_schedule_event($start, 'gsli_2min', 'gsli_process_images_batch');
		gsli_log('Scheduled image import cron.', array('start_in_seconds' => 120, 'interval_seconds' => 120));
	}
}

function gsli_process_images_batch() {
	if (empty($GLOBALS['gsli_log_file'])) {
		gsli_init_log();
	}
	$batch_start = microtime(true);

	// Allow batch to finish within lock window (avoids PHP max_execution_time killing mid-batch).
	if (function_exists('set_time_limit')) {
		@set_time_limit(GSLI_IMAGE_BATCH_LOCK_TTL);
	}

	if (get_transient('gsli_image_batch_lock')) {
		gsli_log('Image batch skipped (lock active).');
		return;
	}
	set_transient('gsli_image_batch_lock', 1, GSLI_IMAGE_BATCH_LOCK_TTL);

	$batch_limit = 25;
	$total_processed = 0;

	$query = new WP_Query(array(
		'post_type' => array('gsli_queue', 'listing'),
		'posts_per_page' => 10,
		'post_status' => 'any',
		'meta_key' => '_pending_image_urls',
		'orderby' => 'ID',
		'order' => 'ASC',
	));

	gsli_log('Image batch started.', array('batch_limit' => $batch_limit, 'posts_found' => $query->post_count));

	// If there is no work left, unschedule further batch processing to avoid idle runs.
	if ($query->post_count === 0) {
		wp_clear_scheduled_hook('gsli_process_images_batch');
		delete_transient('gsli_image_batch_lock');
		gsli_log('No pending images. Unscheduling image batch cron.', array());
		return;
	}

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();

			$pending_urls = get_post_meta($post_id, '_pending_image_urls', true);
			if (empty($pending_urls) || !is_array($pending_urls)) {
				delete_post_meta($post_id, '_pending_image_urls');
				delete_post_meta($post_id, '_pending_image_index');
				continue;
			}

			$index = (int) get_post_meta($post_id, '_pending_image_index', true);
			$remaining = array_slice($pending_urls, $index, $batch_limit);
			if (empty($remaining)) {
				delete_post_meta($post_id, '_pending_image_urls');
				delete_post_meta($post_id, '_pending_image_index');
				gsli_log('Image queue cleared (no remaining).', array('post_id' => $post_id));
				gsli_maybe_publish_after_images($post_id);
				continue;
			}

			$ids = gsli_import_images($post_id, $remaining);
			if (!empty($ids)) {
				$existing = get_post_meta($post_id, 'image_gallery_ids', true);
				$existing_ids = array();
				if (!empty($existing)) {
					$existing_ids = array_filter(array_map('intval', explode(',', $existing)));
				}
				$merged = array_values(array_unique(array_merge($existing_ids, $ids)));
				update_post_meta($post_id, 'image_gallery_ids', implode(',', $merged));
				if (!has_post_thumbnail($post_id)) {
					set_post_thumbnail($post_id, $ids[0]);
				}
			}

			$processed_count = count($remaining);
			$index += $processed_count;
			update_post_meta($post_id, '_pending_image_index', $index);
			wp_cache_delete($post_id, 'post_meta');
			$total_processed += $processed_count;

			gsli_log('Image batch processed.', array(
				'post_id' => $post_id,
				'processed' => count($remaining),
				'next_index' => $index,
				'remaining_total' => max(0, count($pending_urls) - $index),
			));

			if ($index >= count($pending_urls)) {
				delete_post_meta($post_id, '_pending_image_urls');
				delete_post_meta($post_id, '_pending_image_index');
				gsli_log('Image queue cleared (completed).', array('post_id' => $post_id));
				gsli_maybe_publish_after_images($post_id);
			}

			if ($total_processed >= $batch_limit) {
				break;
			}
		}
		wp_reset_postdata();
	}

	$batch_duration = microtime(true) - $batch_start;
	gsli_log('Image batch finished.', array(
		'processed_total' => $total_processed,
		'duration_seconds' => round($batch_duration, 2),
	));
	delete_transient('gsli_image_batch_lock');
}

function gsli_maybe_publish_after_images($post_id) {
	$target_status = get_post_meta($post_id, '_gsli_target_status', true);
	if ($target_status !== 'publish') {
		return;
	}

	if (get_post_status($post_id) !== 'draft') {
		delete_post_meta($post_id, '_gsli_target_status');
		return;
	}

	$result = wp_update_post(array(
		'ID' => $post_id,
		'post_status' => 'publish',
	), true);

	if (!is_wp_error($result)) {
		delete_post_meta($post_id, '_gsli_target_status');
		gsli_log('Listing published after image import.', array('post_id' => $post_id));
		return;
	}

	gsli_log('Listing publish failed after image import.', array(
		'post_id' => $post_id,
		'error' => $result->get_error_message(),
	));
}

function gsli_get_queue_post_id() {
	$existing = get_posts(array(
		'post_type' => 'gsli_queue',
		'post_status' => 'any',
		'meta_key' => '_gsli_queue',
		'meta_value' => '1',
		'fields' => 'ids',
		'numberposts' => 1,
	));
	if (!empty($existing)) {
		return (int) $existing[0];
	}

	$post_id = wp_insert_post(array(
		'post_type' => 'gsli_queue',
		'post_status' => 'private',
		'post_title' => 'GSLI Image Queue',
	), true);
	if (is_wp_error($post_id)) {
		gsli_log('Failed to create queue post.', array('error' => $post_id->get_error_message()));
		return 0;
	}
	update_post_meta($post_id, '_gsli_queue', '1');
	return (int) $post_id;
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
