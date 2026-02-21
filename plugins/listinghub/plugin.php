<?php
	/**
		*
		*
		* @version 1.2.6
		* @package Main
		* @author e-plugins
	*/
	/*
		Plugin Name: Listing Hub
		Plugin URI: http://e-plugins.com/
		Description: Build Paid Directory listing using Wordpress.No programming knowledge required.
		Author: e-plugins
		Author URI: http://e-plugins.com/
		Version: 1.2.6
		Text Domain: listinghub
		License: GPLv3
	*/
	// Exit if accessed directly
	if (!defined('ABSPATH')) {
		exit;
	}
	if (!class_exists('eplugins_listinghub')) {  	
		final class eplugins_listinghub {
			private static $instance;
			/**
				* The Plug-in version.
				*
				* @var string
			*/
			public $version = "1.2.6";
			/**
				* The minimal required version of WordPress for this plug-in to function correctly.
				*
				* @var string
			*/
			public $wp_version = "3.5";
			public static function instance() {
				if (!isset(self::$instance) && !(self::$instance instanceof eplugins_listinghub)) {
					self::$instance = new eplugins_listinghub;
				}
				return self::$instance;
			}
			/**
				* Construct and start the other plug-in functionality
			*/
			
			public function __construct() {
				//
				// 1. Plug-in requirements
				//
				if (!$this->check_requirements()) {
					return;
				}
				//
				// 2. Declare constants and load dependencies
				//
				$this->define_constants();
				$this->load_dependencies();
				//
				// 3. Activation Hooks
				//
				register_activation_hook(__FILE__, array($this, 'activate'));
				register_deactivation_hook(__FILE__, array($this, 'deactivate'));
				register_uninstall_hook(__FILE__, 'eplugins_listinghub::uninstall');
				//
				// 4. Load Widget
				//
				add_action('widgets_init', array($this, 'register_widget'));
				//
				// 5. i18n
				//
				add_action('init', array($this, 'i18n'));
				//
				// 6. Actions
				//	
				
				add_action('template_redirect', array($this, 'listinghub_track_listing_view'), 5);
				add_action('template_redirect', function () {
					if (
						isset($_GET['lh_action']) &&
						$_GET['lh_action'] === 'verify_email_change' &&
						isset($_GET['user_id'], $_GET['token'])
					) {
						$user_id = absint($_GET['user_id']);
						$token = sanitize_text_field($_GET['token']);
				
						$saved_token = get_user_meta($user_id, '_email_change_token', true);
						$new_email   = get_user_meta($user_id, '_pending_new_email', true);
				
						if ($token === $saved_token && is_email($new_email)) {
							wp_update_user([
								'ID' => $user_id,
								'user_email' => $new_email,
							]);
				
							delete_user_meta($user_id, '_email_change_token');
							delete_user_meta($user_id, '_pending_new_email');
				
							wp_redirect(home_url('/login/?email=updated'));
							exit;
						} else {
							wp_redirect(home_url('/login/?email=invalid'));
							exit;
						}
					}
				});
				add_action('wp_ajax_cancel_email_verification', [$this, 'handle_cancel_email_verification']);
				add_action('wp_ajax_listinghub_check_coupon', array($this, 'listinghub_check_coupon'));
				add_action('wp_ajax_nopriv_listinghub_check_coupon', array($this, 'listinghub_check_coupon'));					
				add_action('wp_ajax_listinghub_check_package_amount', array($this, 'listinghub_check_package_amount'));
				add_action('wp_ajax_nopriv_listinghub_check_package_amount', array($this, 'listinghub_check_package_amount'));
				add_action('wp_ajax_listinghub_update_profile_pic', array($this, 'listinghub_update_profile_pic'));					
				add_action('wp_ajax_listinghub_update_profile_setting', array($this, 'listinghub_update_profile_setting'));
				add_action('wp_ajax_listinghub_update_wp_post', array($this, 'listinghub_update_wp_post'));					
				add_action('wp_ajax_listinghub_save_wp_post', array($this, 'listinghub_save_wp_post'));	
				add_action('wp_ajax_listinghub_update_setting_password', array($this, 'listinghub_update_setting_password'));
				add_action('wp_ajax_listinghub_check_login', array($this, 'listinghub_check_login'));
				add_action('wp_ajax_nopriv_listinghub_check_login', array($this, 'listinghub_check_login'));
				add_action('wp_ajax_listinghub_forget_password', array($this, 'listinghub_forget_password'));
				add_action('wp_ajax_nopriv_listinghub_forget_password', array($this, 'listinghub_forget_password'));					
				add_action('wp_ajax_listinghub_cancel_stripe', array($this, 'listinghub_cancel_stripe'));								
				add_action('wp_ajax_listinghub_cancel_paypal', array($this, 'listinghub_cancel_paypal'));					
				add_action('wp_ajax_listinghub_profile_stripe_upgrade', array($this, 'listinghub_profile_stripe_upgrade'));
				add_action('wp_ajax_listinghub_save_favorite', array($this, 'listinghub_save_favorite'));						
				add_action('wp_ajax_listinghub_save_un_favorite', array($this, 'listinghub_save_un_favorite'));				
				add_action('wp_ajax_listinghub_applied_delete', array($this, 'listinghub_applied_delete'));	
				add_action('wp_ajax_listinghub_save_notification', array($this, 'listinghub_save_notification'));							
				add_action('wp_ajax_listinghub_delete_favorite', array($this, 'listinghub_delete_favorite'));
				add_action('wp_ajax_listinghub_candidate_delete', array($this, 'listinghub_candidate_delete'));
				add_action('wp_ajax_listinghub_candidate_reject', array($this, 'listinghub_candidate_reject'));
				add_action('wp_ajax_listinghub_candidate_shortlisted', array($this, 'listinghub_candidate_shortlisted'));
				add_action('wp_ajax_listinghub_candidate_schedule', array($this, 'listinghub_candidate_schedule'));
				add_action('wp_ajax_listinghub_profile_bookmark', array($this, 'listinghub_profile_bookmark'));
				add_action('wp_ajax_listinghub_profile_bookmark_delete', array($this, 'listinghub_profile_bookmark_delete'));
				add_action('wp_ajax_listinghub_employer_bookmark', array($this, 'listinghub_employer_bookmark'));
				add_action('wp_ajax_listinghub_employer_bookmark_delete', array($this, 'listinghub_employer_bookmark_delete'));
				add_action('wp_ajax_listinghub_message_delete', array($this, 'listinghub_message_delete'));
				add_action('wp_ajax_listinghub_message_send', array($this, 'listinghub_message_send'));
				add_action('wp_ajax_nopriv_listinghub_message_send', array($this, 'listinghub_message_send'));
				add_action('wp_ajax_listinghub_claim_send', array($this, 'listinghub_claim_send'));
				add_action('wp_ajax_nopriv_listinghub_claim_send', array($this, 'listinghub_claim_send'));					
				add_action('wp_ajax_listinghub_cron_listing', array($this, 'listinghub_cron_listing'));
				add_action('wp_ajax_nopriv_listinghub_cron_listing', array($this, 'listinghub_cron_listing'));	
				add_action('wp_ajax_listinghub_apply_submit_login', array($this, 'listinghub_apply_submit_login'));	
				add_action('wp_ajax_listinghub_author_email_popup', array($this, 'listinghub_author_email_popup'));
				add_action('wp_ajax_nopriv_listinghub_author_email_popup', array($this, 'listinghub_author_email_popup'));
				add_action('wp_ajax_listinghub_chatgtp_settings_popup', array($this, 'listinghub_chatgtp_settings_popup'));
				add_action('wp_ajax_nopriv_listinghub_chatgtp_settings_popup', array($this, 'listinghub_chatgtp_settings_popup'));
				add_action('wp_ajax_listinghub_chatgpt_upload_image', array($this, 'listinghub_chatgpt_upload_image'));
				add_action('wp_ajax_listinghub_finalerp_csv_product_upload', array($this, 'listinghub_finalerp_csv_product_upload'));
				add_action('wp_ajax_listinghub_save_csv_file_to_database', array($this, 'listinghub_save_csv_file_to_database'));
				add_action('wp_ajax_listinghub_eppro_get_import_status', array($this, 'listinghub_eppro_get_import_status'));		
				add_action('wp_ajax_listinghub_contact_popup', array($this, 'listinghub_contact_popup'));
				add_action('wp_ajax_nopriv_listinghub_contact_popup', array($this, 'listinghub_contact_popup'));
				add_action('wp_ajax_listinghub_listing_contact_popup', array($this, 'listinghub_listing_contact_popup'));
				add_action('wp_ajax_nopriv_listinghub_listing_contact_popup', array($this, 'listinghub_listing_contact_popup'));				
				add_action('wp_ajax_listinghub_listing_claim_popup', array($this, 'listinghub_listing_claim_popup'));
				add_action('wp_ajax_nopriv_listinghub_listing_claim_popup', array($this, 'listinghub_listing_claim_popup'));
								
				add_action('wp_ajax_listinghub_load_categories_fields_wpadmin', array($this, 'listinghub_load_categories_fields_wpadmin'));
				add_action('wp_ajax_nopriv_listinghub_load_categories_fields_wpadmin', array($this, 'listinghub_load_categories_fields_wpadmin'));
				add_action('wp_ajax_listinghub_save_post_without_user', array($this, 'listinghub_save_post_without_user'));
				add_action('wp_ajax_nopriv_listinghub_save_post_without_user', array($this, 'listinghub_save_post_without_user'));	
				add_action('wp_ajax_listinghub_save_user_review', array($this, 'listinghub_save_user_review'));	
				
				add_action('wp_ajax_listinghub_chatgpt_post_creator', array($this, 'listinghub_chatgpt_post_creator'));
				add_action('wp_ajax_nopriv_listinghub_chatgpt_post_creator', array($this, 'listinghub_chatgpt_post_creator'));
				
				add_action('add_meta_boxes', array($this, 'listinghub_custom_meta_listinghub'));
				add_action('save_post', array($this, 'listinghub_meta_save'));	
								
				add_action('pre_get_posts',array($this, 'listinghub_restrict_media_library') );	
				// 7. Shortcode
				add_shortcode('listinghub_price_table', array($this, 'listinghub_price_table_func'));				
				add_shortcode('listinghub_form_wizard', array($this, 'listinghub_form_wizard_func'));
				add_shortcode('listinghub_profile_template', array($this, 'listinghub_profile_template_func'));
				
				add_shortcode('listinghub_profile_public', array($this, 'listinghub_profile_public_func'));	
				add_shortcode('listinghub_login', array($this, 'listinghub_login_func'));
				add_shortcode('listinghub_author_directory', array($this, 'listinghub_author_directory_func'));					
				
				add_shortcode('listinghub_categories', array($this, 'listinghub_categories_func'));
				add_shortcode('listinghub_featured', array($this, 'listinghub_featured_func'));					
				add_shortcode('listinghub_map', array($this, 'listinghub_map_func'));												
				add_shortcode('listinghub_archive_grid_no_map', array($this, 'listinghub_archive_grid_no_map_func'));
				add_shortcode('listinghub_archive_grid', array($this, 'listinghub_archive_grid_func'));
				add_shortcode('listinghub_archive_grid_top_map', array($this, 'listinghub_archive_grid_top_map_func'));
				add_shortcode('listinghub_search', array($this, 'listinghub_search_func'));
				add_shortcode('listinghub_search_popup', array($this, 'listinghub_search_popup_func'));
				add_shortcode('listing_filter', array($this, 'listinghub_listing_filter_func'));					
				add_shortcode('listinghub_categories_carousel', array($this, 'listinghub_categories_carousel_func'));
				add_shortcode('listinghub_tags_carousel', array($this, 'listinghub_tags_carousel_func'));
				add_shortcode('listinghub_locations_carousel', array($this, 'listinghub_locations_carousel_func'));
				add_shortcode('listinghub_locations', array($this, 'listinghub_locations_func'));						
				add_shortcode('listinghub_reminder_email_cron', array($this, 'listinghub_reminder_email_cron_func'));
				add_shortcode('listinghub_add_listing', array($this, 'listinghub_add_listing_func'));	
				add_shortcode('listinghub_listing_detail_page', array($this, 'listinghub_listing_detail_page_func'));	
				
				// 8. Filter	
				add_filter( 'template_include', array($this, 'listinghub_include_template_function'), 9, 2  );
				
										
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'listinghub_plugin_action_links' ) );
				// For elementor
				add_action( 'init', array($this, 'listinghub_elementor_file') );
				// *** End elementor
				//---- COMMENT FILTERS ----//		
				add_action('init', array($this, 'listinghub_remove_admin_bar') );	
				add_action( 'init', array($this, 'listinghub_paypal_form_submit') );
				add_action( 'init', array($this, 'listinghub_stripe_form_submit') );
				add_action( 'init', array($this, 'listinghub_post_type') );
				add_action( 'init', array($this, 'listinghub_create_taxonomy_category'));
				add_action( 'init', array($this, 'listinghub_create_taxonomy_tags'));
				add_action( 'init', array($this, 'listinghub_create_taxonomy_locations'));
				add_action( 'init', array($this, 'ep_listinghub_pdf_cv') );
				add_action('init', array($this, 'listinghub_all_functions'));
				add_action( 'wp_loaded', array($this, 'listinghub_woocommerce_form_submit') );
				add_action( 'init', array($this, 'ep_listinghub_cpt_columns') );
				// Add color script
				add_action('wp_enqueue_scripts', array($this, 'listinghub_color_js') );
			}
			/**
				* Define constants needed across the plug-in.
			*/
			public function handle_cancel_email_verification() {
				if ( ! is_user_logged_in() ) {
					wp_send_json_error('Unauthorized');
				}
			
				$user_id = get_current_user_id();
			
				delete_user_meta($user_id, '_pending_new_email');
				delete_user_meta($user_id, '_email_change_token');
			
				wp_send_json_success('Pending email cancelled.');
				wp_die(); // Important!
			}
			private function define_constants() {
				if (!defined('ep_listinghub_BASENAME')) define('ep_listinghub_BASENAME', plugin_basename(__FILE__));
				if (!defined('ep_listinghub_DIR')) define('ep_listinghub_DIR', dirname(__FILE__));
				if (!defined('ep_listinghub_FOLDER'))define('ep_listinghub_FOLDER', plugin_basename(dirname(__FILE__)));
				if (!defined('ep_listinghub_ABSPATH'))define('ep_listinghub_ABSPATH', trailingslashit(str_replace("\\", "/", WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)))));
				if (!defined('ep_listinghub_URLPATH'))define('ep_listinghub_URLPATH', trailingslashit(plugins_url() . '/' . plugin_basename(dirname(__FILE__))));
				if (!defined('ep_listinghub_ADMINPATH'))define('ep_listinghub_ADMINPATH', get_admin_url());
				$filename = get_stylesheet_directory()."/listinghub/";
				if (!file_exists($filename)) {					
					if (!defined('ep_listinghub_template'))define( 'ep_listinghub_template', ep_listinghub_ABSPATH.'template/' );
					}else{
					if (!defined('ep_listinghub_template'))define( 'ep_listinghub_template', $filename);
				}	
			}				
			public function listinghub_remove_admin_bar() {
				$iv_hide = get_option('epjblistinghub_hide_admin_bar');
				if (!current_user_can('administrator') && !is_admin()) {
					if($iv_hide=='yes'){							
						show_admin_bar(false);
					}
				}	
			}
			
			public function listinghub_include_template_function( $template_path ) {
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}				
				$post_type = get_post_type();
				if($post_type==''){
					if(is_post_type_archive($listinghub_directory_url)){
						$post_type =$listinghub_directory_url;
					}
				}
				if ( $post_type ==$listinghub_directory_url ) { 	 
					if ( is_single() ) { 
						$directoryprosinglepage=get_option('directoryprosinglepage');
						if($directoryprosinglepage==''){$directoryprosinglepage='plugintemplate';}	
						if($directoryprosinglepage=='custompage'){
							 global $post;	
							$single_custompag=get_option('listing_single_custompage'); 
							$page_path= get_the_permalink($single_custompag);
							$template_path = add_query_arg( 'detail', $post->post_name, $page_path );
							wp_redirect($template_path);
							exit;
							
						}else{				
							$template_path =  ep_listinghub_template. 'listing/single-listing.php';	
							return $template_path;
						}
					}				
					if( is_tag() || is_category() || is_archive() ){
						$template_path =  ep_listinghub_template. 'listing/listing-layout.php';
					}
				}
				return $template_path;
			}

			/**
			 * Track listing view count (total) and session view count (one per browser session per listing).
			 * Runs on template_redirect so cookies can be set before any output.
			 */
			public function listinghub_track_listing_view() {
				$listing_type = get_option( 'ep_listinghub_url', 'listing' );
				if ( $listing_type === '' ) {
					$listing_type = 'listing';
				}

				$listing_id = 0;
				if ( is_singular( $listing_type ) ) {
					$listing_id = get_queried_object_id();
				} elseif ( is_page() && ! empty( $_GET['detail'] ) ) {
					$slug = sanitize_text_field( wp_unslash( $_GET['detail'] ) );
					$post = get_page_by_path( $slug, OBJECT, $listing_type );
					if ( $post && $post->ID ) {
						$listing_id = (int) $post->ID;
					}
				}

				if ( $listing_id <= 0 ) {
					return;
				}

				// Always increment total page views.
				$total = (int) get_post_meta( $listing_id, 'listing_views_count', true );
				update_post_meta( $listing_id, 'listing_views_count', $total + 1 );

				// Session view: only count once per browser session per listing (cookie-based).
				$cookie_name = 'listinghub_sess_views';
				$max_ids    = 200;
				$seen       = array();
				if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
					$raw = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
					$seen = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
				}
				if ( ! in_array( $listing_id, $seen, true ) ) {
					$session_count = (int) get_post_meta( $listing_id, 'listing_views_session_count', true );
					update_post_meta( $listing_id, 'listing_views_session_count', $session_count + 1 );
					$seen[] = $listing_id;
					$seen   = array_slice( array_unique( $seen ), -$max_ids );
					$value  = implode( ',', $seen );
					$expiry = 0; // Session cookie (until browser closes).
					if ( PHP_VERSION_ID >= 70300 ) {
						setcookie( $cookie_name, $value, array( 'expires' => $expiry, 'path' => '/', 'samesite' => 'Lax', 'secure' => is_ssl() ) );
					} else {
						setcookie( $cookie_name, $value, $expiry, '/; samesite=Lax' );
					}
				}
			}

			public function listinghub_create_taxonomy_category() {
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				register_taxonomy(
				$listinghub_directory_url.'-category',
				$listinghub_directory_url,
				array(
				'label' => esc_html__( 'Categories','listinghub' ),
				'rewrite' => array( 'slug' => $listinghub_directory_url.'-category' ),
				'hierarchical' => true,					
				'show_in_rest' =>	true,
				)
				);
			}
			public function listinghub_post_type() {
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$listinghub_directory_url_name=ucfirst($listinghub_directory_url);
				$labels = array(
				'name'                => esc_html__( $listinghub_directory_url_name,  'listinghub' ),
				'singular_name'       => esc_html__( $listinghub_directory_url_name,  'listinghub' ),
				'menu_name'           => esc_html__( $listinghub_directory_url_name, 'listinghub' ),
				'name_admin_bar'      => esc_html__( $listinghub_directory_url_name, 'listinghub' ),
				'parent_item_colon'   => esc_html__( 'Parent Item:', 'listinghub' ),
				'all_items'           => esc_html__( 'All ', 'listinghub' ).$listinghub_directory_url_name,
				'add_new_item'        => esc_html__( 'Add New Item', 'listinghub' ),
				'add_new'             => esc_html__( 'Add New', 'listinghub' ),
				'new_item'            => esc_html__( 'New Item', 'listinghub' ),
				'edit_item'           => esc_html__( 'Edit Item', 'listinghub' ),
				'update_item'         => esc_html__( 'Update Item', 'listinghub' ),
				'view_item'           => esc_html__( 'View Item', 'listinghub' ),
				'search_items'        => esc_html__( 'Search Item', 'listinghub' ),
				'not_found'           => esc_html__( 'Not found', 'listinghub' ),
				'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'listinghub' ),
				);
				$args = array(
				'label'               => esc_html__( $listinghub_directory_url_name, 'listinghub' ),
				'description'         => esc_html__( $listinghub_directory_url_name, 'listinghub' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'post-formats','custom-fields' ,'elementor'),					
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu' => 		'listinghub',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'show_in_rest' =>	true,	
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				);
				register_post_type( $listinghub_directory_url, $args );
						///******Review**********
				$labels2 = array(
				'name'                => esc_html__( 'Reviews',  'listinghub' ),
				'singular_name'       => esc_html__( 'Reviews', 'listinghub' ),
				'menu_name'           => esc_html__( 'Reviews', 'listinghub' ),
				'name_admin_bar'      =>esc_html__( 'Reviews', 'listinghub' ),
				'parent_item_colon'   => esc_html__( 'Parent Item:', 'listinghub' ),
				'all_items'           => esc_html__( 'All Reviews', 'listinghub' ),
				'add_new_item'        => esc_html__( 'Add New Review', 'listinghub' ),
				'add_new'             => esc_html__( 'Add New', 'listinghub' ),
				'new_item'            => esc_html__( 'New Review', 'listinghub' ),
				'edit_item'           => esc_html__( 'Edit Review', 'listinghub' ),
				'update_item'         => esc_html__( 'Update Review', 'listinghub' ),
				'view_item'           => esc_html__( 'View Review', 'listinghub' ),
				'search_items'        => esc_html__( 'Search Review', 'listinghub' ),
				'not_found'           => esc_html__( 'Not found', 'listinghub' ),
				'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'listinghub' ),
				);
				$args2 = array(
				'label'               => esc_html__( 'Reviews', 'listinghub' ),
				'description'         => esc_html__( 'Reviews: Directory Pro', 'listinghub' ),
				'labels'              => $labels2,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'post-formats','custom-fields' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu' => 		'listinghub',
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'show_in_rest' =>true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				);
				register_post_type( 'listinghub_review', $args2 );
				
				// Message 
				$labels4 = array(
				'name'                => esc_html__( 'Message', 'Post Type General Name', 'listinghub' ),
				'singular_name'       => esc_html__( 'Message', 'Post Type Singular Name', 'listinghub' ),
				'menu_name'           => esc_html__( 'Message', 'listinghub' ),
				'name_admin_bar'      => esc_html__( 'Message', 'listinghub' ),
				'parent_item_colon'   => esc_html__( 'Parent Item:', 'listinghub' ),
				'all_items'           => esc_html__( 'All Message', 'listinghub' ),
				'add_new_item'        => esc_html__( 'Add New Item', 'listinghub' ),
				'add_new'             => esc_html__( 'Add New', 'listinghub' ),
				'new_item'            => esc_html__( 'New Item', 'listinghub' ),
				'edit_item'           => esc_html__( 'Edit Item', 'listinghub' ),
				'update_item'         => esc_html__( 'Update Item', 'listinghub' ),
				'view_item'           => esc_html__( 'View Item', 'listinghub' ),
				'search_items'        => esc_html__( 'Search Item', 'listinghub' ),
				'not_found'           => esc_html__( 'Not found', 'listinghub' ),
				'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'listinghub' ),
				);
				$args4 = array(
				'label'               => esc_html__( 'Message', 'listinghub' ),
				'description'         => esc_html__( 'Message', 'listinghub' ),
				'labels'              => $labels4,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'post-formats','custom-fields' ),					
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu' => 		'listinghub',
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'show_in_rest' =>true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				);
				register_post_type( 'listinghub_message', $args4 );
			}
			public function listinghub_post_type_tags_fix($request) {
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				if ( isset($request['tag']) && !isset($request['post_type']) ){
					$request['post_type'] = $listinghub_directory_url;
				}
				return $request;
			} 
			public function ep_listinghub_cpt_columns(){ 				
				require_once(ep_listinghub_DIR . '/admin/pages/manage-cpt-columns.php');				
			}
			public function listinghub_plugin_action_links( $links ) {
				return array_merge( array(
				'settings' => '<a href="admin.php?page=listinghub-settings">' . esc_html__( 'Settings', 'listinghub' ).'</a>',
				'doc'  => '<a href="'.esc_url('http://help.eplug-ins.com/listinghub').'">' . esc_html__( 'Docs', 'listinghub' ) . '</a>',
				), $links );
			}	
		
			
			public function author_public_profile() {
				$author = get_the_author();	
				$iv_redirect = get_option('epjblistinghub_profile_public_page');
				if($iv_redirect!='defult'){ 
					$reg_page= get_permalink( $iv_redirect) ; 
					return    $reg_page.'?&id='.$author; 
					exit;
				}
			}
			public function listinghub_chatgtp_settings_popup(){
				include( ep_listinghub_template. 'private-profile/chatgtp_settings_popup.php');
				exit(0);
			}
			public function listinghub_chatgpt_post_creator(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'addlisting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				
				parse_str($_POST['form_data'], $form_data);
				  $title=sanitize_text_field($form_data['gpt_title']);	
				  $apiKey =  get_option('listinghub_openai_api_key');	
				  $feature_image_url='';
				  $gpt_model=get_option('listinghub_gpt_model');	
				  if($gpt_model==""){$gpt_model = 'gpt-3.5-turbo-instruct';}	
				  $modelId = $gpt_model; // Change this to the desired GPT-3 model ID
				  
				  // Set up the request data fr Content
				  $requestData = array(
					'model' => $modelId,
					'prompt' => $title . '\n\n',
					'temperature' => 0.5, 
					'max_tokens' => (int)$form_data['max_tokens'], 
					'n' => 1,
					 'stop' => '\n\n'
				  );	
				  
				  $ch = curl_init();
				  curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
				  curl_setopt($ch, CURLOPT_POST, 1);
				  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $apiKey,
				  ));
				  $response = curl_exec($ch);
				  curl_close($ch);	
				  $responseData = json_decode($response, true);
				  $content = $responseData['choices'][0]['text'];				  
				  // End content
				  
				  // Start FAQs
					$requestData = array(
					'model' => $modelId,
					'prompt' => 'Write '.$form_data['gpt_faq_number'].' FAQ for  ' . $title . ' \n\n',
					'temperature' => 0.5, 
					'max_tokens' =>1024, 
					'n' => 1,
					 'stop' => '\n\n'
				  );						 
					  $ch = curl_init();
					  curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
					  curl_setopt($ch, CURLOPT_POST, 1);
					  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
					  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Authorization: Bearer ' . $apiKey,
					  ));
					  $response = curl_exec($ch);
					  curl_close($ch); 
					  $responseData = json_decode($response, true);
					 
					  $faqs = $responseData['choices'][0]['text'];
					  
				  // End 	
				  
				  if(isset($form_data['listinghub_feature_image_chatgpt'])){
				   // Feature_image_size image				
						$url = 'https://api.openai.com/v1/images/generations';
						$data = [
							'model' => 'image-alpha-001',
							'prompt' => $title,
							'num_images' => 4,
							'size' => '512x512',
							'response_format' => 'url'
						];
						$curl = curl_init();
						curl_setopt_array($curl, array(
						  CURLOPT_URL => $url,
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => '',
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 30,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => 'POST',
						  CURLOPT_POSTFIELDS => json_encode($data),
						  CURLOPT_HTTPHEADER => array(
							'Authorization: Bearer ' . $apiKey,
							'Content-Type: application/json'
						  ),
						));

						$response = curl_exec($curl);
						
						if (curl_error($curl)) {					 
						} else {
								$response_all= json_decode($response, true);
								if(isset($response_all['data'][0]['url'])){
								$image_url = $response_all['data'][0]['url'];
								}
								if(isset($response_all['data'][1]['url'])){
								$image_url = $image_url .'|'.$response_all['data'][1]['url'];	
								}
								if(isset($response_all['data'][2]['url'])){
									$image_url = $image_url .'|'.$response_all['data'][2]['url'];	
								}
								if(isset($response_all['data'][3]['url'])){
								$image_url = $image_url .'|'.$response_all['data'][3]['url'];	
								}	
						  $feature_image_url =$image_url;
						}
						curl_close($curl);
					}else{
						$feature_image_url='off';
					}	
				  // End Feature_image		  
					//FAQ maker		
					$qa_pairs_ep = explode("\n\n", $faqs);
					$qa_pairs_noempty = array_filter($qa_pairs_ep, function($value) { return !empty($value); });
								
					$i=0;	$faq_html='';					
					foreach ($qa_pairs_noempty as $qa_pair) {
						if(!empty($qa_pair)){
							$qa_pair_q_n_a = explode("\n", $qa_pair);
							if(isset($qa_pair_q_n_a[0]) AND isset($qa_pair_q_n_a[1])){							
							$faq_html=$faq_html.'<div class="row border-bottom mb-4" id="faq_delete_'.esc_html($i).'"> <div class="col-md-5 form-group"> <input type="text" class="form-control" name="faq_title[]" id="faq_title[]" value="'.esc_html($qa_pair_q_n_a[0]).'" placeholder="FAQ"></div><div class="col-md-6 form-group"><textarea rows="2"  name="faq_description[]" id="faq_description[]" placeholder="Answer">'.$qa_pair_q_n_a[1].'</textarea></div><div class="col-md-1 form-group pull-right"><button type="button" onclick="listinghub_faq_delete('. esc_html($i).');"  class="btn btn-small-ar"><span class="dashicons dashicons-trash"></span></button></div><div class="row"><hr></div></div><div class="clearfix"></div>';	
							}
						$i++;
						}						
					}					
				
					
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub'),"content"=> $content,"faqs"=> $faq_html,'feature_image_url'=>$feature_image_url));
				exit(0);
			}
			public function listinghub_chatgpt_upload_image(){
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'addlisting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $current_user;				
				parse_str($_POST['form_data'], $form_data);
				if(isset($form_data['gpt_image'])){
					$url = $form_data['gpt_image'];
					$image_url='';$attachment_id='';	
					$attachment_id = media_sideload_image($url, 0, 'Image description','id');			
					if (!is_wp_error($attachment_id)) {
						$image_url_arr = wp_get_attachment_image_src( $attachment_id, 'full' );
						if(isset($image_url_arr[0])){
							$image_url = $image_url_arr[0];
						}						
					}					
				}			
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub'),"attachment_id"=> $attachment_id,"image_url"=> $image_url ));
				exit(0);
			}
			public function listinghub_login_func($atts = ''){
				global $current_user;
				ob_start();		
				if($current_user->ID==0){
					include(ep_listinghub_template. 'private-profile/profile-login.php');
					}else{	
					include( ep_listinghub_template. 'private-profile/profile-template-1.php');
				}	
				$content = ob_get_clean();	
				return $content;
			}
			public function listinghub_forget_password(){
				parse_str($_POST['form_data'], $data_a);
				if( ! email_exists($data_a['forget_email']) ) {
					echo json_encode(array("code" => "not-success","msg"=>"There is no user registered with that email address."));
					exit(0);
					} else {
					require_once( ep_listinghub_ABSPATH. 'inc/forget-mail.php');
					echo json_encode(array("code" => "success","msg"=>"Updated Successfully"));
					exit(0);
				}
			}
			public function listinghub_check_login(){
				parse_str($_POST['form_data'], $form_data);
				global $user;
				$creds = array();
				$creds['user_login'] =sanitize_text_field($form_data['username']);
				$creds['user_password'] =  sanitize_text_field($form_data['password']);
				$creds['remember'] = 'true';
				$secure_cookie = is_ssl() ? true : false;
				$user = wp_signon( $creds, $secure_cookie );
				if ( is_wp_error($user) ) {
					echo json_encode(array("code" => "not-success","msg"=>$user->get_error_message()));
					exit(0);
				}
				if ( !is_wp_error($user) ) {
					$iv_redirect = get_option('epjblistinghub_profile_page');
					if($iv_redirect!='defult'){
						$reg_page= get_permalink( $iv_redirect); 
						echo json_encode(array("code" => "success","msg"=>$reg_page));
						exit(0);
					}
				}		
			}
			public function get_unique_keyword_values( $key = 'keyword', $post_type='listing' ){
				global $wpdb;
				if( empty( $key ) ){
					return;
				}	
				$res=array();
				$args = array(
				'post_type' => $post_type, // enter your custom post type						
				'post_status' => 'publish',						
				'posts_per_page'=> -1,  // overrides posts per page in theme settings
				);
				$query_auto = new WP_Query( $args );
				$posts_auto = $query_auto->posts;						
				foreach($posts_auto as $post_a) {
					$res[]=$post_a->post_title;
				}	
				return $res;
			}
			public function get_unique_post_meta_values( $key = 'postcode', $post_type='listing' ){
				global $wpdb;
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				if( empty( $key ) ){
					return;
				}	
				$res = $wpdb->get_col( $wpdb->prepare( "
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type='{$post_type}' AND  pm.meta_key = '%s'						
				", $key) );
				return $res;
			}  
			public function listinghub_check_field_input_access($field_key_pass, $field_value, $template='myaccount', $user_id=0){ 
				if($template=='myaccount'){				
					$current_user_id=$user_id;					
					}else{
					$current_user_id=0;		
				}					
				$field_type_opt=  get_option( 'listinghub_field_type' );
				if(!empty($field_type_opt)){
					$field_type=get_option('listinghub_field_type' ); 
					}else{
					$field_type= array();
					$field_type['full_name']='text';								
					$field_type['company_since']='datepicker';
					$field_type['team_size']='text';									
					$field_type['phone']='text';
					$field_type['mobile']='text';
					$field_type['address']='text';
					$field_type['city']='text';
					$field_type['postcode']='text';
					$field_type['state']='text';
					$field_type['country']='text';										
					$field_type['listing_title']='text';									
					$field_type['hourly_rate']='text';
					$field_type['experience']='text';
					$field_type['age']='text';
					$field_type['qualification']='text';								
					$field_type['gender']='radio';	
					$field_type['website']='url';
					$field_type['description']='textarea';			
				}
				$field_type_value= get_option( 'listinghub_field_type_value' );
				if($field_type_value==''){
					$field_type_value=array();
					$field_type_value['gender']=esc_html__('Female,Male,Other', 'listinghub');	
				}	
				$return_value='';
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='dropdown'){	 								
					$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
					$return_value=$return_value.'<div class="col-md-6"><div class="form-group">
					<label class="control-label">'. esc_html($field_value).'</label>
					<select name="'. esc_html($field_key_pass).'" id="'.esc_attr($field_key_pass).'" class="form-control col-md-12"  >';				
					foreach($dropdown_value as $one_value){	 
						if(trim($one_value)!=''){
							$return_value=$return_value.'<option '.(trim(get_user_meta($current_user_id,$field_key_pass,true))==trim($one_value)?' selected':'').' value="'. esc_attr($one_value).'">'. esc_html($one_value).'</option>';
						}
					}	
					$return_value=$return_value.'</select></div></div>';					
				}
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='checkbox'){	 								
					$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
					$return_value=$return_value.'<div class="col-md-6"><div class="form-group">
					<label class="control-label ">'. esc_html($field_value).'</label>						
					';
					$saved_checkbox_value =	explode(',',get_user_meta($current_user_id,$field_key_pass,true));
					foreach($dropdown_value as $one_value){
						if(trim($one_value)!=''){
							$return_value=$return_value.'
							<div class="form-check form-check-inline">
							<label class="form-check-label" for="'. esc_attr($one_value).'">
							<input '.( in_array($one_value,$saved_checkbox_value)?' checked':'').' class=" form-check-input" type="checkbox" name="'. esc_attr($field_key_pass).'[]"  id="'. esc_attr($one_value).'" value="'. esc_attr($one_value).'">
							'. esc_html($one_value).' </label>
							</div>';
						}
					}	
					$return_value=$return_value.'</div></div>';						
				}
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='radio'){	 								
					$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
					$return_value=$return_value.'<div class="col-md-6"><div class="form-group ">
					<label class="control-label ">'. esc_html($field_value).'</label>
					';						
					foreach($dropdown_value as $one_value){	 
						if(trim($one_value)!=''){
							$return_value=$return_value.'
							<div class="form-check form-check-inline">
							<label class="form-check-label" for="'. esc_attr($one_value).'">
							<input '.(get_user_meta($current_user_id,$field_key_pass,true)==$one_value?' checked':'').' class="form-check-input" type="radio" name="'. esc_attr($field_key_pass).'"  id="'. esc_attr($one_value).'" value="'. esc_attr($one_value).'">
							'. esc_html($one_value).'</label>
							</div>														
							';
						}
					}	
					$return_value=$return_value.'</div></div>';					
				}					 
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='textarea'){	 
					$return_value=$return_value.'<div class="col-md-12"><div class="form-group">';
					$return_value=$return_value.'<label class="control-label ">'. esc_html($field_value).'</label>';
					$return_value=$return_value.'<textarea  placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-textarea col-md-12"  rows="4"/>'.esc_textarea(get_user_meta($current_user_id,$field_key_pass,true)).'</textarea></div></div>';
				}
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='datepicker'){	 
					$return_value=$return_value.'<div class="col-md-6"><div class="form-group ">';
					$return_value=$return_value.'<label class="control-label ">'. esc_html($field_value).'</label>';
					$return_value=$return_value.'<input type="text" placeholder="'.esc_html__('Select ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control epinputdate " value="'.esc_attr(get_user_meta($current_user_id,$field_key_pass,true)).'"/></div></div>';
				}
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='text'){ 
					if($field_value=='address'){								
						$return_value=$return_value.'<input type="hidden" class="form-control" name="address" id="address" value="'. esc_attr(get_user_meta($current_user_id,'address',true)).'" >									
						<div class=" form-group col-md-12">
						<label for="text" class=" control-label">'.esc_html__('Address','listinghub').'</label>
						<div id="map"></div>
						<div id="search-box"></div>
						<div id="result"></div>
						</div>';
						}else{
						$return_value=$return_value.'<div class="col-md-6"><div class="form-group ">';
						$return_value=$return_value.'<label class="control-label ">'. esc_html($field_value).'</label>';
						$return_value=$return_value.'<input type="text" placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control " value="'.esc_attr(get_user_meta($current_user_id,$field_key_pass,true)).'"/></div></div>';
					}
				}
				if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='url'){	 
					$return_value=$return_value.'<div class="col-md-6"><div class="form-group ">';
					$return_value=$return_value.'<label class="control-label ">'. esc_html($field_value).'</label>';
					$return_value=$return_value.'<input type="text" placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control " value="'.esc_url(get_user_meta($current_user_id,$field_key_pass,true)).'"/></div></div>';
				}
				return $return_value;
			}
			public function listinghub_check_field_input_access_signup($field_key_pass, $field_value){ 
				$sign_up_array=		get_option( 'listinghub_signup_fields');
				$require_array=		get_option( 'listinghub_signup_require');
				$field_type=  		get_option( 'listinghub_field_type' );
				$field_type_value=  get_option( 'listinghub_field_type_value' );
				$field_type_roles=  get_option( 'listinghub_field_type_roles' );
				$myaccount_fields_array=  get_option( 'listinghub_myaccount_fields' );
				$return_value='';
				$require='no';				
				if(isset($require_array[$field_key_pass]) && $require_array[$field_key_pass] == 'yes') {
					$require='yes';
				}
				if(isset($sign_up_array[$field_key_pass]) && $sign_up_array[$field_key_pass]=='yes'){
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='dropdown'){	 								
						$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
						$return_value=$return_value.'<div class="form-group row">
						<label class="control-label col-md-12">'. esc_html($field_value).'</label>
						<div class="col-md-8"><select name="'. esc_html($field_key_pass).'" id="'.esc_attr($field_key_pass).'" class="form-dropdown col-md-12" '.($require=='yes'?'data-validation="required" data-validation-error-msg="'. esc_html__('This field cannot be left blank','listinghub').'"':'').'>';				
						foreach($dropdown_value as $one_value){	 	
							if(trim($one_value)!=''){
								$return_value=$return_value.'<option value="'. esc_attr($one_value).'">'. esc_html($one_value).'</option>';
							}
						}	
						$return_value=$return_value.'</select></div></div>';					
					}
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='checkbox'){	 								
						$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
						$return_value=$return_value.'<div class="form-group row">
						<label class="control-label col-md-12">'. esc_html($field_value).'</label>
						<div class="col-md-8">
						<div class="" >
						';
						foreach($dropdown_value as $one_value){
							if(trim($one_value)!=''){
								$return_value=$return_value.'
								<div class="form-check form-check-inline col-md-12">
								<input class=" form-check-input" type="checkbox" name="'. esc_attr($field_key_pass).'[]"  id="'. esc_attr($one_value).'" value="'. esc_attr($one_value).'" '.($require=='yes'?'data-validation="required" data-validation-error-msg="'. esc_html__('Required','listinghub').'"':'').'>
								<label class="form-check-label" for="'. esc_attr($one_value).'">							
								'. esc_attr($one_value).' </label>
								</div>';
							}
						}	
						$return_value=$return_value.'</div></div></div>';						
					}
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='radio'){	 								
						$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
						$return_value=$return_value.'<div class="form-group row ">
						<label class="control-label col-md-12">'. esc_html($field_value).'</label>
						<div class="col-md-8">
						<div class="" >
						';						
						foreach($dropdown_value as $one_value){	 		
							if(trim($one_value)!=''){
								$return_value=$return_value.'
								<div class="form-check form-check-inline col-md-4">
								<label class="form-check-label" for="'. esc_attr($one_value).'">
								<input class="form-check-input" type="radio" name="'. esc_attr($field_key_pass).'"  id="'. esc_attr($one_value).'" value="'. esc_attr($one_value).'" '.($require=='yes'?'data-validation="required" data-validation-error-msg="'. esc_html__('Required','listinghub').'"':'').'>
								'. esc_attr($one_value).'</label>
								</div>';
							}
						}	
						$return_value=$return_value.'</div></div></div>';					
					}					 
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='textarea'){	 
						$return_value=$return_value.'<div class="form-group row">';
						$return_value=$return_value.'<label class="control-label col-md-12">'. esc_html($field_value).'</label><div class="col-md-8">';
						$return_value=$return_value.'<textarea  placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-textarea col-md-12"  rows="4"/ '.($require=='yes'?'data-validation="length" data-validation-length="2-100"':'').'></textarea></div></div>';
					}
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='datepicker'){	 
						$return_value=$return_value.'<div class="form-group row">';
						$return_value=$return_value.'<label class="control-label col-md-12">'. esc_html($field_value).'</label>';
						$return_value=$return_value.'<div class="col-md-12"><input type="text" placeholder="'.esc_html__('Select ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-date col-md-12 epinputdate " '.($require=='yes'?'data-validation="required" data-validation-error-msg="'. esc_html__('This field cannot be left blank','listinghub').'"':'').' /></div></div>';
					}
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='text'){	 
						$return_value=$return_value.'<div class="form-group row">';
						$return_value=$return_value.'<label class="control-label col-md-12" style="display: none;">'. esc_html($field_value).'</label>';
						$return_value=$return_value.'<div class="col-md-12 mb-4"><input type="text" placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control form-control-solid placeholder-no-fix" '.($require=='yes'?'data-validation="length" data-validation-length="2-100"':'').' /></div></div>';
					}
					if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='url'){	 
						$return_value=$return_value.'<div class="form-group row">';
						$return_value=$return_value.'<label class="control-label col-md-12">'. esc_html($field_value).'</label>';
						$return_value=$return_value.'<div class="col-md-12"><input type="text" placeholder="'.esc_html__('Enter ','listinghub').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-input col-md-12" '.($require=='yes'?'data-validation="length" data-validation-length="2-100"':'').' /></div></div>';
					}
				}
				return $return_value;
			}
			public function listinghub_save_user_review(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'listing' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $current_user;
				parse_str($_POST['form_data'], $form_data);
				$post_type = 'listinghub_review';
				$args = array(
				'post_type' => $post_type, // enter your custom post type
				'author' => sanitize_text_field($form_data['listingid']),
				);
				$the_query_review = new WP_Query( $args );
				$deleteid ='';
				if ( $the_query_review->have_posts() ) :
				while ( $the_query_review->have_posts() ) : $the_query_review->the_post();
				$deleteid = get_the_ID();
				if(get_post_meta($deleteid,'review_submitter',true)==$current_user->ID){
					wp_delete_post($deleteid );
				}
				endwhile;
				endif;
				$my_post= array();
				$my_post['post_author'] = sanitize_text_field($form_data['listingid']);
				$my_post['post_title'] = sanitize_text_field($form_data['review_subject']);
				$my_post['post_content'] = sanitize_textarea_field($form_data['review_comment']);
				$my_post['post_status'] = 'publish';
				$my_post['post_type'] = $post_type;
				$newpost_id= wp_insert_post( $my_post );
				$review_value=1;
				if(isset($form_data['star']) ){$review_value=sanitize_text_field($form_data['star']);}
				update_post_meta($newpost_id, 'review_submitter', $current_user->ID);
				update_post_meta($newpost_id, 'review_value', $review_value);	
				
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function user_profile_image_upload($userid){
				$iv_membership_signup_profile_pic=get_option('listinghub_signup_profile_pic');
				if($iv_membership_signup_profile_pic=='' ){ $iv_membership_signup_profile_pic='yes';}	
				if($iv_membership_signup_profile_pic=='yes' ){ 
					if ( 0 < $_FILES['profilepicture']['error'] ) { 
											
					}else {  
						$new_file_type = mime_content_type( $_FILES['profilepicture']['tmp_name'] );	
						if( in_array( $new_file_type, get_allowed_mime_types() ) ){   
							$upload_dir   = wp_upload_dir();
							$date = date('YmdHis');						
							$file_name = $date.$_FILES['profilepicture']['name'];
							$validate = wp_check_filetype( $file_name );
							if ( $validate['type'] == true ) {
							$return= move_uploaded_file($_FILES['profilepicture']['tmp_name'],  $upload_dir['basedir'].'/'.$file_name);
							if($return){  
								$image_url= $upload_dir['baseurl'].'/'.$file_name;
								update_user_meta($userid, 'listinghub_profile_pic_thum',sanitize_url($image_url));
							}
						  }
						}
					}
				}
			}
			public function listinghub_update_wp_post(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'addlisting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_die( 'Are you cheating:user Permission?' );								
				}
				global $current_user;global $wpdb;	
				$allowed_html = wp_kses_allowed_html( 'post' );	
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				parse_str($_POST['form_data'], $form_data);
				$newpost_id= sanitize_text_field($form_data['user_post_id']);
				$my_post = array();
				$my_post['ID'] = $newpost_id;
				$my_post['post_title'] = sanitize_text_field($form_data['title']);
				$my_post['post_content'] =  wp_kses( $form_data['new_post_content'], $allowed_html);
				$my_post['post_type'] 	= $listinghub_directory_url;					
				$listinghub_user_can_publish=get_option('listinghub_user_can_publish');	
				if($listinghub_user_can_publish==""){$listinghub_user_can_publish='yes';}	
				$my_post['post_status']=$form_data['post_status'];
				if($form_data['post_status']=='publish'){					
					$my_post['post_status']='pending';
					if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
						$my_post['post_status']='publish';
						}else{ 
						if($listinghub_user_can_publish=="yes"){ 
							$my_post['post_status']='publish';
							}else{
							$my_post['post_status']='pending';
						}								
					}						
				}
				wp_update_post( $my_post );
				if(isset($form_data['feature_image_id'] ) AND $form_data['feature_image_id']!='' ){
					$attach_id =sanitize_text_field($form_data['feature_image_id']);
					set_post_thumbnail( sanitize_text_field($form_data['user_post_id']), $attach_id );
					}else{
					$attach_id='0';
					delete_post_thumbnail( sanitize_text_field($form_data['user_post_id']));
				}
				if(isset($form_data['postcats'] )){ 
					$category_ids = $form_data['postcats'];
					$input_array_data= sanitize_text_field($category_ids) ;
					if(is_array($category_ids)){
						$input_array_data= array();
						foreach($category_ids as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-category');
				}
				if(isset($form_data['new_category'] )){						
					$tag_new= explode(",", $form_data['new_category']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-category');
					}
				}	
				// Location
				if(isset($form_data['location_arr'] )){ 
					$location_arr = $form_data['location_arr'];
					$input_array_data= sanitize_text_field($location_arr) ;
					if(is_array($location_arr)){
						$input_array_data= array();
						foreach($location_arr as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-locations');
				}
				
				if(isset($form_data['new_location'] )){						
					$tag_new= explode(",", $form_data['new_location']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-locations');
					}
				}	
				// Check Feature*************	
				$post_author_id= $current_user->ID;
				$author_package_id=get_user_meta($post_author_id, 'listinghub_package_id', true);
				$have_package_feature= get_post_meta($author_package_id,'listinghub_package_feature',true);
				$exprie_date= strtotime (get_user_meta($post_author_id, 'listinghub_exprie_date', true));
				$current_date=time();						
				if($have_package_feature=='yes'){
					if($exprie_date >= $current_date){ 
						update_post_meta($newpost_id, 'listinghub_featured', 'featured' );	
					}	
					}else{
					update_post_meta($newpost_id, 'listinghub_featured', 'no' );	
				}
				// listing detail *****	
			
				// For Tag Save tag_arr			
				$tag_all='';
				if(isset($form_data['tag_arr'] )){
					$tag_name= $form_data['tag_arr'] ;	
					$input_array_data= sanitize_text_field($tag_name) ;
					if(is_array($tag_name)){
						$input_array_data= array();
						foreach($tag_name as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					$i=0;$tag_all='';						
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-tag');							
				}
				$tag_all='';
				if(isset($form_data['new_tag'] )){						
					$tag_new= explode(",", $form_data['new_tag']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-tag');											
						$i++;	
					}
				}	
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'latitude', sanitize_text_field($form_data['latitude']));  
				update_post_meta($newpost_id, 'longitude', sanitize_text_field($form_data['longitude']));					
				update_post_meta($newpost_id, 'city', sanitize_text_field($form_data['city'])); 
				update_post_meta($newpost_id, 'state', sanitize_text_field($form_data['state'])); 
				update_post_meta($newpost_id, 'postcode', sanitize_text_field($form_data['postcode'])); 
				update_post_meta($newpost_id, 'country', sanitize_text_field($form_data['country'])); 
				update_post_meta($newpost_id, 'local-area', sanitize_text_field($form_data['local-area'])); 
				
				$opening_day=array();
				if(isset($form_data['day_name'] )){
					$day_name= $form_data['day_name'] ;
					$day_value1 = $form_data['day_value1'];
					$day_value2 = $form_data['day_value2'] ;
					$i=0;
					foreach($day_name  as $one_meta){
						if(isset($day_name[$i]) and isset($day_value1[$i]) ){
							if($day_name[$i] !=''){
								$opening_day[sanitize_text_field($day_name[$i])]= array(sanitize_text_field($day_value1[$i])=>sanitize_text_field($day_value2[$i])) ;
							}
						}
						$i++;
					}
					update_post_meta($newpost_id, '_opening_time', $opening_day);
				}
				// For FAQ Save
				// Delete 1st
				$i=0;
				for($i=0;$i<20;$i++){
					delete_post_meta($newpost_id, 'faq_title'.$i);							
					delete_post_meta($newpost_id, 'faq_description'.$i);
				}
				// Delete End
				if(isset($form_data['faq_title'] )){
					$faq_title= $form_data['faq_title']; //this is array data we sanitize later, when it save				
					$faq_description= $form_data['faq_description'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($faq_title[$i]) AND $faq_title[$i]!=''){
							update_post_meta($newpost_id, 'faq_title'.$i, sanitize_text_field($faq_title[$i]));
							update_post_meta($newpost_id, 'faq_description'.$i, sanitize_textarea_field($faq_description[$i]));
						}
					}
				}
				
				// End FAQ
				$default_fields = array();
				$field_set=get_option('listinghub_li_fields' );
				if($field_set!=""){ 
					$default_fields=get_option('listinghub_li_fields' );
					}else{															
					$default_fields['business_type']='Business Type';
					$default_fields['main_products']='Main Products';
					$default_fields['number_of_employees']='Number Of Employees';
					$default_fields['main_markets']='Main Markets';
					$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
				}				
				if(sizeof($default_fields )){			
					foreach( $default_fields as $field_key => $field_value ) { 
						update_post_meta($newpost_id, $field_key, $form_data[$field_key] );							
					}					
				}
				// listing detail*****		
				
				if(isset($form_data['dirpro_email_button'])){						
					update_post_meta($newpost_id, 'dirpro_email_button', sanitize_text_field($form_data['dirpro_email_button'])); 
				}
				if(isset($form_data['dirpro_web_button'])){						
					update_post_meta($newpost_id, 'dirpro_web_button', sanitize_text_field($form_data['dirpro_web_button'])); 
				}
				update_post_meta($newpost_id, 'image_gallery_ids', sanitize_text_field($form_data['gallery_image_ids'])); 
				update_post_meta($newpost_id, 'topbanner', sanitize_text_field($form_data['topbanner_image_id'])); 
				if(isset($form_data['feature_image_id'] )){
					$attach_id =sanitize_text_field($form_data['feature_image_id']);
					set_post_thumbnail( $newpost_id, $attach_id );					
				}	
				
				update_post_meta($newpost_id, 'listing_contact_source', sanitize_text_field($form_data['contact_source']));  
				update_post_meta($newpost_id, 'company_name', sanitize_text_field($form_data['company_name']));
				update_post_meta($newpost_id, 'phone', sanitize_text_field($form_data['phone'])); 
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'contact-email', sanitize_text_field($form_data['contact-email'])); 
				update_post_meta($newpost_id, 'contact_web', sanitize_text_field($form_data['contact_web']));				
				update_post_meta($newpost_id, 'vimeo', sanitize_text_field($form_data['vimeo'])); 
				update_post_meta($newpost_id, 'youtube', sanitize_text_field($form_data['youtube'])); 
				delete_post_meta($newpost_id, 'listinghub-tags');
				delete_post_meta($newpost_id, 'listinghub-category');
				delete_post_meta($newpost_id, 'listinghub-locations');
				
				if($form_data['post_status']=='publish'){ 
					include( ep_listinghub_ABSPATH. 'inc/add-listing-notification.php');
					
					include( ep_listinghub_ABSPATH. 'inc/notification.php');
					
				}
				
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);				
			}
			public function listinghub_save_wp_post(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'addlisting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				$allowed_html = wp_kses_allowed_html( 'post' );	
				global $current_user; global $wpdb;	
				parse_str($_POST['form_data'], $form_data);				
				$my_post = array();
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$post_type = $listinghub_directory_url;
				$listinghub_user_can_publish=get_option('listinghub_user_can_publish');	
				if($listinghub_user_can_publish==""){$listinghub_user_can_publish='yes';}	
				if($form_data['post_status']=='publish'){					
					$form_data['post_status']='pending';
					if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
						$form_data['post_status']='publish';
						}else{
						if($listinghub_user_can_publish=="yes"){
							$form_data['post_status']='publish';
							}else{
							$form_data['post_status']='pending';
						}								
					}						
				}
				$my_post['post_title'] = sanitize_text_field($form_data['title']);
				$my_post['post_content'] = wp_kses( $form_data['new_post_content'], $allowed_html); 
				$my_post['post_type'] = $post_type;
				$my_post['post_status'] = sanitize_text_field($form_data['post_status']);										
				$newpost_id= wp_insert_post( $my_post );
				update_post_meta($newpost_id, 'listinghub_listing_status', sanitize_text_field($form_data['listing_type'])); 
				// WPML Start******
				if ( function_exists('icl_object_id') ) {
					include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
					$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;
					$query =$wpdb->prepare( "UPDATE {$wpdb->prefix}icl_translations SET element_type='post_%s' WHERE element_id='%s' LIMIT 1",$post_type,$newpost_id );
					$wpdb->query($query);					
				}
				// End WPML**********	
				if(isset($form_data['postcats'] )){ 				
					$category_ids = $form_data['postcats'];
					$input_array_data= sanitize_text_field($category_ids) ;
					if(is_array($category_ids)){
						$input_array_data= array();
						foreach($category_ids as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-category');
				}
				if(isset($form_data['new_category'] )){						
					$tag_new= explode(",", $form_data['new_category']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-category');							
					}
				}	
				$opening_day=array();
				if(isset($form_data['day_name'] )){
					$day_name= $form_data['day_name'] ;
					$day_value1 = $form_data['day_value1'];
					$day_value2 = $form_data['day_value2'] ;
					$i=0;
					foreach($day_name  as $one_meta){
						if(isset($day_name[$i]) and isset($day_value1[$i]) ){
							if($day_name[$i] !=''){
								$opening_day[sanitize_text_field($day_name[$i])]= array(sanitize_text_field($day_value1[$i])=>sanitize_text_field($day_value2[$i])) ;
							}
						}
						$i++;
					}
					update_post_meta($newpost_id, '_opening_time', $opening_day);
				}
				// For FAQ Save				
				if(isset($form_data['faq_title'] )){
					$faq_title= $form_data['faq_title']; //this is array data we sanitize later, when it save				
					$faq_description= $form_data['faq_description'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($faq_title[$i]) AND $faq_title[$i]!=''){
							update_post_meta($newpost_id, 'faq_title'.$i, sanitize_text_field($faq_title[$i]));
							update_post_meta($newpost_id, 'faq_description'.$i, sanitize_textarea_field($faq_description[$i]));
						}
					}
				}
				// End FAQ
				// Location
				if(isset($form_data['location_arr'] )){ 
					$location_arr = $form_data['location_arr'];
					$input_array_data= sanitize_text_field($location_arr) ;
					if(is_array($location_arr)){
						$input_array_data= array();
						foreach($location_arr as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-locations');
				}
				if(isset($form_data['new_location'] )){						
					$tag_new= explode(",", $form_data['new_location']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-locations');
					}
				}	
				$default_fields = array();
				$field_set=get_option('listinghub_li_fields' );
				if($field_set!=""){ 
					$default_fields=get_option('listinghub_li_fields' );
					}else{															
					$default_fields['business_type']='Business Type';
					$default_fields['main_products']='Main Products';
					$default_fields['number_of_employees']='Number Of Employees';
					$default_fields['main_markets']='Main Markets';
					$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
				}					
				if(sizeof($default_fields )){			
					foreach( $default_fields as $field_key => $field_value ) { 
						update_post_meta($newpost_id, $field_key, $form_data[$field_key] );							
					}					
				}
				// Check Feature*************	
				$post_author_id= $current_user->ID;
				$author_package_id=get_user_meta($post_author_id, 'listinghub_package_id', true);
				$have_package_feature= get_post_meta($author_package_id,'listinghub_package_feature',true);
				$exprie_date= strtotime (get_user_meta($post_author_id, 'listinghub_exprie_date', true));
				$current_date=time();						
				if($have_package_feature=='yes'){
					if($exprie_date >= $current_date){
						update_post_meta($newpost_id, 'listinghub_featured', 'featured' );	
					}	
					}else{
					update_post_meta($newpost_id, 'listinghub_featured', 'no' );	
				}
				
				// For Tag Save tag_arr
				$tag_all='';
				if(isset($form_data['tag_arr'] )){
					$tag_name= $form_data['tag_arr'] ;	
					$input_array_data= sanitize_text_field($tag_name) ;
					if(is_array($tag_name)){
						$input_array_data= array();
						foreach($tag_name as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					$i=0;$tag_all='';						
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-tag');							
				}
				$tag_all='';
				if(isset($form_data['new_tag'] )){						
					$tag_new= explode(",", $form_data['new_tag']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-tag');											
						$i++;	
					}
				}	
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'latitude', sanitize_text_field($form_data['latitude'])); 
				update_post_meta($newpost_id, 'longitude', sanitize_text_field($form_data['longitude']));					
				update_post_meta($newpost_id, 'city', sanitize_text_field($form_data['city'])); 
				update_post_meta($newpost_id, 'state', sanitize_text_field($form_data['state'])); 
				update_post_meta($newpost_id, 'postcode', sanitize_text_field($form_data['postcode'])); 
				update_post_meta($newpost_id, 'country', sanitize_text_field($form_data['country'])); 
				update_post_meta($newpost_id, 'local-area', sanitize_text_field($form_data['local-area'])); 
				// listing detail*****
								
				if(isset($form_data['dirpro_email_button'])){						
					update_post_meta($newpost_id, 'dirpro_email_button', sanitize_text_field($form_data['dirpro_email_button'])); 
				}
				if(isset($form_data['dirpro_web_button'])){						
					update_post_meta($newpost_id, 'dirpro_web_button', sanitize_text_field($form_data['dirpro_web_button'])); 
				}
				update_post_meta($newpost_id, 'image_gallery_ids', sanitize_text_field($form_data['gallery_image_ids'])); 
				update_post_meta($newpost_id, 'topbanner', sanitize_text_field($form_data['topbanner_image_id'])); 
				update_post_meta($newpost_id, 'listing_contact_source', sanitize_text_field($form_data['contact_source']));  
				update_post_meta($newpost_id, 'external_form_url', sanitize_url($form_data['external_form_url']));  
				if(isset($form_data['feature_image_id'] )){
					$attach_id =sanitize_text_field($form_data['feature_image_id']);
					set_post_thumbnail( $newpost_id, $attach_id );					
				}	
				update_post_meta($newpost_id, 'company_name', sanitize_text_field($form_data['company_name']));
				update_post_meta($newpost_id, 'phone', sanitize_text_field($form_data['phone'])); 
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'contact-email', sanitize_text_field($form_data['contact-email'])); 
				update_post_meta($newpost_id, 'contact_web', sanitize_text_field($form_data['contact_web']));
				update_post_meta($newpost_id, 'vimeo', sanitize_text_field($form_data['vimeo'])); 
				update_post_meta($newpost_id, 'youtube', sanitize_text_field($form_data['youtube']));
				
				include( ep_listinghub_ABSPATH. 'inc/add-listing-notification.php');
				if($form_data['post_status']=='publish'){ 
					include( ep_listinghub_ABSPATH. 'inc/notification.php');
				}
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			// add listing listinghub_save_post_without_user
			public function listinghub_save_post_without_user(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'addlisting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				$allowed_html = wp_kses_allowed_html( 'post' );	
				global $current_user; global $wpdb;	
				parse_str($_POST['form_data'], $form_data);		
				if($form_data['user_id']=='0'){ 					// create new user 
					if($form_data['n_user_email']!='' and $form_data['n_password']!='' ){ 
						$userdata = array();
						$userdata['user_email']=sanitize_email($form_data['n_user_email']);
						$userdata['user_login']='';
						$userdata['user_pass']=sanitize_text_field($form_data['n_password']);
						if ( email_exists($userdata['user_email']) == false ) {						
							$user_id = wp_create_user($userdata['user_email'],$userdata['user_pass'],$userdata['user_email']); 
							
							//wp_clear_auth_cookie();
							//wp_set_current_user ( $user_id);
							//wp_set_auth_cookie  ( $user_id );
							include( ep_listinghub_ABSPATH. 'inc/signup-mail.php');
							}else{
							echo json_encode(array("code" => "error","msg"=>esc_html__( 'Email already exists ', 'listinghub')));
							exit(0);
						}
					}	
				}
				$my_post = array();
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$post_type = $listinghub_directory_url;
				$listinghub_user_can_publish=get_option('listinghub_user_can_publish');	
				if($listinghub_user_can_publish==""){$listinghub_user_can_publish='yes';}	
				$form_data['post_status']='pending';
				if($form_data['post_status']=='publish'){	
					if($listinghub_user_can_publish=="yes"){
						$form_data['post_status']='publish';
						}else{
						$form_data['post_status']='pending';
					}								
				}
				$my_post['post_title'] = sanitize_text_field($form_data['title']);
				$my_post['post_content'] = wp_kses( $form_data['new_post_content'], $allowed_html); 
				$my_post['post_type'] = $post_type;
				$my_post['post_status'] = sanitize_text_field($form_data['post_status']);										
				$newpost_id= wp_insert_post( $my_post );
				update_post_meta($newpost_id, 'listinghub_listing_status', sanitize_text_field($form_data['listing_type'])); 
				// WPML Start******
				if ( function_exists('icl_object_id') ) {
					include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
					$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;
					$query =$wpdb->prepare( "UPDATE {$wpdb->prefix}icl_translations SET element_type='post_%s' WHERE element_id='%s' LIMIT 1",$post_type,$newpost_id );
					$wpdb->query($query);					
				}
				// End WPML**********	
				if(isset($form_data['postcats'] )){ 				
					$category_ids = $form_data['postcats'];
					$input_array_data= sanitize_text_field($category_ids) ;
					if(is_array($category_ids)){
						$input_array_data= array();
						foreach($category_ids as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-category');
				}
				if(isset($form_data['new_category'] )){						
					$tag_new= explode(",", $form_data['new_category']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-category');							
					}
				}	
				// For FAQ Save				
				if(isset($form_data['faq_title'] )){
					$faq_title= $form_data['faq_title']; //this is array data we sanitize later, when it save				
					$faq_description= $form_data['faq_description'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($faq_title[$i]) AND $faq_title[$i]!=''){
							update_post_meta($newpost_id, 'faq_title'.$i, sanitize_text_field($faq_title[$i]));
							update_post_meta($newpost_id, 'faq_description'.$i, sanitize_textarea_field($faq_description[$i]));
						}
					}
				}
				// End FAQ
				// Location
				if(isset($form_data['location_arr'] )){ 
					$location_arr = $form_data['location_arr'];
					$input_array_data= sanitize_text_field($location_arr) ;
					if(is_array($location_arr)){
						$input_array_data= array();
						foreach($location_arr as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-locations');
				}
				if(isset($form_data['new_location'] )){						
					$tag_new= explode(",", $form_data['new_location']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-locations');
					}
				}	
				$default_fields = array();
				$field_set=get_option('listinghub_li_fields' );
				if($field_set!=""){ 
					$default_fields=get_option('listinghub_li_fields' );
					}else{															
					$default_fields['business_type']='Business Type';
					$default_fields['main_products']='Main Products';
					$default_fields['number_of_employees']='Number Of Employees';
					$default_fields['main_markets']='Main Markets';
					$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
				}					
				if(sizeof($default_fields )){			
					foreach( $default_fields as $field_key => $field_value ) {
						if(isset($form_data[$field_key])){
							update_post_meta($newpost_id, $field_key, $form_data[$field_key] );				
						}
					}					
				}
				$post_author_id= $current_user->ID;
				update_post_meta($newpost_id, 'listing_education', wp_kses( $form_data['content_education'], $allowed_html));	
				update_post_meta($newpost_id, 'listing_must_have', wp_kses( $form_data['content_must_have'], $allowed_html));
				// For Tag Save tag_arr
				$tag_all='';
				if(isset($form_data['tag_arr'] )){
					$tag_name= $form_data['tag_arr'] ;							
					$i=0;$tag_all='';	
					$input_array_data= sanitize_text_field($tag_name) ;
					if(is_array($tag_name)){
						$input_array_data= array();
						foreach($tag_name as $one_input_field){
							$input_array_data[]=sanitize_text_field($one_input_field);
						}					
					}
					
					wp_set_object_terms( $newpost_id, $input_array_data, $listinghub_directory_url.'-tag');							
				}
				$tag_all='';
				if(isset($form_data['new_tag'] )){						
					$tag_new= explode(",", $form_data['new_tag']); 			
					foreach($tag_new  as $one_tag){	
						wp_add_object_terms( $newpost_id, sanitize_text_field($one_tag), $listinghub_directory_url.'-tag');											
						$i++;	
					}
				}	
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'latitude', sanitize_text_field($form_data['latitude'])); 
				update_post_meta($newpost_id, 'longitude', sanitize_text_field($form_data['longitude']));					
				update_post_meta($newpost_id, 'city', sanitize_text_field($form_data['city'])); 
				update_post_meta($newpost_id, 'state', sanitize_text_field($form_data['state'])); 
				update_post_meta($newpost_id, 'postcode', sanitize_text_field($form_data['postcode'])); 
				update_post_meta($newpost_id, 'country', sanitize_text_field($form_data['country'])); 
			
				// listing detail*****
				update_post_meta($newpost_id, 'educational_requirements', sanitize_text_field($form_data['educational_requirements'])); 
				update_post_meta($newpost_id, 'listing_type', sanitize_text_field($form_data['listing_type'])); 
				update_post_meta($newpost_id, 'listinghub_listing_level', sanitize_text_field($form_data['listinghub_listing_level'])); 
				update_post_meta($newpost_id, 'listinghub_experience_range', sanitize_text_field($form_data['listinghub_experience_range'])); 
				update_post_meta($newpost_id, 'age_range', sanitize_text_field($form_data['age_range'])); 
				update_post_meta($newpost_id, 'gender', sanitize_text_field($form_data['gender'])); 
				update_post_meta($newpost_id, 'vacancy', sanitize_text_field($form_data['vacancy'])); 
				if($form_data['deadline']==''){ 
					$deadline= date("Y-m-d", strtotime("+1 month"));
					}else{
					$deadline=sanitize_text_field($form_data['deadline']);
				}
				update_post_meta($newpost_id, 'deadline', $deadline);  
				update_post_meta($newpost_id, 'workplace', sanitize_text_field($form_data['workplace']));
				update_post_meta($newpost_id, 'salary', sanitize_text_field($form_data['salary']));
				update_post_meta($newpost_id, 'other_benefits', sanitize_text_field($form_data['other_benefits']));
				if(isset($form_data['dirpro_email_button'])){						
					update_post_meta($newpost_id, 'dirpro_email_button', sanitize_text_field($form_data['dirpro_email_button'])); 
				}
				if(isset($form_data['dirpro_web_button'])){						
					update_post_meta($newpost_id, 'dirpro_web_button', sanitize_text_field($form_data['dirpro_web_button'])); 
				}
				update_post_meta($newpost_id, 'image_gallery_ids', sanitize_text_field($form_data['gallery_image_ids'])); 
				update_post_meta($newpost_id, 'topbanner', sanitize_text_field($form_data['topbanner_image_id'])); 
				update_post_meta($newpost_id, 'listing_contact_source', sanitize_text_field($form_data['contact_source']));  
				update_post_meta($newpost_id, 'external_form_url', sanitize_url($form_data['external_form_url']));  
				if(isset($form_data['feature_image_id'] )){
					$attach_id =sanitize_text_field($form_data['feature_image_id']);
					set_post_thumbnail( $newpost_id, $attach_id );					
				}	
				update_post_meta($newpost_id, 'company_name', sanitize_text_field($form_data['company_name']));
				update_post_meta($newpost_id, 'phone', sanitize_text_field($form_data['phone'])); 
				update_post_meta($newpost_id, 'address', sanitize_text_field($form_data['address'])); 
				update_post_meta($newpost_id, 'contact-email', sanitize_text_field($form_data['contact-email'])); 
				update_post_meta($newpost_id, 'contact_web', sanitize_text_field($form_data['contact_web']));
				update_post_meta($newpost_id, 'vimeo', sanitize_text_field($form_data['vimeo'])); 
				update_post_meta($newpost_id, 'youtube', sanitize_text_field($form_data['youtube'])); 
				
					include( ep_listinghub_ABSPATH. 'inc/add-listing-notification.php');
				
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function eppro_upload_featured_image($thumb_url, $post_id) {
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');
			
				$tmp = download_url($thumb_url);
				if (is_wp_error($tmp)) {
					error_log('Error downloading image: ' . $tmp->get_error_message());
					return false; // Exit the function if download fails
				}
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|webp|WEBP)/', $thumb_url, $matches);
				$file_array = [
					'name' => basename($matches[0]),
					'tmp_name' => $tmp,
				];				
				$thumbid = media_handle_sideload($file_array, $post_id, 'gallery desc');				
				if (is_wp_error($thumbid)) {
					error_log('Error uploading image: ' . $thumbid->get_error_message());
					@unlink($file_array['tmp_name']); // Clean up temporary file
					return false;
				}				
				set_post_thumbnail($post_id, $thumbid);				
				@unlink($file_array['tmp_name']);

				return $thumbid; 
			}

			public function listinghub_finalerp_csv_product_upload(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'csv' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				$csv_file_id=0;$maping='';
				if(isset($_POST['csv_file_id'])){
					$csv_file_id= sanitize_text_field($_POST['csv_file_id']);
				}
				require(ep_listinghub_DIR .'/admin/pages/importer/upload_main_big_csv.php');
				$total_files = get_option( 'finalerp-number-of-files');
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub'), "maping"=>$maping));
				exit(0);
			}
			public function listinghub_save_csv_file_to_database(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'csv' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$csv_file_id=0;
				if(isset($_POST['csv_file_id'])){
					$csv_file_id= sanitize_text_field($_POST['csv_file_id']);
				}	
				$row_start=0;
				if(isset($_POST['row_start'])){
					$row_start= sanitize_text_field($_POST['row_start']);
				}
				require (ep_listinghub_DIR .'/admin/pages/importer/csv_save_database.php');
				echo json_encode(array("code" => $done_status,"msg"=>esc_html__( 'Updated Successfully', 'listinghub'), "row_done"=>$row_done ));
				exit(0);
			}
			public function listinghub_eppro_get_import_status(){
				$eppro_total_row = floatval( get_option( 'eppro_total_row' ));	
				$eppro_current_row = floatval( get_option( 'eppro_current_row' ));		
				$progress =  ((int)$eppro_current_row / (int)$eppro_total_row)*100;
				if($eppro_total_row<=$eppro_current_row){$progress='100';}
				if($progress=='100'){
					echo json_encode(array("code" => "-1","progress"=>(int)$progress, "eppro_total_row"=>$eppro_total_row,"eppro_current_row"=>$eppro_current_row));	
					}else{
					echo json_encode(array("code" => "0","progress"=>(int)$progress, "eppro_total_row"=>$eppro_total_row ,"eppro_current_row"=>$eppro_current_row));
				}		  
				exit(0);
			}
			public function ep_listinghub_pdf_cv(){ 				
				require( ep_listinghub_DIR . '/template/pdf/pdf_post.php');
			}
			public function  listinghub_apply_submit_login(){
				global $current_user;
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'listing' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$my_post = array();	
				$allowed_html = wp_kses_allowed_html( 'post' );	
				$listinghub_directory_url='listing_apply';
				$my_post['post_author'] =$current_user->ID;
				$my_post['post_title'] = $current_user->display_name;
				$my_post['post_name'] = $current_user->display_name;
				$my_post['post_content'] =wp_kses( $form_data['cover-content2'], $allowed_html) ;  
				$my_post['post_type'] 	= $listinghub_directory_url;
				$my_post['post_status']='private';						
				$newpost_id= wp_insert_post( $my_post );
				update_post_meta($newpost_id, 'candidate_name', $current_user->display_name); 
				update_post_meta($newpost_id, 'apply_jod_id',  sanitize_text_field($form_data['dir_id']));				
				update_post_meta($newpost_id, 'email_address', $current_user->user_email); 
				update_post_meta($newpost_id, 'user_id', $current_user->ID); 					
				$old_apply=get_user_meta($current_user->ID,'listing_apply_all', true);
				$new_apply=$old_apply.', '.sanitize_text_field($form_data['dir_id']);						
				update_user_meta($current_user->ID,'listing_apply_all',$new_apply);
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Successfully Sent', 'listinghub')));
				// Send Email
				include( ep_listinghub_ABSPATH. 'inc/apply_submit_login.php');
				exit(0);
			}
	
			
			public function listinghub_author_email_popup(){
				include( ep_listinghub_template. 'private-profile/author_email_popup-file.php');
				exit(0);
			}
			
			public function listinghub_elementor_file(  ) { 
				//Register Custom Elementor Widget					
				if(defined( 'ELEMENTOR_PATH' )){						
					require_once(ep_listinghub_template . 'elementor/custom-elementor-widgets.php' );
				}				
			}
			public function listinghub_cancel_paypal(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $wpdb;
				global $current_user;
				parse_str($_POST['form_data'], $form_data);
				if( ! class_exists('Paypal' ) ) {
					require_once(ep_listinghub_DIR . '/inc/class-paypal.php');
				}
				$post_name='listinghub_paypal_setting';						
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name));
				$paypal_id='0';
				if(isset($row->ID )){
					$paypal_id= $row->ID;
				}
				$paypal_api_currency=get_post_meta($paypal_id, 'listinghub_paypal_api_currency', true);
				$paypal_username=get_post_meta($paypal_id, 'listinghub_paypal_username',true);
				$paypal_api_password=get_post_meta($paypal_id, 'listinghub_paypal_api_password', true);
				$paypal_api_signature=get_post_meta($paypal_id, 'listinghub_paypal_api_signature', true);
				$credentials = array();
				$credentials['USER'] = (isset($paypal_username)) ? $paypal_username : '';
				$credentials['PWD'] = (isset($paypal_api_password)) ? $paypal_api_password : '';
				$credentials['SIGNATURE'] = (isset($paypal_api_signature)) ? $paypal_api_signature : '';
				$paypal_mode=get_post_meta($paypal_id, 'listinghub_paypal_mode', true);
				$currencyCode = $paypal_api_currency;
				$sandbox = ($paypal_mode == 'live') ? '' : 'sandbox.';
				$sandboxBool = (!empty($sandbox)) ? true : false;
				$paypal = new Paypal($credentials,$sandboxBool);
				$oldProfile = get_user_meta($current_user->ID,'iv_paypal_recurring_profile_id',true);
				if (!empty($oldProfile)) {
					$cancelParams = array(
					'PROFILEID' => $oldProfile,
					'ACTION' => 'Cancel'
					);
					$paypal -> request('ManageRecurringPaymentsProfileStatus',$cancelParams);
					update_user_meta($current_user->ID,'iv_paypal_recurring_profile_id','');
					update_user_meta($current_user->ID,'listinghub_iv_cancel_reason', sanitize_text_field($form_data['cancel_text'])); 
					update_user_meta($current_user->ID,'listinghub_payment_status', 'cancel'); 
					echo json_encode(array("code" => "success","msg"=>"Cancel Successfully"));
					exit(0);							
					}else{
					echo json_encode(array("code" => "not","msg"=>esc_html__( 'Unable to Cancel', 'listinghub')));
					exit(0);	
				}
			}
			public function listinghub_woocommerce_form_submit(  ) {
				$iv_gateway = get_option('listinghub_payment_gateway');
				if($iv_gateway=='woocommerce'){ 
					require_once(ep_listinghub_ABSPATH . '/admin/pages/payment-inc/woo-submit.php');
				}	
			}
			public function  listinghub_profile_stripe_upgrade(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				require_once(ep_listinghub_DIR . '/admin/init.php');
				global $wpdb;
				global $current_user;
				parse_str($_POST['form_data'], $form_data);	
				$newpost_id='';
				$post_name='listinghub_stripe_setting';
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name ));
				if(isset($row->ID )){
					$newpost_id= $row->ID;
				}
				$stripe_mode=get_post_meta( $newpost_id,'listinghub_stripe_mode',true);	
				if($stripe_mode=='test'){
					$stripe_api =get_post_meta($newpost_id, 'listinghub_stripe_secret_test',true);	
					}else{
					$stripe_api =get_post_meta($newpost_id, 'listinghub_stripe_live_secret_key',true);	
				}
				\Stripe\Stripe::setApiKey($stripe_api);				
				// For  cancel ----
				$arb_status =	get_user_meta($current_user->ID, 'listinghub_payment_status', true);
				$cust_id = get_user_meta($current_user->ID,'listinghub_stripe_cust_id',true);
				$sub_id = get_user_meta($current_user->ID,'listinghub_stripe_subscrip_id',true);
				if($sub_id!=''){	
					try{
						$iv_cancel_stripe = Stripe_Customer::retrieve(sanitize_text_field($form_data['cust_id']));
						$iv_cancel_stripe->subscriptions->retrieve(sanitize_text_field($form_data['sub_id']))->cancel();
						} catch (Exception $e) {
					}
					update_user_meta($current_user->ID,'listinghub_payment_status', 'cancel'); 
					update_user_meta($current_user->ID,'listinghub_stripe_subscrip_id','');
				}			
				require_once(ep_listinghub_DIR . '/admin/pages/payment-inc/stripe-upgrade.php');
				echo json_encode(array("code" => "success","msg"=>$response));
				exit(0);
			}
			public function listinghub_contact_popup(){
				include( ep_listinghub_template. 'private-profile/contact_popup.php');
				exit(0);
			}
			public function listinghub_listing_contact_popup(){
				include( ep_listinghub_template. 'listing/contact_popup.php');
				exit(0);
			}
			public function listinghub_listing_claim_popup(){
				include( ep_listinghub_template. 'listing/single-template/claim.php');
				exit(0);
			}
			
			public function listinghub_get_categories_caching($id, $post_type){				
				if(metadata_exists('post', $id, 'listinghub-category')) {
					$items = get_post_meta($id,'listinghub-category',true );										
					}else{									
					$items=wp_get_object_terms( $id, $post_type.'-category');
					update_post_meta($id, 'listinghub-category' , $items);
				}					
				return $items;
			}
			public function listinghub_get_categories_mapmarker($id, $post_type){	
				$default_marker =ep_listinghub_URLPATH."/admin/files/css/images/marker-icon.png";
				if(metadata_exists('post', $id, 'listinghub-category')) {
					$items = get_post_meta($id,'listinghub-category',true );
					if(is_array($items)){
					if(isset($items[0]->slug)){										
						foreach($items as $c){
							$map_marker= get_term_meta($c->term_id, 'listinghub_term_mapmarker', true);
							if($map_marker!=''){
								$default_marker =$map_marker;
								break;
							}							
						}
					}
					}
				}			
				return $default_marker;
			}
			public function listinghub_get_location_caching($id, $post_type){				
				if(metadata_exists('post', $id, 'listinghub-locations')) {
					$items = get_post_meta($id,'listinghub-locations',true );										
					}else{									
					$items=wp_get_object_terms( $id, $post_type.'-locations');
					update_post_meta($id, 'listinghub-locations' , $items);
				}					
				return $items;
			}					
			public function listinghub_get_tags_caching($id, $post_type){				
				if(metadata_exists('post', $id, 'listinghub-tags')) {
					$items = get_post_meta($id,'listinghub-tags',true );										
					}else{										
					$items=wp_get_object_terms( $id, $post_type.'-tag');
					update_post_meta($id, 'listinghub-tags' , $items);
				}					
				return $items;
			}
			public function listinghub_listing_default_image() {
				$listinghub_listing_defaultimage=get_option('listinghub_listing_defaultimage');
				if(!empty($listinghub_listing_defaultimage)){
					$default_image_url= wp_get_attachment_image_src($listinghub_listing_defaultimage,'full');		
					if(isset($default_image_url[0])){									
						$default_image_url=$default_image_url[0] ;
					}
					}else{
					$default_image_url=ep_listinghub_URLPATH."/assets/images/default-directory.jpg";
				}
				return $default_image_url;
			}
			public function listinghub_cancel_stripe(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				require_once(ep_listinghub_DIR . '/admin/files/lib/Stripe.php');
				global $wpdb;
				global $current_user;
				parse_str($_POST['form_data'], $form_data);	
				$newpost_id='';
				$post_name='listinghub_stripe_setting';
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name ));
				if(isset($row->ID )){
					$newpost_id= $row->ID;
				}
				$stripe_mode=get_post_meta( $newpost_id,'listinghub_stripe_mode',true);	
				if($stripe_mode=='test'){
					$stripe_api =get_post_meta($newpost_id, 'listinghub_stripe_secret_test',true);	
					}else{
					$stripe_api =get_post_meta($newpost_id, 'listinghub_stripe_live_secret_key',true);	
				}
				Stripe::setApiKey($stripe_api);
				try{
					$iv_cancel_stripe = Stripe_Customer::retrieve(sanitize_text_field($form_data['cust_id']));
					$iv_cancel_stripe->subscriptions->retrieve(sanitize_text_field($form_data['sub_id']))->cancel();
					} catch (Exception $e) {
				}
				update_user_meta($current_user->ID,'listinghub_iv_cancel_reason', sanitize_text_field($form_data['cancel_text'])); 
				update_user_meta($current_user->ID,'listinghub_payment_status', 'cancel'); 
				update_user_meta($current_user->ID,'listinghub_stripe_subscrip_id','');
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Cancel Successfully', 'listinghub')));
				exit(0);
			}
			
			public function listinghub_update_setting_password(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);		
				if(array_key_exists('wp_capabilities',$form_data)){
					wp_die( 'Are you cheating:wp_capabilities?' );		
				}
				global $current_user;										
				if ( wp_check_password( sanitize_text_field($form_data['c_pass']), $current_user->user_pass, $current_user->ID) ){
					if($form_data['r_pass']!=$form_data['n_pass']){
						echo json_encode(array("code" => "not", "msg"=>"New Password & Re Password are not same. "));
						exit(0);
						}else{
						wp_set_password( sanitize_text_field($form_data['n_pass']), $current_user->ID);
						echo json_encode(array("code" => "success","msg"=>"Updated Successfully"));
						exit(0);
					}
					}else{
					echo json_encode(array("code" => "not", "msg"=>esc_html__( 'Current password is wrong.', 'listinghub')));
					exit(0);
				}
			}			
			
			public function listinghub_update_profile_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);		
				if(array_key_exists('wp_capabilities',$form_data)){
					wp_die( 'Are you cheating:wp_capabilities?' );		
				}
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$allowed_html = wp_kses_allowed_html( 'post' );
				global $current_user;
				
				
				// Location
				$all_locations='';
				if(is_array($form_data['location_arr'])){				
				 $all_locations= implode(",",$form_data['location_arr']);
					if(isset($form_data['new_location']) AND $form_data['new_location']!=''){ 
						$all_locations= $all_locations.','.$form_data['new_location'];
					}
				}				
				update_user_meta($current_user->ID, 'all_locations', sanitize_text_field($all_locations)); 
				update_user_meta($current_user->ID, 'new_locations', sanitize_text_field($form_data['new_location']));  
				
				$field_type=array();
				$field_type_opt=  get_option( 'listinghub_field_type' );
				if($field_type_opt!=''){
					$field_type=get_option('listinghub_field_type' );
					}else{
					$field_type['full_name']='text';					 
					$field_type['company_type']='text';
					$field_type['phone']='text';								
					$field_type['address']='text';
					$field_type['city']='text';
					$field_type['postcode']='text';
					$field_type['country']='text';
					$field_type['listing_title']='text';
					$field_type['gender']='radio';
					$field_type['occupation']='text';
					$field_type['description']='textarea';
					$field_type['web_site']='url';					
				}				

				foreach ( $form_data as $field_key => $field_value ) {
					if (strtolower(trim($field_key)) === 'user_email') continue; 
					if(strtolower(trim($field_key))!='wp_capabilities'){						
						if(is_array($field_value)){
							$field_value =implode(",",$field_value);
						}
						if($field_type[$field_key]=='url'){							
							update_user_meta($current_user->ID, sanitize_text_field($field_key), sanitize_url($field_value)); 
						}elseif($field_type[$field_key]=='textarea'){
							update_user_meta($current_user->ID, sanitize_text_field($field_key), sanitize_textarea_field($field_value));  
						}else{
							update_user_meta($current_user->ID, sanitize_text_field($field_key), sanitize_text_field($field_value)); 
						}
					}
				}
				// top banner
				update_user_meta($current_user->ID, 'topbanner', sanitize_text_field($form_data['topbanner'])); 
				
				// For education Save
				// Delete 1st
				$i=0;
				for($i=0;$i<20;$i++){
					delete_user_meta($current_user->ID, 'educationtitle'.$i);
					delete_user_meta($current_user->ID, 'edustartdate'.$i);
					delete_user_meta($current_user->ID, 'eduenddate'.$i);
					delete_user_meta($current_user->ID, 'institute'.$i);
					delete_user_meta($current_user->ID, 'edudescription'.$i);
				}
				// Delete End
				if(isset($form_data['educationtitle'] )){
					$educationtitle= $form_data['educationtitle']; //this is array data we sanitize later, when it save
					$edustartdate= $form_data['edustartdate']; //this is array data we sanitize later, when it save
					$eduenddate= $form_data['eduenddate']; //this is array data we sanitize later, when it save
					$institute= $form_data['institute'];
					$edudescription= $form_data['edudescription'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($educationtitle[$i]) AND $educationtitle[$i]!=''){
							update_user_meta($current_user->ID, 'educationtitle'.$i, sanitize_text_field($educationtitle[$i]));
							update_user_meta($current_user->ID, 'edustartdate'.$i, sanitize_text_field($edustartdate[$i]));
							update_user_meta($current_user->ID, 'eduenddate'.$i, sanitize_text_field($eduenddate[$i]));
							update_user_meta($current_user->ID, 'institute'.$i, sanitize_text_field($institute[$i]));
							update_user_meta($current_user->ID, 'edudescription'.$i, sanitize_textarea_field($edudescription[$i]));
						}
					}
				}
				// End education	
				// For Work Experience Save
				// Delete 1st
				$i=0;
				for($i=0;$i<20;$i++){
					delete_user_meta($current_user->ID, 'experience_title'.$i);
					delete_user_meta($current_user->ID, 'experience_start'.$i);
					delete_user_meta($current_user->ID, 'experience_end'.$i);
					delete_user_meta($current_user->ID, 'experience_company'.$i);
					delete_user_meta($current_user->ID, 'experience_description'.$i);
				}
				// Delete End
				if(isset($form_data['experience_title'] )){
					$experience_title= $form_data['experience_title']; //this is array data we sanitize later, when it save
					$experience_start= $form_data['experience_start']; //this is array data we sanitize later, when it save
					$experience_end= $form_data['experience_end']; //this is array data we sanitize later, when it save
					$experience_company= $form_data['experience_company'];
					$experience_description= $form_data['experience_description'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($experience_title[$i]) AND $experience_title[$i]!=''){
							update_user_meta($current_user->ID, 'experience_title'.$i, sanitize_text_field($experience_title[$i]));
							update_user_meta($current_user->ID, 'experience_start'.$i, sanitize_text_field($experience_start[$i]));
							update_user_meta($current_user->ID, 'experience_end'.$i, sanitize_text_field($experience_end[$i]));
							update_user_meta($current_user->ID, 'experience_company'.$i, sanitize_text_field($experience_company[$i]));
							update_user_meta($current_user->ID, 'experience_description'.$i, sanitize_textarea_field($experience_description[$i]));
						}
					}
				}
				// End Work Experience
				// For Award Save
				// Delete 1st
				$i=0;
				for($i=0;$i<20;$i++){
					delete_user_meta($current_user->ID, 'award_title'.$i);
					delete_user_meta($current_user->ID, 'award_year'.$i);						
					delete_user_meta($current_user->ID, 'award_description'.$i);
				}
				// Delete End
				if(isset($form_data['award_title'] )){
					$award_title= $form_data['award_title']; //this is array data we sanitize later, when it save
					$award_year= $form_data['award_year']; //this is array data we sanitize later, when it save
					$award_description= $form_data['award_description'];
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($award_title[$i]) AND $award_title[$i]!=''){
							update_user_meta($current_user->ID, 'award_title'.$i, sanitize_text_field($award_title[$i]));
							update_user_meta($current_user->ID, 'award_year'.$i, sanitize_text_field($award_year[$i]));
							update_user_meta($current_user->ID, 'award_description'.$i, sanitize_textarea_field($award_description[$i]));
						}
					}
				}
				// End Award
				// Languages
				for($i=0;$i<20;$i++){
					delete_user_meta($current_user->ID, 'language'.$i);
					delete_user_meta($current_user->ID, 'language_level'.$i);
				}
				$language= $form_data['language']; //this is array data we sanitize later, when it save
				$language_level= $form_data['language_level']; //this is array data we sanitize later, when it save
				for($i=0;$i<20;$i++){
					if(isset($language[$i]) AND $language[$i]!=''){							
						update_user_meta($current_user->ID, 'language'.$i, sanitize_text_field($language[$i]));
					}
					if(isset($language_level[$i]) AND $language_level[$i]!=''){			
						update_user_meta($current_user->ID, 'language_level'.$i, sanitize_text_field($language_level[$i]));
					}
				}	
				// professional_skills***
				$specialties='';
				if(isset($form_data['professional_skills'])){
					foreach ($form_data['professional_skills'] as $specialty){
						$specialties= $specialties.','. sanitize_text_field($specialty);
					}
				}
				// For new professional_skill
				$new_professional_skills=$form_data['new_professional_skills'];
				$new_professional_skills_arr= explode(",",$new_professional_skills);
				foreach ($new_professional_skills_arr as $specialty1){
					$specialty1= sanitize_text_field($specialty1);
					wp_create_term( $specialty1,$listinghub_directory_url.'-tag');
					$specialties= $specialties.','. $specialty1;									
				}								
				update_user_meta($current_user->ID, 'professional_skills', $specialties); 
				if(isset($form_data['latitude'])){
					update_user_meta($current_user->ID, 'latitude', sanitize_text_field($form_data['latitude']));
				}
				if(isset($form_data['longitude'])){
					update_user_meta($current_user->ID, 'longitude', sanitize_text_field($form_data['longitude']));
				}

				$current_email = $current_user->user_email;
				$new_email = sanitize_email($form_data['user_email']);

				if ($new_email && $new_email !== $current_email) {
					// Don't change the email yet — store it as "pending"
					update_user_meta($current_user->ID, '_pending_new_email', $new_email);

					// Generate a unique verification token
					$token = wp_generate_password(32, false);
					update_user_meta($current_user->ID, '_email_change_token', $token);

					// Prepare verification link
					$verify_url = add_query_arg(array(
						'lh_action' => 'verify_email_change',
						'user_id' => $current_user->ID,
						'token'   => $token,
					), home_url());

					// Send the email with a custom subject
					$subject = 'Please verify your new email address';  // Custom subject
					$message = "Hi {$current_user->display_name},\n\nPlease confirm your new email address by clicking the link below:\n\n$verify_url\n\nIf you didn't request this, please ignore this email.";

					wp_mail(
						$new_email,
						$subject,  // Use custom subject here
						$message,
						array('Content-Type: text/plain; charset=UTF-8')  // For plain text emails
					);

					// If you want to send an HTML version as well:
					// wp_mail(
					//     $new_email,
					//     $subject,  
					//     $message, 
					//     array('Content-Type: text/html; charset=UTF-8')  // For HTML emails
					// );

					echo json_encode(array("code" => "email_verification_required", "msg" => esc_html__('A verification email has been sent to your new address.', 'listinghub')));
					exit(0);
				}

				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			
			public function listinghub_total_listing_count($userid, $allusers='no' ){
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				if($allusers=='yes' ){
					$args = array(
					'post_type' => $listinghub_directory_url, // enter your custom post type
					'paged' => '1',					
					'post_status' => 'publish',	
					'posts_per_page'=>'99999',  // overrides posts per page in theme settings
					);
					}else{
					$args = array(
					'post_type' => $listinghub_directory_url, // enter your custom post type
					'paged' => '1',
					'author'=>$userid ,
					'post_status' => 'publish',	
					'posts_per_page'=>'99999',  // overrides posts per page in theme settings
					);
				}
				$listing_count = new WP_Query( $args );
				$count = $listing_count->found_posts;
				return $count;
			}
			public function listinghub_total_applications_count($listingid ){ 
				$listinghub_directory_url2='listing_apply';		
				$args_apply ='';
				$args_apply = array(
				'post_type' => $listinghub_directory_url2, 
				'paged' => '1',	
				'post_status'=>'Private',
				'posts_per_page'=>'99999', 
				'meta_query' => array(
				array(
				'key' => 'apply_jod_id',
				'value' => $listingid,
				'compare' => '='
				)
				)					
				);				
				$apply_count = new WP_Query( $args_apply );				
				$count = $apply_count->found_posts;
				return $count;
			}
			public function listinghub_restrict_media_library( $wp_query ) {
				if(!function_exists('wp_get_current_user')) { include(ABSPATH . "wp-includes/pluggable.php"); }
				global $current_user, $pagenow;
				if( is_admin() && !current_user_can('edit_others_posts') ) {
					$wp_query->set( 'author', $current_user->ID );
					add_filter('views_edit-post', 'fix_post_counts');
					add_filter('views_upload', 'fix_media_counts');
				}
			}
			
			public function listinghub_update_profile_pic(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $current_user;
				if(isset($_REQUEST['profile_pic_url_1'])){
					$iv_profile_pic_url=$_REQUEST['profile_pic_url_1'];
					$attachment_thum=$_REQUEST['attachment_thum'];
					}else{
					$iv_profile_pic_url='';
					$attachment_thum='';
				}
				update_user_meta($current_user->ID, 'listinghub_profile_pic_thum', $attachment_thum);					
				update_user_meta($current_user->ID, 'iv_profile_pic_url', $iv_profile_pic_url);
				echo json_encode('success');
				exit(0);
			}
			public function listinghub_paypal_form_submit(  ) {
				require_once(ep_listinghub_DIR . '/admin/pages/payment-inc/paypal-submit.php');
			}	
			public function listinghub_stripe_form_submit(  ) {
				require_once(ep_listinghub_DIR . '/admin/pages/payment-inc/stripe-submit.php');
			}
			
			/***********************************
				* Adds a meta box to the post editing screen
			*/
			public function listinghub_custom_meta_listinghub() {
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				add_meta_box('prfx_meta', esc_html__('Claim Approve ', 'listinghub'), array(&$this, 'listinghub_meta_callback'),$listinghub_directory_url,'side');
				add_meta_box('prfx_meta2', esc_html__('Listing Data  ', 'listinghub'), array(&$this, 'listinghub_meta_callback_full_data'),$listinghub_directory_url,'advanced');
			}
			public function listinghub_check_coupon(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'signup' ) ) {
					echo json_encode(array("msg"=>"Are you cheating:wpnonce?"));						
					exit(0);
				}
				global $wpdb;
				$coupon_code=sanitize_text_field($_REQUEST['coupon_code']);
				$package_id=sanitize_text_field($_REQUEST['package_id']);					
				$package_amount=get_post_meta($package_id, 'listinghub_package_cost',true);
				$api_currency =sanitize_text_field($_REQUEST['api_currency']);
				$post_cont = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' and  post_type='listinghub_coupon'",$coupon_code ));	
				if(sizeof($post_cont)>0 && $package_amount>0){
					$coupon_name = $post_cont->post_title;
					$current_date=$today = date("m/d/Y");
					$start_date=get_post_meta($post_cont->ID, 'listinghub_coupon_start_date', true);
					$end_date=get_post_meta($post_cont->ID, 'listinghub_coupon_end_date', true);
					$coupon_used=get_post_meta($post_cont->ID, 'listinghub_coupon_used', true);
					$coupon_limit=get_post_meta($post_cont->ID, 'listinghub_coupon_limit', true);
					$dis_amount=get_post_meta($post_cont->ID, 'listinghub_coupon_amount', true);							 
					$package_ids =get_post_meta($post_cont->ID, 'listinghub_coupon_pac_id', true);
					$all_pac_arr= explode(",",$package_ids);
					$today_time = strtotime($current_date);
					$start_time = strtotime($start_date);
					$expire_time = strtotime($end_date);
					if(in_array('0', $all_pac_arr)){
						$pac_found=1;
						}else{
						if(in_array($package_id, $all_pac_arr)){
							$pac_found=1;
							}else{
							$pac_found=0;
						}
					}
					$recurring = get_post_meta( $package_id,'listinghub_package_recurring',true); 
					if($today_time >= $start_time && $today_time<=$expire_time && $coupon_used<=$coupon_limit && $pac_found == '1' && $recurring!='on' ){
						$total = $package_amount -$dis_amount;
						$coupon_type= get_post_meta($post_cont->ID, 'listinghub_coupon_type', true);
						if($coupon_type=='percentage'){
							$dis_amount= $dis_amount * $package_amount/100;
							$total = $package_amount -$dis_amount ;
						}
						echo json_encode(array('code' => 'success',
						'dis_amount' => $dis_amount.' '.$api_currency,
						'gtotal' => $total.' '.$api_currency,
						'p_amount' => $package_amount.' '.$api_currency,
						));
						exit(0);
						}else{
						$dis_amount='';
						$total=$package_amount;
						echo json_encode(array('code' => 'not-success-2',
						'dis_amount' => '',
						'gtotal' => $total.' '.$api_currency,
						'p_amount' => $package_amount.' '.$api_currency,
						));
						exit(0);
					}
					}else{
					if($package_amount=="" or $package_amount=="0"){$package_amount='0';}
					$dis_amount='';
					$total=$package_amount;
					echo json_encode(array('code' => 'not-success-1',
					'dis_amount' => '',
					'gtotal' => $total.' '.$api_currency,
					'p_amount' => $package_amount.' '.$api_currency,
					));
					exit(0);
				}
			}
			public function listinghub_check_package_amount(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'signup' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $wpdb;
				$coupon_code=(isset($_REQUEST['coupon_code'])? sanitize_text_field($_REQUEST['coupon_code']):'');
				$package_id=sanitize_text_field($_REQUEST['package_id']);
				if( get_post_meta( $package_id,'listinghub_package_recurring',true) =='on'  ){
					$package_amount=get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);			
					}else{					
					$package_amount=get_post_meta($package_id, 'listinghub_package_cost',true);
				}
				$api_currency =sanitize_text_field($_REQUEST['api_currency']);			
				$iv_gateway = get_option('listinghub_payment_gateway');
				if($iv_gateway=='woocommerce'){
					if ( class_exists( 'WooCommerce' ) ) {	
						$api_currency= get_option( 'woocommerce_currency' );
						$api_currency= get_woocommerce_currency_symbol( $api_currency );
					}
				}		
				$post_cont = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' and  post_type='listinghub_coupon'", $coupon_code));	
				if(isset($post_cont->ID)){
					$coupon_name = $post_cont->post_title;
					$current_date=$today = date("m/d/Y");
					$start_date=get_post_meta($post_cont->ID, 'listinghub_coupon_start_date', true);
					$end_date=get_post_meta($post_cont->ID, 'listinghub_coupon_end_date', true);
					$coupon_used=get_post_meta($post_cont->ID, 'listinghub_coupon_used', true);
					$coupon_limit=get_post_meta($post_cont->ID, 'listinghub_coupon_limit', true);
					$dis_amount=get_post_meta($post_cont->ID, 'listinghub_coupon_amount', true);							 
					$package_ids =get_post_meta($post_cont->ID, 'listinghub_coupon_pac_id', true);
					$all_pac_arr= explode(",",$package_ids);
					$today_time = strtotime($current_date);
					$start_time = strtotime($start_date);
					$expire_time = strtotime($end_date);
					$pac_found= in_array($package_id, $all_pac_arr);							
					if($today_time >= $start_time && $today_time<=$expire_time && $coupon_used<=$coupon_limit && $pac_found=="1"){
						$total = $package_amount -$dis_amount;
						echo json_encode(array('code' => 'success',
						'dis_amount' => $api_currency.' '.$dis_amount,
						'gtotal' => $api_currency.' '.$total,
						'p_amount' => $api_currency.' '.$package_amount,
						));
						exit(0);
						}else{
						$dis_amount='--';
						$total=$package_amount;
						echo json_encode(array('code' => 'not-success',
						'dis_amount' => $api_currency.' '.$dis_amount,
						'gtotal' => $api_currency.' '.$total,
						'p_amount' => $api_currency.' '.$package_amount,
						));
						exit(0);
					}
					}else{
					$dis_amount='--';
					$total=$package_amount;
					echo json_encode(array('code' => 'not-success',
					'dis_amount' => $api_currency.' '.$dis_amount,
					'gtotal' => $api_currency.' '.$total,
					'p_amount' => $api_currency.' '.$package_amount,
					));
					exit(0);
				}
			}
			/**
				* Outputs the content of the meta box
			*/
			public function listinghub_meta_callback($post) {
				wp_nonce_field(basename(__FILE__), 'prfx_nonce');
				require_once ('admin/pages/metabox.php');
			}
			public function listinghub_meta_callback_full_data(){
				require_once ('admin/pages/metabox_full_data.php');
			}
			public function listinghub_color_js(){
				$big_button_color=get_option('epjbdir_big_button_color');	
				if($big_button_color==""){$big_button_color='#2e7ff5';}	
				$small_button_color=get_option('epjbdir_small_button_color');	
				if($small_button_color==""){$small_button_color='#5f9df7';}
				$icon_color=get_option('epjbdir_icon_color');	
				if($icon_color==""){$icon_color='#5b5b5b';}	
				$title_color=get_option('epjbdir_title_color');	
				if($title_color==""){$title_color='#5b5b5b';}
				$button_font_color=get_option('epjbdir_button_font_color');	
				if($button_font_color==""){$button_font_color='#ffffff';}
				$button_small_font_color=get_option('epjbdir_button_small_font_color');	
				if($button_small_font_color==""){$button_small_font_color='#ffffff';}
				$content_font_color=get_option('epjbdir_content_font_color');	
				if($content_font_color==""){$content_font_color='#66789C';}	
				$border_color=get_option('epjbdir_border_color');	
				if($border_color==""){$border_color='#E0E6F7';}	
				wp_enqueue_script('listinghub-dynamic-color', ep_listinghub_URLPATH . 'admin/files/js/dynamic-color.js');
				wp_localize_script('listinghub-dynamic-color', 'listinghub_color', array(
				'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
				'big_button'=>$big_button_color,
				'small_button'=>$small_button_color,
				'button_font'=>$button_font_color,
				'button_small_font'=>$button_small_font_color,
				'title_color'=>$title_color,
				'content_font_color'=>$content_font_color,
				'icon_color'=>$icon_color,
				'max_border_color'=>$border_color,	
				) );	
			}
			public function listinghub_all_functions(){
				include_once('functions/listing-functions.php');
				include_once('functions/open-status-checker.php');				
				include_once('admin/pages/metaboxes/location-meta.php');
				include_once('admin/pages/metaboxes/category-meta.php');
			}
			public function listinghub_meta_save($post_id) { 
				global $wpdb,$post; 
				$newpost_id=$post_id;
				$is_autosave = wp_is_post_autosave($post_id);
				if (isset($_REQUEST['listinghub_approve'])) {
					if($_REQUEST['listinghub_approve']=='yes'){ 
						update_post_meta($post_id, 'listinghub_approve', sanitize_text_field($_REQUEST['listinghub_approve']));
						// Set new user for post							
						$listinghub_author_id= sanitize_text_field($_REQUEST['listinghub_author_id']);
						$sql=$wpdb->prepare("UPDATE  $wpdb->posts SET post_author=%d  WHERE ID=%d",$listinghub_author_id,$post_id );		
						$wpdb->query($sql); 					
					}
				} 
				
				if (isset($_REQUEST['listinghub_featured'])) {							
					update_post_meta($post_id, 'listinghub_featured', sanitize_text_field($_REQUEST['listinghub_featured']));
				}
			
				$opening_day=array();
				
				
				if(isset($_REQUEST['day_name'] )){	
					$day_name= $_REQUEST['day_name'] ;
					$day_value1 = $_REQUEST['day_value1'];
					$day_value2 = $_REQUEST['day_value2'] ;
					$i=0;
					foreach($day_name  as $one_meta){
						if(isset($day_name[$i]) and isset($day_value1[$i]) ){
							if($day_name[$i] !=''){
								$opening_day[sanitize_text_field($day_name[$i])]= array(sanitize_text_field($day_value1[$i])=>sanitize_text_field($day_value2[$i])) ;
							}
						}
						$i++;
					}					
					update_post_meta($post_id, '_opening_time', $opening_day);
				}
				
				if (isset($_REQUEST['listing_data_submit'])) {
					$newpost_id=$post_id;
					// For FAQ Save
					// Delete 1st
					$i=0;
					for($i=0;$i<20;$i++){
						delete_post_meta($newpost_id, 'faq_title'.$i);							
						delete_post_meta($newpost_id, 'faq_description'.$i);
					}
					// Delete End
					if(isset($_REQUEST['faq_title'] )){
						$faq_title= $_REQUEST['faq_title']; //this is array data we sanitize later, when it save				
						$faq_description= $_REQUEST['faq_description'];
						$i=0;
						for($i=0;$i<20;$i++){
							if(isset($faq_title[$i]) AND $faq_title[$i]!=''){
								update_post_meta($newpost_id, 'faq_title'.$i, sanitize_text_field($faq_title[$i]));
								update_post_meta($newpost_id, 'faq_description'.$i, sanitize_textarea_field($faq_description[$i]));
							}
						}
					}
					// End FAQ
					
					$default_fields = array();
					$field_set=get_option('listinghub_li_fields' );
					if($field_set!=""){ 
						$default_fields=get_option('listinghub_li_fields' );
						}else{															
						$default_fields['business_type']='Business Type';
						$default_fields['main_products']='Main Products';
						$default_fields['number_of_employees']='Number Of Employees';
						$default_fields['main_markets']='Main Markets';
						$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';	
					}					
					if(sizeof($default_fields )){			
						foreach( $default_fields as $field_key => $field_value ) { 
							update_post_meta($newpost_id, $field_key, $_REQUEST[$field_key] );							
						}					
					}
					
					update_post_meta($newpost_id, 'address', sanitize_text_field($_REQUEST['address'])); 
					update_post_meta($newpost_id, 'latitude', sanitize_text_field($_REQUEST['latitude'])); 
					update_post_meta($newpost_id, 'longitude', sanitize_text_field($_REQUEST['longitude']));					
					update_post_meta($newpost_id, 'city', sanitize_text_field($_REQUEST['city'])); 
					update_post_meta($newpost_id, 'state', sanitize_text_field($_REQUEST['state'])); 
					update_post_meta($newpost_id, 'postcode', sanitize_text_field($_REQUEST['postcode'])); 
					update_post_meta($newpost_id, 'country', sanitize_text_field($_REQUEST['country'])); 
					update_post_meta($newpost_id, 'local-area', sanitize_text_field($_REQUEST['local-area'])); 
					// Get latlng from address* START********
					// Get latlng from address* ENDDDDDD********	
					// listing detail*****
					
					if(isset($_REQUEST['dirpro_email_button'])){						
						update_post_meta($newpost_id, 'dirpro_email_button', sanitize_text_field($_REQUEST['dirpro_email_button'])); 
					}
					if(isset($_REQUEST['dirpro_web_button'])){						
						update_post_meta($newpost_id, 'dirpro_web_button', sanitize_text_field($_REQUEST['dirpro_web_button'])); 
					}
					update_post_meta($newpost_id, 'image_gallery_ids', sanitize_text_field($_REQUEST['gallery_image_ids'])); 
					update_post_meta($newpost_id, 'topbanner', sanitize_text_field($_REQUEST['topbanner_image_id'])); 
					if(isset($_REQUEST['feature_image_id'] )){
						$attach_id =sanitize_text_field($_REQUEST['feature_image_id']);
						set_post_thumbnail( $newpost_id, $attach_id );					
					}
					update_post_meta($newpost_id, 'external_form_url', sanitize_url($_REQUEST['external_form_url']));  
					update_post_meta($newpost_id, 'listing_contact_source', sanitize_text_field($_REQUEST['contact_source']));  
					update_post_meta($newpost_id, 'company_name', sanitize_text_field($_REQUEST['company_name']));
					update_post_meta($newpost_id, 'phone', sanitize_text_field($_REQUEST['phone'])); 
					update_post_meta($newpost_id, 'address', sanitize_text_field($_REQUEST['address'])); 
					update_post_meta($newpost_id, 'contact-email', sanitize_text_field($_REQUEST['contact-email'])); 
					update_post_meta($newpost_id, 'contact_web', sanitize_text_field($_REQUEST['contact_web']));
					update_post_meta($newpost_id, 'vimeo', sanitize_text_field($_REQUEST['vimeo'])); 
					update_post_meta($newpost_id, 'youtube', sanitize_text_field($_REQUEST['youtube'])); 
					delete_post_meta($newpost_id, 'listinghub-tags');
					delete_post_meta($newpost_id, 'listinghub-category');
					delete_post_meta($newpost_id, 'listinghub-locations');
				}
			}
			/**
				* Checks that the WordPress setup meets the plugin requirements
				* @global string $wp_version
				* @return boolean
			*/
			private function check_requirements() {
				global $wp_version;
				if (!version_compare($wp_version, $this->wp_version, '>=')) {
					add_action('admin_notices', 'eplugins_listinghub::display_req_notice');
					return false;
				}
				return true;
			}
			/**
				* Display the requirement notice
				* @static
			*/
			static function display_req_notice() {
				global $eplugins_listinghub;
				echo '<div id="message" class="error"><p><strong>';
				echo esc_html__('Sorry, BootstrapPress re requires WordPress ' . $eplugins_listinghub->wp_version . ' or higher.
				Please upgrade your WordPress setup', 'listinghub');
				echo '</strong></p></div>';
			}
			private function load_dependencies() {
				// Admin Panel
				if (is_admin()) {						
					require_once ('admin/notifications.php');					
					require_once ('admin/admin.php');					
				}
				// Front-End Site
				if (!is_admin()) {
				}
				require_once('functions/listing-functions.php');
				// Global
			}
			/**
				* Called every time the plug-in is activated.
			*/
			public function activate() {				
				require_once ('install/install.php');
			}
			/**
				* Called when the plug-in is deactivated.
			*/
			public function deactivate() {
				global $wpdb;			
				$page_name='price-table';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."'";
				$wpdb->query($query);
				$page_name='registration';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."'";
				$wpdb->query($query);
				$page_name='my-account';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";
				$wpdb->query($query);
				$page_name='agent-public';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";
				$wpdb->query($query);
				$page_name='login';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."'";				
				$wpdb->query($query);
						
				$page_name='author-directory';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='author-profile';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='all-listings';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='all-listings-no-map';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='all-locations';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='all-categories';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);
				$page_name='search-form';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);				
				$page_name='add-listing';						
				$query = "delete from {$wpdb->prefix}posts where  post_name='".$page_name."' ";				
				$wpdb->query($query);	
			}
			/**
				* Called when the plug-in is uninstalled
			*/
			static function uninstall() {
			}
			/**
				* Register the widgets
			*/
			public function register_widget() {
			}
			/**
				* Internationalization
			*/
			public function i18n() {
				load_plugin_textdomain('listinghub', false, basename(dirname(__FILE__)) . '/languages/' );
			}
			/**
				* Starts the plug-in main functionality
			*/
			
			public function listinghub_price_table_func($atts = '', $content = '') {									
				ob_start();					  //include the specified file
				include( ep_listinghub_template. 'price-table/price-table-1.php');
				$content = ob_get_clean();	
				return $content;
			}
			public function listinghub_form_wizard_func($atts = '') {
				global $current_user;
				$template_path=ep_listinghub_template.'signup/';
				ob_start();	 //include the specified file
				if($current_user->ID==0){
					$signup_access= get_option('users_can_register');	
					if($signup_access=='0'){
						esc_html_e( 'Sorry! You are not allowed for signup.', 'listinghub' );
						}else{
						include( $template_path. 'wizard-style-2.php');
					}						
					}else{						  
					include( ep_listinghub_template. 'private-profile/profile-template-1.php');
				}
				$content = ob_get_clean();	
				return $content;
			}
			public function listinghub_profile_template_func($atts = '') {
				global $current_user;
				ob_start();
				if($current_user->ID==0){
					require_once(ep_listinghub_template. 'private-profile/profile-login.php');
					}else{					  
					include( ep_listinghub_template. 'private-profile/profile-template-1.php');
				}
				$content = ob_get_clean();	
				return $content;
			}
			public function listinghub_reminder_email_cron_func ($atts = ''){
				include( ep_listinghub_ABSPATH. 'inc/reminder-email-cron.php');
			}
			public function listinghub_cron_listing(){
				include( ep_listinghub_ABSPATH. 'inc/all_cron_listing.php');
				exit(0);
			}
			public function listinghub_categories_func($atts = ''){
				ob_start();				
				include( ep_listinghub_template. 'listing/listing_categories.php');
				$content = ob_get_clean();
				return $content;	
			}
			public function listinghub_add_listing_func(){
				ob_start();	
				include( ep_listinghub_template. 'private-profile/add-listing-without-user.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_locations_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/listing-locations.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_search_popup_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/listing_search_popup.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_search_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/listing_search.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_categories_carousel_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/carousel/categories-carousel.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_tags_carousel_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/carousel/tags-carousel.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_locations_carousel_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/carousel/locations-carousel.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_map_func($atts = ''){
				ob_start();	
				include( ep_listinghub_template. 'listing/archive-map.php');
				$content = ob_get_clean();
				return $content;
			}				
			public function listinghub_featured_func($atts = ''){
				ob_start();	
				if(isset($atts['style']) and $atts['style']!="" ){
					$tempale=$atts['style']; 
					}else{
					$tempale=get_option('listinghub_featured'); 
				}
				if($tempale==''){
					$tempale='style-1';
				}						
				//include the specified file
				if($tempale=='style-1'){
					include( ep_listinghub_template. 'listing/listing_featured.php');
				}
				$content = ob_get_clean();
				return $content;	
			}	
			public function listinghub_archive_grid_top_map_func($atts=''){
				ob_start();	
				include( ep_listinghub_template. 'listing/archive-grid-top-map.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_archive_grid_func($atts=''){
				ob_start();	
				include( ep_listinghub_template. 'listing/archive-grid.php');
				$content = ob_get_clean();
				return $content;
			}
			public function listinghub_archive_grid_no_map_func($atts=''){
				ob_start();	
				include( ep_listinghub_template. 'listing/archive-grid-no-map.php');
				$content = ob_get_clean();
				return $content;
			}
			
			public function listinghub_listing_detail_page_func($atts=''){
				ob_start();	
				include( ep_listinghub_template. 'listing/listing_detail_shortcode.php');
				$content = ob_get_clean();
				return $content;				
			}
			public function listinghub_listing_filter_func($atts=''){
				ob_start();	
				include( ep_listinghub_template. 'listing/listing-filter.php');
				$content = ob_get_clean();
				return $content;				
			}
			public function listinghub_author_directory_func($atts = ''){
				global $current_user;	
				ob_start(); //include the specified file					
				include( ep_listinghub_template. 'user-directory/author-directory.php');
				$content = ob_get_clean();
				return $content;	
			}
			
			public function get_unique_location_values( $key = 'keyword', $post_type='' ){
				global $wpdb;
				$post_type=get_option('ep_listinghub_url');
				if($post_type==""){$post_type='listing';}
				$all_data=array();
				// Area**
				$dir_facet_title=get_option('dir_facet_area_title');
				if($dir_facet_title==""){$dir_facet_title= esc_html__('Area','listinghub');}
				$res=array();
				$key = 'area';
				$res = $wpdb->get_col( $wpdb->prepare( "
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type='{$post_type}' AND  pm.meta_key = '%s'						
				", $key) );						
				foreach($res as $row1){							
					$row_data=array();
					if(!empty($row1)){
						$row_data['label']=$row1;
						$row_data['value']=$row1;
						$row_data['category']= $dir_facet_title;
						array_push( $all_data, $row_data );
					}
				}
				// City ***
				$dir_facet_title=get_option('dir_facet_location_title');
				if($dir_facet_title==""){$dir_facet_title= esc_html__('City','listinghub');}
				$res=array();
				$key = 'city';
				$res = $wpdb->get_col( $wpdb->prepare( "
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type='{$post_type}' AND  pm.meta_key = '%s'						
				", $key) );						
				foreach($res as $row1){							
					$row_data=array();
					if(!empty($row1)){
						$row_data['label']=$row1;
						$row_data['value']=$row1;
						$row_data['category']= $dir_facet_title;
						array_push( $all_data, $row_data );
					}	
				}
				// Zipcode ***
				$dir_facet_title=get_option('dir_facet_zipcode_title');
				if($dir_facet_title==""){$dir_facet_title= esc_html__('Zipcode','listinghub');}
				$res=array();
				$key = 'postcode';
				$res = $wpdb->get_col( $wpdb->prepare( "
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type='{$post_type}' AND  pm.meta_key = '%s'						
				", $key) );						
				foreach($res as $row1){							
					$row_data=array();
					if(!empty($row1)){
						$row_data['label']=$row1;
						$row_data['value']=$row1;
						$row_data['category']= $dir_facet_title;
						array_push( $all_data, $row_data );
					}	
				}
				$all_data_json= json_encode($all_data);		
				return $all_data_json;
			}
			public function get_unique_search_values(){						
				global $wpdb;
				$post_type=get_option('ep_listinghub_url');
				if($post_type==""){$post_type='listing';}
				$res=array();
				$all_data=array();						
				$partners = array();
				$partners_obj =  get_terms( $post_type.'-category', array('hide_empty' => true) );
				$dir_facet_title=get_option('dir_facet_cat_title');
				if($dir_facet_title==""){$dir_facet_title= esc_html__('Categories','listinghub');}
				foreach ($partners_obj as $partner) {
					$row_data=array();
					$row_data['label']=$partner->name.'['.$partner->count.']';
					$row_data['value']=$partner->name;
					$row_data['category']= $dir_facet_title;
					array_push( $all_data, $row_data );
				}
				// For tags
				$dir_facet_title=get_option('dir_facet_features_title');
				if($dir_facet_title==""){$dir_facet_title= esc_html__('Features','listinghub');}
				$dir_tags=get_option('epjbdir_tags');	
				if($dir_tags==""){$dir_tags='yes';}	
				if($dir_tags=="yes"){
					$partners = array();
					$partners_obj =  get_terms( $post_type.'-tag', array('hide_empty' => true) );
					foreach ($partners_obj as $partner) {
						$row_data=array();
						$row_data['label']=$partner->name.'['.$partner->count.']';
						$row_data['value']=$partner->name;
						$row_data['category']=$dir_facet_title;
						array_push( $all_data, $row_data );
					}
					}else{
					$args =array();
					$args['hide_empty']=true;
					$tags = get_tags($args );
					foreach ( $tags as $tag ) { 
						$row_data=array();
						$row_data['label']=$tag->name.'['.$tag->count.']';
						$row_data['value']=$tag->name;
						$row_data['category']=$dir_facet_title;
						array_push( $all_data, $row_data );
					}							
				}
				// End Tags	****					
				$args3 = array(
				'post_type' => $post_type, // enter your custom post type						
				'post_status' => 'publish',						
				'posts_per_page'=> -1,  // overrides posts per page in theme settings
				'orderby' => 'title',
				'order' => 'ASC',
				);
				$all_data_json=array();
				$query_auto = new WP_Query( $args3 );
				$posts_auto = $query_auto->posts;						
				foreach($posts_auto as $post_a) {
					$row_data=array();  
					$row_data['label']=$post_a->post_title;
					$row_data['value']=$post_a->post_title;
					$row_data['category']= esc_html__('Title','listinghub');
					array_push( $all_data, $row_data );
				}						
				$all_data_json= json_encode($all_data);	
				return $all_data_json;
			}
			
			public function listinghub_profile_public_func($atts = '') {	
				ob_start();						  //include the specified file
				include( ep_listinghub_template. 'profile-public/profile.php');							
				$content = ob_get_clean();	
				return $content;
			}
			public function listinghub_create_taxonomy_locations(){
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$listinghub_directory_url_name=ucfirst('Locations');
				$labels = array(			
				'all_items'           => esc_html__( 'All Location', 'listinghub' ).$listinghub_directory_url_name,
				'add_new_item'        => esc_html__( 'Add New Location', 'listinghub' ),
				'add_new'             => esc_html__( 'Add Location', 'listinghub' ),
				'new_item'            => esc_html__( 'New Location', 'listinghub' ),
				'edit_item'           => esc_html__( 'Edit Location', 'listinghub' ),
				'update_item'         => esc_html__( 'Update Location', 'listinghub' ),
				'view_item'           => esc_html__( 'View Location', 'listinghub' ),
				'search_items'        => esc_html__( 'Search Location', 'listinghub' ),
				'not_found'           => esc_html__( 'Not found', 'listinghub' ),
				'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'listinghub' ),
				);
				register_taxonomy(
				$listinghub_directory_url.'-locations',
				$listinghub_directory_url,
				array(
				'label' => esc_html__( 'Locations', 'listinghub'),					
				'description'         => esc_html__('Locations' , 'listinghub' ),
				'labels'              => $labels,
				'rewrite' => array( 'slug' => $listinghub_directory_url.'-locations' ),
				'description'         => esc_html__( 'Location', 'listinghub' ),
				'hierarchical' => true,
				'show_in_rest' =>	true,
				)
				);		
			}
			public function listinghub_create_taxonomy_tags(){
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$listinghub_directory_url_name=ucfirst('Tags');
				$labels = array(			
				'all_items'           => esc_html__( 'All Tags', 'listinghub' ).$listinghub_directory_url_name,
				'add_new_item'        => esc_html__( 'Add New Tags', 'listinghub' ),
				'add_new'             => esc_html__( 'Add Tags', 'listinghub' ),
				'new_item'            => esc_html__( 'New Tags', 'listinghub' ),
				'edit_item'           => esc_html__( 'Edit Tags', 'listinghub' ),
				'update_item'         => esc_html__( 'Update Tags', 'listinghub' ),
				'view_item'           => esc_html__( 'View Tags', 'listinghub' ),
				'search_items'        => esc_html__( 'Search Tags', 'listinghub' ),
				'not_found'           => esc_html__( 'Not found', 'listinghub' ),
				'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'listinghub' ),
				);
				register_taxonomy(
				$listinghub_directory_url.'-tag',
				$listinghub_directory_url,
				array(
				'label' => esc_html__( 'Tags', 'listinghub'),					
				'description'         => esc_html__('Tags' , 'listinghub' ),
				'labels'              => $labels,
				'rewrite' => array( 'slug' => $listinghub_directory_url.'-tag' ),					
				'hierarchical' => true,
				'show_in_rest' =>	true,
				)
				);						
			}		
			public function listinghub_save_favorite(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'contact' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'_favorites',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites.', '.get_current_user_id();
				update_post_meta($dir_id,'_favorites',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'_dir_favorites', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);
				$new_favorites2=$old_favorites2.', '.$dir_id;
				update_user_meta(get_current_user_id(),'_dir_favorites',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_applied_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'contact' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'listing_apply_all',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites;
				update_post_meta($dir_id,'listing_apply_all',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'listing_apply_all', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);
				$new_favorites2=$old_favorites2;
				update_user_meta(get_current_user_id(),'listing_apply_all',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_save_un_favorite(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'contact' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'_favorites',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites;
				update_post_meta($dir_id,'_favorites',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'_dir_favorites', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);
				$new_favorites2=$old_favorites2;
				update_user_meta(get_current_user_id(),'_dir_favorites',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_save_notification(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'contact' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);	
				get_current_user_id();
				$notification_value=array();
				$notification= $form_data['notificationone']; //this is array data we sanitize later, when it save
				foreach($notification as $notification_one){
					if( $notification_one!=''){							
						$notification_value[]= sanitize_text_field($notification_one);
					}
				}	
				update_user_meta(get_current_user_id(),'listing_notifications',$notification_value);
				echo json_encode(array("code" => "success","msg"=>"Updated Successfully"));
				exit(0);	
			}
			public function listinghub_candidate_schedule(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);	
				$dir_id=sanitize_text_field($form_data['dir_id']);	
				$already_meeting=get_post_meta($dir_id,'candidate_schedule',true);
				update_post_meta($dir_id,'candidate_schedule','yes');
				update_post_meta($dir_id,'candidate_schedule_time',sanitize_text_field($form_data['meeting_date']));
				update_post_meta($dir_id,'candidate_schedule_note',sanitize_text_field($form_data['message-content']));
				echo json_encode(array("msg" => 'success', 'already_meeting'=>$already_meeting ));
				exit(0);
			}
			public function listinghub_candidate_shortlisted(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}				
				parse_str($_POST['data'], $form_data);	
				$dir_id=sanitize_text_field($form_data['id']);	
				if(isset($form_data['shortlisted'])){
					update_post_meta($dir_id,'listinghub_candidate_shortlisted','no');
					}else{
					update_post_meta($dir_id,'listinghub_candidate_shortlisted','yes');
				}
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_profile_bookmark(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'listinghub_profilebookmark',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites.', '.get_current_user_id();
				update_post_meta($dir_id,'listinghub_profilebookmark',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'listinghub_profilebookmark', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);
				$new_favorites2=$old_favorites2.', '.$dir_id;
				update_user_meta(get_current_user_id(),'listinghub_profilebookmark',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_profile_bookmark_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'listinghub_profilebookmark',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites;
				update_post_meta($dir_id,'listinghub_profilebookmark',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'listinghub_profilebookmark', true);						
				$old_favorites2 = str_replace($dir_id ,'',  $old_favorites2);
				$new_favorites2=$old_favorites2;
				update_user_meta(get_current_user_id(),'listinghub_profilebookmark',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);		
			}
			public function listinghub_employer_bookmark(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'listinghub_authorbookmark',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites.', '.get_current_user_id();
				update_post_meta($dir_id,'listinghub_authorbookmark',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'listinghub_authorbookmark', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);
				$new_favorites2=$old_favorites2.', '.$dir_id;
				update_user_meta(get_current_user_id(),'listinghub_authorbookmark',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);	
			}
			public function listinghub_message_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);
				global $current_user;
				$message_id=sanitize_text_field($form_data['id']);
				$user_to=get_post_meta($message_id,'user_to',true);	
				if($user_to==$current_user->ID){				
					wp_delete_post($message_id);
					delete_post_meta($message_id,true);	
					echo json_encode(array("msg" => 'success'));
					}else{
					echo json_encode(array("msg" => 'Not success'));
				}
				exit(0);		
			}
			public function listinghub_load_categories_fields_wpadmin(){
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				$main_class = new eplugins_listinghub;
				$fields_data='';
				$categories_arr=array();
				$term_id = $_POST['term_id'];
				$post_id= $_POST['post_id'];
				$datatype= $_POST['datatype']; 
				if (!empty($term_id)) {
					if($datatype!='slug'){
						foreach ($term_id as $tid) {
							$category = get_term_by('name', $tid, $listinghub_directory_url.'-category');
							$categories_arr[] = $category->slug;
						}
						}else{
						foreach ($term_id as $tid) {							
							$categories_arr[] = $tid;
						}
					}
					$fields_data=$main_class->listinghub_listing_fields($post_id, $categories_arr );
				}
				echo json_encode(array("msg" => 'success',"field_data"=>$fields_data));
				exit(0);
			}
			public function listinghub_listing_fields($listid, $categories_arr){ 
				$listid=$listid;
				$default_fields = array();			
				$listinghub_fields=  		get_option( 'listinghub_li_fields' );
				$field_type=  get_option( 'listinghub_li_field_type' );
				$field_type_value=  get_option( 'listinghub_li_fieldtype_value' );													
				$listinghub_field_type_cat=  get_option( 'listinghub_field_type_cat' );
				if($listinghub_fields==""){ 									
					$default_fields['business_type']='Business Type';
					$default_fields['main_products']='Main Products';
					$default_fields['number_of_employees']='Number Of Employees';
					$default_fields['main_markets']='Main Markets';
					$default_fields['total_annual_sales_volume']='Total Annual Sales Volume';
					}else{
					$default_fields=$listinghub_fields;				
				}
				$return_value='';
				foreach ( $default_fields as $field_key_pass => $field_value ) { 					
					$intersection='';				
					$field_cat_arr= (isset($listinghub_field_type_cat[$field_key_pass])?$listinghub_field_type_cat[$field_key_pass] : '' );					
					if(is_array($field_cat_arr) AND is_array($categories_arr) ){
						$intersection = array_intersect($categories_arr, $listinghub_field_type_cat[$field_key_pass]);
					}
					if(!empty($intersection)){ 
						$return_value=$return_value.'<div class="col-md-12">';
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='dropdown'){	 								
							$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
							$return_value=$return_value.'<div class="form-group row">
							<label class="control-label col-md-4">'. esc_html($field_value).'</label>
							<div class="col-md-8"><select name="'. esc_html($field_key_pass).'" id="'.esc_attr($field_key_pass).'" class="form-control "  >';				
							foreach($dropdown_value as $one_value){	 
								if(trim($one_value)!=''){
									$return_value=$return_value.'<option '.(trim(get_post_meta($listid,$field_key_pass,true))==trim($one_value)?' selected':'').' value="'. esc_attr(trim($one_value)).'">'. esc_html($one_value).'</option>';
								}
							}	
							$return_value=$return_value.'</select></div></div>';					
						}
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='checkbox'){								
							$dropdown_value= explode(',',$field_type_value[$field_key_pass]);						
							$return_value=$return_value.'<div class="form-group row">
							<label class="control-label col-md-4">'. esc_html($field_value).'</label>
							<div class="col-md-8">
							<div class="" >
							';
							$saved_checkbox_value=get_post_meta($listid,$field_key_pass,true);							
							if(!is_array($saved_checkbox_value)){
								if($saved_checkbox_value!=''){								
									$saved_checkbox_value =	explode(',',get_post_meta($listid,$field_key_pass,true));
								}
							}
							if(empty($saved_checkbox_value)){$saved_checkbox_value=array();}
							foreach($dropdown_value as $one_value){
								if(trim($one_value)!=''){
									$return_value=$return_value.'
									<div class="form-check form-check-inline col-md-12 margin-top10">
									<label class="form-check-label" for="'. esc_attr($one_value).'">
									<input '.( in_array($one_value,$saved_checkbox_value)?' checked':'').' class=" form-check-input" type="checkbox" name="'. esc_attr($field_key_pass).'[]"  id="'. esc_attr($one_value).'" value="'. esc_attr(trim($one_value)).'">
									'. esc_attr($one_value).' </label>
									</div>';
								}
							}	
							$return_value=$return_value.'</div></div></div>';						
						}
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='radio'){	 								
							$dropdown_value= explode(',',$field_type_value[$field_key_pass]);
							$return_value=$return_value.'<div class="form-group row ">
							<label class="control-label col-md-4">'. esc_html($field_value).'</label>
							<div class="col-md-8">
							<div class="" >
							';						
							foreach($dropdown_value as $one_value){	 
								if(trim($one_value)!=''){
									$return_value=$return_value.'
									<div class="form-check form-check-inline col-md-12 margin-top10">
									<label class="form-check-label " for="'. esc_attr($one_value).'">
									<input '.(get_post_meta($listid,$field_key_pass,true)==$one_value?' checked':'').' class="form-check-input" type="radio" name="'. esc_attr($field_key_pass).'"  id="'. esc_attr($one_value).'" value="'. esc_attr(trim($one_value)).'">
									'. esc_attr($one_value).'</label>
									</div>														
									';
								}
							}	
							$return_value=$return_value.'</div></div></div>';					
						}					 
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='textarea'){	
							$return_value=$return_value.'<div class="form-group row">';
							$return_value=$return_value.'<label class="control-label col-md-4">'. esc_html($field_value).'</label>';
							$return_value=$return_value.'<div class="col-md-8"><textarea  placeholder="'.esc_html__('Enter ','ivdirectories').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="col-md-12"  rows="4"/>'.esc_attr(get_post_meta($listid,$field_key_pass,true)).'</textarea></div></div>';
						}
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='datepicker'){	 
							$return_value=$return_value.'<div class="form-group row">';
							$return_value=$return_value.'<label class="control-label col-md-4">'. esc_html($field_value).'</label>';
							$return_value=$return_value.'<div class="col-md-8"><input type="text" placeholder="'.esc_html__('Select ','ivdirectories').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control epinputdate " value="'.esc_attr(get_post_meta($listid,$field_key_pass,true)).'"/></div></div>';
						}
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='text'){	 
							$return_value=$return_value.'<div class="form-group row">';
							$return_value=$return_value.'<label class="control-label col-md-4">'. esc_html($field_value).'</label>';
							$return_value=$return_value.'<div class="col-md-8"><input type="text" placeholder="'.esc_html__('Enter ','ivdirectories').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control " value="'.esc_attr(get_post_meta($listid,$field_key_pass,true)).'"/></div></div>';
						}
						if(isset($field_type[$field_key_pass]) && $field_type[$field_key_pass]=='url'){	 
							$return_value=$return_value.'<div class="form-group row">';
							$return_value=$return_value.'<label class="control-label col-md-4">'. esc_html($field_value).'</label>';
							$return_value=$return_value.'<div class="col-md-8"><input type="text" placeholder="'.esc_html__('Enter ','ivdirectories').esc_attr($field_value).'" name="'.esc_html($field_key_pass).'" id="'. esc_attr($field_key_pass).'"  class="form-control " value="'.esc_url(get_post_meta($listid,$field_key_pass,true)).'"/></div></div>';
						}
						$return_value=$return_value.'</div>';
					}
				} // For main  fields loop 
				return $return_value;
			}
			public function listinghub_employer_bookmark_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);
				$old_favorites= get_post_meta($dir_id,'listinghub_authorbookmark',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites;
				update_post_meta($dir_id,'listinghub_authorbookmark',$new_favorites);
				$old_favorites2=get_user_meta(get_current_user_id(),'listinghub_authorbookmark', true);						
				$old_favorites2 = str_replace($dir_id ,'',  $old_favorites2);
				$new_favorites2=$old_favorites2;
				update_user_meta(get_current_user_id(),'listinghub_authorbookmark',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);		
			}
			public function listinghub_candidate_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $current_user;
				parse_str($_POST['data'], $form_data);	
				$post_id=sanitize_text_field($form_data['id']);				
				$listing_post_id= get_post_meta($post_id,'apply_jod_id',true);
				$post_edit = get_post($listing_post_id);				
				$success='0';
				if($post_edit){
					if($post_edit->post_author==$current_user->ID){
						wp_delete_post($post_id);
						delete_post_meta($post_id,true);
						$success='1';
					}
					if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
						wp_delete_post($post_id);
						delete_post_meta($post_id,true);								
						$success='1';
					}	
				}
				if($success=='1'){
					echo json_encode(array("msg" => 'success'));
					}else{
					echo json_encode(array("msg" => 'not-success'));
				}				
				exit(0);
			}
			public function listinghub_candidate_reject(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				global $current_user;
				parse_str($_POST['data'], $form_data);							
				$post_id=sanitize_text_field($form_data['id']);				
				$listing_post_id= get_post_meta($post_id,'apply_jod_id',true);
				$post_edit = get_post($listing_post_id);				
				$success='0';
				if(isset($form_data['reject'])){
					if($post_edit->post_author==$current_user->ID){ 
						update_post_meta($post_id,'listinghub_candidate_reject','no');		
						$success='1';
					}
					if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){ 
						update_post_meta($post_id,'listinghub_candidate_reject','no');							
						$success='1';
					}	
					}else{
					if($post_edit){
						if($post_edit->post_author==$current_user->ID){ 
							update_post_meta($post_id,'listinghub_candidate_reject','yes');		
							$success='1';
						}
						if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){ 
							update_post_meta($post_id,'listinghub_candidate_reject','yes');							
							$success='1';
						}	
					}
				}
				if($success=='1'){
					echo json_encode(array("msg" => 'success'));
					}else{
					echo json_encode(array("msg" => 'not-success'));
				}		
				exit(0);
			}
			public function listinghub_delete_favorite(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'myaccount' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['data'], $form_data);					
				$dir_id=sanitize_text_field($form_data['id']);						
				$old_favorites= get_post_meta($dir_id,'_favorites',true);
				$old_favorites = str_replace(get_current_user_id(), '',  $old_favorites);
				$new_favorites=$old_favorites;
				update_post_meta($dir_id,'_favorites',$new_favorites);						
				$old_favorites2=get_user_meta(get_current_user_id(),'_dir_favorites', true);						
				$old_favorites2 = str_replace($dir_id ,' ',  $old_favorites2);						
				$new_favorites2=$old_favorites2;
				update_user_meta(get_current_user_id(),'_dir_favorites',$new_favorites2);
				echo json_encode(array("msg" => 'success'));
				exit(0);
			}
			public function listinghub_message_send(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'contact' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				parse_str($_POST['form_data'], $form_data);					
				// Create new message post
				$allowed_html = wp_kses_allowed_html( 'post' );					
				if(isset($form_data['dir_id'])){
					if($form_data['dir_id']>0){
						$dir_id=sanitize_text_field($form_data['dir_id']);
						$dir_detail= get_post($dir_id); 
						$dir_title= '<a href="'.get_permalink($dir_id).'">'.$dir_detail->post_title.'</a>';
						$user_id=$dir_detail->post_author;
						$user_info = get_userdata( $user_id);
						$client_email_address =$user_info->user_email;
						$userid_to=$user_id;
					}
				}
				if(isset($form_data['user_id'])){
					if($form_data['user_id']!=''){
						$dir_title= '';
						$user_info = get_userdata(sanitize_text_field($form_data['user_id']));
						$client_email_address =$user_info->user_email;
						$userid_to=sanitize_text_field($form_data['user_id']);
					}
				}
				if(isset($form_data['name'])){
					$new_nessage= $form_data['name'];
				}else{
					$new_nessage= esc_html__( 'New Message', 'listinghub' );
				}
				$my_post=array();
				$subject=$new_nessage;
				if(isset($form_data['subject'])){
					$subject=sanitize_text_field($form_data['subject']);
				} 
				$my_post['post_title'] =$subject;
				$my_post['post_content'] = wp_kses( $form_data['message-content'], $allowed_html); 
				$my_post['post_type'] = 'listinghub_message';
				$my_post['post_status']='private';												
				$newpost_id= wp_insert_post( $my_post );
				Update_post_meta($newpost_id,'user_to', $userid_to );
				Update_post_meta($newpost_id,'dir_url', $dir_title );				
				Update_post_meta($newpost_id,'from_email',sanitize_email($form_data['email_address']) );
				if(isset($form_data['name'])){
					Update_post_meta($newpost_id,'from_name', sanitize_text_field($form_data['name']) );
				}
				Update_post_meta($newpost_id,'from_phone', sanitize_text_field($form_data['visitorphone']) );
				include( ep_listinghub_ABSPATH. 'inc/message-mail.php');	
				echo json_encode(array("msg" => esc_html__( 'Message Sent', 'listinghub' )));
				exit(0);
			}
			public function listinghub_claim_send(){				
				parse_str($_POST['form_data'], $form_data);					
				include( ep_listinghub_ABSPATH. 'inc/claim-mail.php');	
				echo json_encode(array("msg" => esc_html__( 'Message Sent', 'listinghub' )));
				exit(0);
			}
			public function check_listing_expire_date($listin_id, $owner_id,$listinghub_directory_url){ 
				$listing_hide=get_option('listinghub_listing_hide_opt');	
				if($listing_hide==""){$listing_hide='package';}			
				if($listing_hide=='package'){
					$exp_date= get_user_meta($owner_id, 'listinghub_exprie_date', true);
					if($exp_date!=''){
						$package_id=get_user_meta($owner_id,'listinghub_package_id',true);
						$dir_hide= get_post_meta($package_id, 'listinghub_package_hide_exp', true);
						if($dir_hide=='yes'){
							if(strtotime($exp_date) < time()){
								$dir_post = array();
								$dir_post['ID'] = $listin_id;
								$dir_post['post_status'] = 'draft';	
								$dir_post['post_type'] = $listinghub_directory_url;	
								wp_update_post( $dir_post );
							}
						}
						$have_package_feature= get_post_meta($package_id,'listinghub_package_feature',true);										
						if($have_package_feature=='yes'){
							if(strtotime($exp_date) < time()){
								update_post_meta($listin_id, 'listinghub_featured', 'no' );
							}	
						}
					}
				}
				if($listing_hide=='deadline'){
					$deadline= get_post_meta($listin_id, 'deadline', true);		
					$current_time= strtotime(date("Y-m-d"));							
					if(strtotime($deadline) < $current_time){ 
						$dir_post = array();
						$dir_post['ID'] = $listin_id;
						$dir_post['post_status'] = 'draft';	
						$dir_post['post_type'] = $listinghub_directory_url;	
						wp_update_post( $dir_post );
						$have_package_feature= get_post_meta($package_id,'listinghub_package_feature',true);
						if($have_package_feature=='yes'){
							if(strtotime($exp_date) < time()){
								update_post_meta($listin_id, 'listinghub_featured', 'no' );
							}	
						}						
					}
				}
			}
			public function paging() {
				global $wp_query;
			} 
		}
	}
	if(!class_exists('WP_GeoQuery'))
	{
		/**
			* Extends WP_Query to do geographic searches
		*/
		class WP_GeoQuery extends WP_Query
		{
			private $_search_latitude = NULL;
			private $_search_longitude = NULL;
			private $_search_distance = NULL;
			private $_search_postcats = NULL;
			/**
				* Constructor - adds necessary filters to extend Query hooks
			*/
			public function __construct($args = array())
			{
				$listinghub_directory_url=get_option('ep_listinghub_url');
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				// Extract Latitude
				if(!empty($args['lat']))
				{
					$this->_search_latitude = $args['lat'];
				}
				// Extract Longitude
				if(!empty($args['lng']))
				{
					$this->_search_longitude = $args['lng'];
				}
				if(!empty($args['distance']))
				{
					$this->_search_distance = (int)$args['distance'];
				}
				if(!empty($args[$listinghub_directory_url.'-category']))
				{
					$this->_search_postcats= $args[$listinghub_directory_url.'-category'];
				}
				if(!empty($args[$listinghub_directory_url.'-tag']))
				{
					$this->_search_posttags= $args[$listinghub_directory_url.'-tag'];
				}
				if(!empty($args[$listinghub_directory_url.'-locations']))
				{
					$this->_search_postlocations= $args[$listinghub_directory_url.'-locations'];
				}
				// unset lat/lng
				unset($args['lat'], $args['lng'],$args['distance']);
				add_filter('posts_fields', array($this, 'listinghub_posts_fields'), 10, 2);
				add_filter('posts_join', array($this, 'listinghub_posts_join'), 10, 2);
				add_filter('posts_where', array($this, 'listinghub_posts_where'), 10, 2);
				add_filter('posts_groupby', array($this, 'listinghub_posts_groupby'), 10, 2);
				add_filter('posts_orderby', array($this, 'listinghub_posts_orderby'), 10, 2);
				parent::query($args);
				remove_filter('posts_fields', array($this, 'listinghub_posts_fields'));
				remove_filter('posts_join', array($this, 'listinghub_posts_join'));
				remove_filter('posts_where', array($this, 'listinghub_posts_where'));
				remove_filter('posts_groupby', array($this, 'listinghub_posts_groupby'));
				remove_filter('posts_orderby', array($this, 'listinghub_posts_orderby'));
			} // END public function __construct($args = array())
			/**
				* Selects the distance from a haversine formula
			*/
			public function listinghub_posts_groupby($where) {
				global $wpdb;
				if($this->_search_longitude!=""){
					if($this->_search_postcats!=""){
						$where .= $wpdb->prepare(" HAVING distance < %d ", $this->_search_distance);
						}else{
						$where = $wpdb->prepare("{$wpdb->posts}.ID  HAVING distance < %d ", $this->_search_distance);
					}
					if($this->_search_posttags!=""){
						$where .= $wpdb->prepare(" HAVING distance < %d ", $this->_search_distance);
						}else{
						$where = $wpdb->prepare("{$wpdb->posts}.ID  HAVING distance < %d ", $this->_search_distance);
					}
					if($this->_search_postlocations!=""){
						$where .= $wpdb->prepare(" HAVING distance < %d ", $this->_search_distance);
						}else{
						$where = $wpdb->prepare("{$wpdb->posts}.ID  HAVING distance < %d ", $this->_search_distance);
					}
				}
				if($this->_search_postcats!=""){
				}
				return $where;
			}
			public function listinghub_posts_fields($fields)
			{
				global $wpdb;
				if(!empty($this->_search_latitude) && !empty($this->_search_longitude))
				{
					$dir_search_redius=get_option('epjbdir_map_radius');
					$for_option_redius='6387.7';
					if($dir_search_redius=="Mile"){$for_option_redius='3959';}else{$for_option_redius='6387.7'; }
					$fields .= sprintf(", ( ".$for_option_redius."* acos(
					cos( radians(%s) ) *
					cos( radians( latitude.meta_value ) ) *
					cos( radians( longitude.meta_value ) - radians(%s) ) +
					sin( radians(%s) ) *
					sin( radians( latitude.meta_value ) )
					) ) AS distance ", $this->_search_latitude, $this->_search_longitude, $this->_search_latitude);
					$fields .= ", latitude.meta_value AS latitude ";
					$fields .= ", longitude.meta_value AS longitude ";
				}
				return $fields;
			} // END public function posts_join($join, $query)
			/**
				* Makes joins as necessary in order to select lat/long metadata
			*/
			public function listinghub_posts_join($join, $query)
			{
				global $wpdb;
				if(!empty($this->_search_latitude) && !empty($this->_search_longitude)){
					$join .= " INNER JOIN {$wpdb->postmeta} AS latitude ON {$wpdb->posts}.ID = latitude.post_id ";
					$join .= " INNER JOIN {$wpdb->postmeta} AS longitude ON {$wpdb->posts}.ID = longitude.post_id ";
				}
				return $join;
			} // END public function posts_join($join, $query)
			/**
				* Adds where clauses to compliment joins
			*/
			public function listinghub_posts_where($where)
			{
				if(!empty($this->_search_latitude) && !empty($this->_search_longitude)){
					$where .= ' AND latitude.meta_key="latitude" ';
					$where .= ' AND longitude.meta_key="longitude" ';
				}
				return $where;
			} // END public function posts_where($where)
			/**
				* Adds where clauses to compliment joins
			*/
			public function listinghub_posts_orderby($orderby)
			{
				if(!empty($this->_search_latitude) && !empty($this->_search_distance))
				{
					$orderby = " distance ASC, " . $orderby;
				}
				return $orderby;
			} // END public function posts_orderby($orderby)
		}
	}
	/*
		* Creates a new instance of the BoilerPlate Class
	*/
	function listinghubBootstrap() {
		return eplugins_listinghub::instance();
	}

listinghubBootstrap(); 



//Plugin Modify

	// PHP: In functions.php or a custom plugin

	add_action('wp_ajax_search_listing_titles', 'search_listing_titles_callback');
add_action('wp_ajax_nopriv_search_listing_titles', 'search_listing_titles_callback');

function search_listing_titles_callback() {
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');

    if (empty($keyword)) {
        wp_send_json([]);
    }

    $post_type = get_option('ep_listinghub_url', 'listing');

    // Construct meta query to search in postcode, city, and address
    $meta_query = [
        'relation' => 'OR', // Match any of these conditions
        [
            'key'     => 'postcode', // Assuming 'postcode' is the meta key for postcode
            'value'   => $keyword,
            'compare' => 'LIKE', // Use LIKE for partial matching
        ],
        [
            'key'     => 'city', // Assuming 'city' is the meta key for city
            'value'   => $keyword,
            'compare' => 'LIKE', // Use LIKE for partial matching
        ],
        [
            'key'     => 'address', // Assuming 'address' is the meta key for address
            'value'   => $keyword,
            'compare' => 'LIKE', // Use LIKE for partial matching
        ],
    ];

    // Construct taxonomy query to search for 'student-let' category
    $tax_query = [
        [
            'taxonomy' => 'listing-category',  // Assuming 'category' is the taxonomy
            'field'    => 'slug',      // We are using the term slug
            'terms'    => 'student-let', // The term slug for the category you want to search
            'operator' => 'IN',        // 'IN' is the operator to match the term
        ],
    ];

    // Add title search to the main query (this will search the title and meta fields)
    $args = [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'meta_query'     => $meta_query, // Include meta query for postcode, city, and address
        'tax_query'      => $tax_query,  // Include taxonomy query to check for 'student-let'
        'fields'         => 'ids', // Fetch only post IDs to optimize
    ];

    $query = new WP_Query($args);

    $results = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            // Fetch all post meta
            $city = get_post_meta($post_id, 'city', true);
            $postcode = get_post_meta($post_id, 'postcode', true);
            // Structure the post data with title and all meta fields
            $post_data = [
                'title'    => get_the_title($post_id),
                'permalink'=> get_permalink($post_id),
                'city'     => $city,
				'postcode'     => $postcode,
            ];

            $results[] = $post_data;
        }
    }

    wp_send_json($results); // Send the response as JSON
    wp_die(); // Always call this after wp_send_json
}



add_action('wp_ajax_search_listing_titles_private_let', 'search_listing_titles_private_let_callback');
add_action('wp_ajax_nopriv_search_listing_titles_private_let', 'search_listing_titles_private_let_callback');

function search_listing_titles_private_let_callback() {
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');

    if (empty($keyword)) {
        wp_send_json([]);
    }

    // Define post type
    $post_type = get_option('ep_listinghub_url', 'listing');

    // Construct meta query to search in postcode, city, and address
    $meta_query = [
        'relation' => 'OR',
        [
            'key'     => 'postcode', // Assuming 'postcode' is the meta key for postcode
            'value'   => $keyword,
            'compare' => 'LIKE',
        ],
        [
            'key'     => 'city', // Assuming 'city' is the meta key for city
            'value'   => $keyword,
            'compare' => 'LIKE',
        ],
        [
            'key'     => 'address', // Assuming 'address' is the meta key for address
            'value'   => $keyword,
            'compare' => 'LIKE',
        ],
    ];

    // Taxonomy query for 'student-let' category
    $tax_query = [
        [
            'taxonomy' => 'listing-category',
            'field'    => 'slug',
            'terms'    => 'private-let',
            'operator' => 'IN',
        ],
    ];

    // Construct query for 'student-let' category
    $args = [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'meta_query'     => $meta_query,
        'tax_query'      => $tax_query,
        'fields'         => 'ids',
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $city = get_post_meta($post_id, 'city', true);
			$postcode = get_post_meta($post_id, 'postcode', true);

            $post_data = [
                'title'    => get_the_title($post_id),
                'permalink'=> get_permalink($post_id),
                'city'     => $city,
				'postcode'     => $postcode,
            ];
            $results[] = $post_data;
        }
    }

    wp_send_json($results); // Send the response as JSON
    wp_die();
}




	
    //Plugin Modify End


?>
 
    
    