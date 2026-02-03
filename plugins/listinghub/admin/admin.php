

<?php
	if (!defined('ABSPATH')) {
		exit;
	}
	/**
		* The Admin Panel and related tasks are handled in this file.
	*/
	if (!class_exists('eplugins_listinghub_Admin')) {
		class eplugins_listinghub_Admin {
			static $pages = array();
			public function __construct() {
				add_action('admin_menu', array($this, 'listinghub_admin_menu'));
				add_action('admin_print_scripts', array($this, 'listinghub_load_scripts'));
				add_action('admin_print_styles', array($this, 'listinghub_load_styles'));							
				add_action('wp_ajax_listinghub_save_package', array($this, 'listinghub_save_package'));
				add_action('wp_ajax_listinghub_update_package', array($this, 'listinghub_update_package'));
				add_action('wp_ajax_listinghub_update_paypal_settings', array($this, 'listinghub_update_paypal_settings'));
				add_action('wp_ajax_listinghub_update_stripe_settings', array($this, 'listinghub_update_stripe_settings'));			
				add_action('wp_ajax_listinghub_create_coupon', array($this, 'listinghub_create_coupon'));
				add_action('wp_ajax_listinghub_update_coupon', array($this, 'listinghub_update_coupon'));	
				add_action('wp_ajax_listinghub_update_payment_setting', array($this, 'listinghub_update_payment_setting'));
				add_action('wp_ajax_listinghub_update_page_setting', array($this, 'listinghub_update_page_setting'));
				add_action('wp_ajax_listinghub_update_email_setting', array($this, 'listinghub_update_email_setting'));
				add_action('wp_ajax_listinghub_update_mailchamp_setting', array($this, 'listinghub_update_mailchamp_setting'));
				add_action('wp_ajax_listinghub_add_home_page', array($this, 'listinghub_add_home_page'));
				add_action('wp_ajax_listinghub_update_package_status', array($this, 'listinghub_update_package_status'));
				add_action('wp_ajax_listinghub_gateway_settings_update', array($this, 'listinghub_gateway_settings_update'));
				add_action('wp_ajax_listinghub_update_account_setting', array($this, 'listinghub_update_account_setting'));			
				add_action('wp_ajax_listinghub_update_protected_setting', array($this, 'listinghub_update_protected_setting'));
				add_action('wp_ajax_listinghub_import_data', array($this, 'listinghub_import_data'));
				add_action('wp_ajax_listinghub_update_user_settings', array($this, 'listinghub_update_user_settings'));			
				add_action('wp_ajax_listinghub_update_profile_fields', array($this, 'listinghub_update_profile_fields'));
				add_action('wp_ajax_listinghub_update_dir_fields', array($this, 'listinghub_update_dir_fields'));
				add_action('wp_ajax_listinghub_update_profile_signup_fields', array($this, 'listinghub_update_profile_signup_fields'));
				add_action('wp_ajax_listinghub_update_dir_setting', array($this, 'listinghub_update_dir_setting'));	
				add_action('wp_ajax_listinghub_update_search_fields', array($this, 'listinghub_update_search_fields'));				
				add_action('wp_ajax_listinghub_update_archive_fields', array($this, 'listinghub_update_archive_fields'));
				add_action('wp_ajax_listinghub_update_single_fields', array($this, 'listinghub_update_single_fields'));				
				add_action('wp_ajax_listinghub_create_search_shortcode', array($this, 'listinghub_create_search_shortcode'));
				add_action('wp_ajax_listinghub_search_shortcodes_saved_delete', array($this, 'listinghub_search_shortcodes_saved_delete'));			
				add_action('wp_ajax_listinghub_update_map_settings', array($this, 'listinghub_update_map_settings'));
				add_action('wp_ajax_listinghub_update_color_settings', array($this, 'listinghub_update_color_settings'));
				add_action('wp_ajax_listinghub_update_myaccount_menu', array($this, 'listinghub_update_myaccount_menu'));
				add_action('wp_ajax_listinghub_update_openai_settings', array($this, 'listinghub_update_openai_settings'));
				add_action( 'init', array($this, 'listinghub_gutenberg_widgets') );
				add_action( 'init', array($this, 'listinghub_payment_post_type') );
				add_filter( 'manage_edit-iv_payment_columns', array($this, 'listinghub_set_custom_edit_iv_payment_columns')  );
				add_action( 'manage_iv_payment_posts_custom_column' ,  array($this, 'listinghub_custom_iv_payment_column')  , 10, 2 );
				wp_admin_notifications::load();
			}
			// Hook into the 'init' action
			public function listinghub_payment_post_type() {
				$args = array(
				'description' => 'listinghub Payment Post Type',
				'show_ui' => true,   
				'exclude_from_search' => true,
				'labels' => array(
				'name'=> 'Payment History',
				'singular_name' => 'iv_payment',							 
				'edit' => 'Edit Payment History',
				'edit_item' => 'Edit Payment History',							
				'view' => 'View Payment History',
				'view_item' => 'View Payment History',
				'search_items' => 'Search ',
				'not_found' => 'No  Found',
				'not_found_in_trash' => 'No Found in Trash',
				),
				'public' => true,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'show_ui' => true,
				'show_in_menu' => 'flase',
				'hiearchical' => false,
				'capability_type' => 'post',
				'hierarchical' => false,
				'rewrite' => true,
				'supports' => array('title', 'editor', 'thumbnail','excerpt','custom-fields'),							
				);
				register_post_type( 'iv_payment', $args );
			}
			public function listinghub_gutenberg_widgets(){
				require_once ('pages/tinymce_shortcode_button.php');
			}
			public function listinghub_set_custom_edit_iv_payment_columns($columns) {
				$columns['title']=esc_html__( 'Package Name', 'listinghub'); 
				$columns['User'] = esc_html__( 'User Name', 'listinghub'); 
				$columns['Member'] = esc_html__( 'User ID', 'listinghub'); 			
				$columns['Amount'] = esc_html__( 'Amount', 'listinghub'); 
				return $columns;
			}
			public function listinghub_custom_iv_payment_column( $column ) {
				global $post;
				switch ( $column ) {
					case 'User' :							
					if(isset($post->post_author) ){
						$user_info = get_userdata( $post->post_author);
						if($user_info!='' ){
							echo  esc_html($user_info->user_login);
						}
					}
					break; 
					case 'Member' :
					echo esc_html($post->post_author); 
					break;
					case 'Amount' :
					echo esc_html($post->post_content); 
					break;
				}
			}
			/**
				* Menus in the wp-admin sidebar
			*/
			public function listinghub_admin_menu() {
				$listinghub_directory_url=get_option('ep_listinghub_url');					
				if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
				add_menu_page('listinghub', 'Listing Hub Settings', 'manage_options', 'listinghub', array($this, 'listinghub_menu_hook'),'dashicons-universal-access-alt',9);
				self::$pages['listinghub-settings'] = add_submenu_page('listinghub', 'listinghub Settings', 'Settings', 'manage_options', 'listinghub-settings', array($this, 'listinghub_menu_hook'));				
				add_submenu_page('listinghub', 'listinghub', 'Categories', 'manage_options',  'edit-tags.php?taxonomy='.$listinghub_directory_url.'-category&post_type='.$listinghub_directory_url,'',1);
				add_submenu_page('listinghub', 'listinghub', 'Tags', 'manage_options', 'edit-tags.php?taxonomy='.$listinghub_directory_url.'-tag&post_type='.$listinghub_directory_url,'',2);
				add_submenu_page('listinghub', 'listinghub', 'Locations', 'manage_options', 'edit-tags.php?taxonomy='.$listinghub_directory_url.'-locations&post_type='.$listinghub_directory_url,'',3);
				self::$pages['listinghub-package-create'] = add_submenu_page('', 'listinghub package', '', 'manage_options', 'listinghub-package-create', array($this, 'listinghub_package_create_page'));
				self::$pages['listinghub-package-update'] = add_submenu_page('', 'listinghub package', '', 'manage_options', 'listinghub-package-update', array($this, 'listinghub_package_update_page'));
				self::$pages['listinghub-coupon-create'] = add_submenu_page('', ' listinghub coupon', '', 'manage_options', 'listinghub-coupon-create', array($this, 'listinghub_coupon_create_page'));
				self::$pages['listinghub-coupon-update'] = add_submenu_page('', 'listinghub coupon', '', 'manage_options', 'listinghub-coupon-update', array($this, 'listinghub_coupon_update_page'));
				self::$pages['listinghub-payment-paypal'] = add_submenu_page('', 'listinghub Payment setting', '', 'manage_options', 'listinghub-payment-paypal', array($this, 'listinghub_paypal_update_page'));
				self::$pages['listinghub-payment-stripe'] = add_submenu_page('', 'listinghub Payment setting', '', 'manage_options', 'listinghub-payment-stripe', array($this, 'listinghub_stripe_update_page'));
				self::$pages['listinghub-user_update'] = add_submenu_page('', 'listinghub user_update', '', 'manage_options', 'listinghub-user_update', array($this, 'listinghub_user_update_page'));
			}
			/**
				* Menu Page Router
			*/
			public function listinghub_menu_hook() {
				$screen = get_current_screen();
				switch ($screen->id) {
					default:
					require_once ('pages/settings.php');
					break;
					case self::$pages['listinghub-settings']:
					require_once ('pages/settings.php');
					break;
				}
			}
			public function listinghub_profile_fields_setting (){
				require_once ('pages/registration-fields.php');
			}
			public function listinghub_coupon_create_page(){
				require_once ('pages/coupon_create.php');
			}
			public function listinghub_coupon_update_page(){
				require_once ('pages/coupon_update.php');
			}
			public function listinghub_package_create_page(){
				require_once ('pages/package_create.php');
			}
			public function listinghub_package_update_page(){
				require_once ('pages/package_update.php');
			}
			public function listinghub_paypal_update_page(){
				require_once ('pages/paypal_update.php');
			}
			public function listinghub_stripe_update_page(){
				require_once ('pages/stripe_update.php');
			}
			public function listinghub_user_update_page(){
				require_once ('pages/user_update.php');
			}
			/**
				* Page based Script Loader
			*/
			public function listinghub_load_scripts() { 
				$screen = get_current_screen();
				$screen = get_current_screen();
				if (in_array($screen->id, array_values(self::$pages))) {
					$currencyCode= 'USD';
					wp_enqueue_script("jquery");	
					wp_enqueue_script('jquery-ui-core');
					wp_enqueue_script('jquery-ui-datepicker');
					wp_enqueue_script('jquery-ui-sortable');
					wp_enqueue_media();
					wp_enqueue_script('bootstrap.min', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js');					
					wp_enqueue_script('listinghub-script-dashboardadmin', ep_listinghub_URLPATH . 'admin/files/js/dashboard-admin.js');
					wp_localize_script('listinghub-script-dashboardadmin', 'admindata', array(
					'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
					'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
					'wp_iv_directories_URLPATH'		=> ep_listinghub_URLPATH,
					'ep_listinghub_ADMINPATH' => ep_listinghub_ADMINPATH,
					'current_user_id'	=>get_current_user_id(),	
					'SetImage'		=>esc_html__('Set Image','listinghub'),
					'GalleryImages'=>esc_html__('Gallery Images','listinghub'),	
					'cancel-message' => esc_html__('Are you sure to cancel this Membership','listinghub'),
					'currencyCode'=>  $currencyCode,
					'dirwpnonce'=> wp_create_nonce("myaccount"),
					'settings'=> wp_create_nonce("settings"), 
					'cityimage'=> wp_create_nonce("city-image"),
					'packagenonce'=> wp_create_nonce("package"),
					'catimage'=> wp_create_nonce("cat-image"),							
					'signup'=> wp_create_nonce("signup"),
					'contact'=> wp_create_nonce("contact"),
					'coupon'=> wp_create_nonce("coupon"),
					'fields'=> wp_create_nonce("fields"),
					'dirsetting'=> wp_create_nonce("dir-setting"),
					'mymenu'=> wp_create_nonce("my-menu"),
					'paymentgateway'=> wp_create_nonce("payment-gateway"), 
					'permalink'=> get_permalink(),			
					) );
					$big_button_color=get_option('epjbdir_big_button_color');	
					if($big_button_color==""){$big_button_color='#2e7ff5';}	
					$small_button_color=get_option('epjbdir_small_button_color');	
					if($small_button_color==""){$small_button_color='#5f9df7';}
					$icon_color=get_option('epjbdir_icon_color');	
					if($icon_color==""){$icon_color='#5b5b5b';}	
					$title_color=get_option('epjbdir_title_color');	
					if($title_color==""){$title_color='#5b5b5b';}
					$button_font_color=get_option('epjbdir_button_font_color');	
					if($button_font_color==""){$button_font_color='#fffff';}
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
					wp_enqueue_script('dataTables', ep_listinghub_URLPATH . 'admin/files/js/jquery.dataTables.js');
					wp_enqueue_script('dataTablesrowReordermin', ep_listinghub_URLPATH . 'admin/files/js/dataTables.rowReorder.min.js');
					wp_enqueue_script('dataTablesresponsivemin', ep_listinghub_URLPATH . 'admin/files/js/dataTables.responsive.min.js');
				}
			}
			/**
				* Page based Style Loader
			*/
			public function listinghub_load_styles() {
				$screen = get_current_screen();	
					
				if (in_array($screen->id, array_values(self::$pages))) {
					wp_enqueue_style('jquery-ui', ep_listinghub_URLPATH . 'admin/files/css/jquery-ui.css');					
					wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
					wp_enqueue_style('listinghub_dashboard-style', ep_listinghub_URLPATH . 'admin/files/css/dashboard-admin.css');	
					wp_enqueue_style('dataTables-min', ep_listinghub_URLPATH . 'admin/files/css/jquery.dataTables.min.css');	
					wp_enqueue_style('rowReorder-dataTables', ep_listinghub_URLPATH . 'admin/files/css/rowReorder.dataTables.min.css');
					wp_enqueue_style('responsive-dataTables', ep_listinghub_URLPATH . 'admin/files/css/responsive.dataTables.min.css');	
				}
			}
			
			
			/**
				* Use this function to execute actions
			*/
			public function listinghub_import_data(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				include ('pages/import-demo.php');
				echo json_encode(array('code' => 'success'));
				exit(0);
			}
			public function listinghub_save_package() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'package' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				global $wpdb;			
				$listinghub_pack='listinghub_pack';
				$last_post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = '%s' ORDER BY `ID` DESC ", $listinghub_pack));
				$form_number = $last_post_id + 1;
				$role_name='';
				if($form_data['package_name']==""){
					$post_name = 'Package' . $form_number;
					$role_name=$post_name;
					}else{
					$post_name = sanitize_text_field($form_data['package_name']) .'-'. $form_number;
					$role_name=sanitize_text_field($form_data['package_name']);
				}					
				$post_title=sanitize_text_field($form_data['package_name']);
				$post_content= ''; 
				$my_post_form = array('post_title' => wp_strip_all_tags($post_title), 'post_name' => wp_strip_all_tags($post_name), 'post_content' => $post_content, 'post_type'=>'listinghub_pack', 'post_status' => 'draft', 'post_author' => get_current_user_id(),);
				$newpost_id = wp_insert_post($my_post_form);
				update_post_meta($newpost_id, 'package_subtitle', sanitize_text_field($form_data['package_subtile']));
				if(isset($form_data['is_it_feature'])){
					update_post_meta($newpost_id, '_is_it_feature', sanitize_text_field($form_data['is_it_feature']));
					}else{
					update_post_meta($newpost_id, '_is_it_feature', '');
				}
				if(isset($form_data['package_feature'] )){
					$package_feature= $form_data['package_feature'];
					$image_icon= (isset($form_data['feature_icon']) ? $form_data['feature_icon']:'');
					$i=0;
					for($i=0;$i<20;$i++){
						if(isset($package_feature[$i])){
							if(trim($package_feature[$i])!=''){
								update_post_meta($newpost_id, 'package_feature_'.$i, sanitize_text_field($package_feature[$i]));
								if(isset($image_icon[$i])){
									update_post_meta($newpost_id, 'feature_icon_'.$i, sanitize_text_field($image_icon[$i]));
								}
							}
						}
					}
				}
				update_post_meta($newpost_id, 'listinghub_display_order', sanitize_text_field($form_data['package_order']));
				update_post_meta($newpost_id, 'listinghub_package_cost', sanitize_text_field($form_data['package_cost']));
				update_post_meta($newpost_id, 'listinghub_package_initial_expire_interval', sanitize_text_field($form_data['package_initial_expire_interval']));							
				update_post_meta($newpost_id, 'listinghub_package_initial_expire_type', sanitize_text_field($form_data['package_initial_expire_type']));
				if(isset($form_data['package_recurring'])){
					update_post_meta($newpost_id, 'listinghub_package_recurring', sanitize_text_field($form_data['package_recurring']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_recurring', '');
				}
				if(isset($form_data['listinghub_stripe_plan'])){
					update_post_meta($newpost_id, 'listinghub_stripe_plan', sanitize_text_field($form_data['listinghub_stripe_plan']));
					}else{
					update_post_meta($newpost_id, 'listinghub_stripe_plan', '');
				}
				update_post_meta($newpost_id, 'listinghub_package_recurring_cost_initial', sanitize_text_field($form_data['package_recurring_cost_initial']));
				update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_count', sanitize_text_field($form_data['package_recurring_cycle_count']));
				update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_type', sanitize_text_field($form_data['package_recurring_cycle_type']));
				update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_limit', sanitize_text_field($form_data['package_recurring_cycle_limit']));
				if(isset($form_data['package_enable_trial_period'])){
					update_post_meta($newpost_id, 'listinghub_package_enable_trial_period', sanitize_text_field($form_data['package_enable_trial_period']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_enable_trial_period', 'no');
				}
				update_post_meta($newpost_id, 'listinghub_package_trial_amount', sanitize_text_field($form_data['package_trial_amount']));
				update_post_meta($newpost_id, 'listinghub_package_trial_period_interval', sanitize_text_field($form_data['package_trial_period_interval']));
				update_post_meta($newpost_id, 'listinghub_package_recurring_trial_type', sanitize_text_field($form_data['package_recurring_trial_type']));
				//Woocommerce_products
				if(isset($form_data['Woocommerce_product'])){
					update_post_meta($newpost_id, 'listinghub_package_woocommerce_product', sanitize_text_field($form_data['Woocommerce_product']));
				}
				// Start User Role
				global $wp_roles;
				$contributor_roles = $wp_roles->get_role('contributor');							
				$role_name_new= str_replace(' ', '_', $role_name);
				$wp_roles->remove_role( $role_name_new );
				$role_display_name = $role_name;
				$wp_roles->add_role($role_name_new, $role_display_name, array(
				'read' => true, // True allows that capability, False specifically removes it.
				'edit_posts' => true,
				'delete_posts' => true,
				'upload_files' => true //last in array needs no comma!
				));
				update_post_meta($newpost_id, 'listinghub_package_user_role', $role_name_new);					
				update_post_meta($newpost_id, 'listinghub_package_max_post_no', sanitize_text_field($form_data['max_pst_no']));				
				if(isset($form_data['listing_hide'])){
					update_post_meta($newpost_id, 'listinghub_package_hide_exp', sanitize_text_field($form_data['listing_hide']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_hide_exp', 'no');
				}
				if(isset($form_data['listing_badge_vip'])){
					update_post_meta($newpost_id, 'listinghub_package_vip_badge', sanitize_text_field($form_data['listing_badge_vip']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_vip_badge', 'no');
				}						
				if(isset($form_data['listing_feature'])){
					update_post_meta($newpost_id, 'listinghub_package_feature', sanitize_text_field($form_data['listing_feature']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_feature', 'no');
				}
				// End User Role
				// For Stripe Plan Create*****
				if(isset($form_data['package_recurring'])){
					$iv_gateway = get_option('listinghub_payment_gateway');
					if($iv_gateway=='stripe'){
						require_once(ep_listinghub_DIR . '/admin/init.php');
						$post_name2='listinghub_stripe_setting';
						$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name2 ));
						if(isset($row->ID )){
							$stripe_id= $row->ID;
						}			
						$stripe_mode=get_post_meta( $stripe_id,'listinghub_stripe_mode',true);	
						if($stripe_mode=='test'){
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_secret_test',true);	
							}else{
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_live_secret_key',true);	
						}									
						$interval_count= ($form_data['package_recurring_cycle_count']=="" ? '1':$form_data['package_recurring_cycle_count']);
						$stripe_currency =get_post_meta($stripe_id, 'listinghub_stripe_api_currency',true);
						\Stripe\Stripe::setApiKey($stripe_api);
						$stripe_array=array();
						$post_package_one = get_post($newpost_id); 
						$p_name = $post_package_one->post_name;
						$stripe_array['id']= $p_name;						
						$stripe_array['amount']=sanitize_text_field($form_data['package_recurring_cost_initial']) * 100;
						$stripe_array['interval']=sanitize_text_field($form_data['package_recurring_cycle_type']);									
						$stripe_array['interval_count']=$interval_count;
						$stripe_array['currency']=$stripe_currency;
						$stripe_array['product']=array('name' => $p_name);
						$trial=get_post_meta($newpost_id, 'listinghub_package_enable_trial_period', true);
						if($trial=='yes'){
							$trial_type = get_post_meta( $newpost_id,'listinghub_package_recurring_trial_type',true);
							$trial_cycle_count =get_post_meta($newpost_id, 'listinghub_package_trial_period_interval', true);
							switch ($trial_type) {
								case 'year':
								$periodNum =  365 * 1;
								break;
								case 'month':
								$periodNum =  30 * $trial_cycle_count;
								break;
								case 'week':
								$periodNum = 7 * $trial_cycle_count;
								break;
								case 'day':
								$periodNum = 1 * $trial_cycle_count;
								break;
							}									
							$stripe_array['trial_period_days']=$periodNum;
						}																	
						\Stripe\Plan::create($stripe_array);
					}	
				}
				// End Stripe Plan Create*****	
				echo json_encode(array('code' => 'success'));
				exit(0);
			}
			public function listinghub_update_myaccount_menu(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				// remove menu******
				if(isset($form_data['listinghome'])){
					update_option('epjblistinghub_menu_listinghome' ,sanitize_text_field($form_data['listinghome'])); 
					}else{
					update_option('epjblistinghub_menu_listinghome' ,'no') ; 
				}
				if(isset($form_data['mylevel'])){
					update_option('epjblistinghub_mylevel' ,sanitize_text_field($form_data['mylevel'])); 
					}else{
					update_option('epjblistinghub_mylevel' ,'no') ; 
				}
				if(isset($form_data['menusetting'])){
					update_option('epjblistinghub_menusetting' ,sanitize_text_field($form_data['menusetting'])); 
					}else{
					update_option('epjblistinghub_menusetting' ,'no') ; 
				}
				if(isset($form_data['menuallpost'])){
					update_option('epjblistinghub_menuallpost' ,sanitize_text_field($form_data['menuallpost'])); 
					}else{
					update_option('epjblistinghub_menuallpost' ,'no') ; 
				}
				if(isset($form_data['menunecandidates'])){
					update_option('epjblistinghub_menunecandidates' ,sanitize_text_field($form_data['menunecandidates'])); 
					}else{
					update_option('epjblistinghub_menunecandidates' ,'no') ; 
				}
				if(isset($form_data['messageboard'])){
					update_option('epjblistinghub_messageboard' ,sanitize_text_field($form_data['messageboard'])); 
					}else{
					update_option('epjblistinghub_messageboard' ,'no') ; 
				}
				if(isset($form_data['notification'])){
					update_option('epjblistinghub_notification' ,sanitize_text_field($form_data['notification'])); 
					}else{
					update_option('epjblistinghub_notification' ,'no') ; 
				}
				
				if(isset($form_data['author_bookmarks'])){
					update_option('epjblistinghub_author_bookmarks' ,sanitize_text_field($form_data['author_bookmarks'])); 
					}else{
					update_option('epjblistinghub_author_bookmarks' ,'no') ; 
				}
				if(isset($form_data['listing_bookmark'])){
					update_option('epjblistinghub_listing_bookmarks' ,sanitize_text_field($form_data['listing_bookmark'])); 
					}else{
					update_option('epjblistinghub_listing_bookmarks' ,'no') ; 
				}
				echo json_encode(array("code" => "success","msg"=> esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_profile_signup_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'admin' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$opt_array= array();
				$opt_type_array= array();
				$opt_type_value_array= array();
				$opt_type_roles_array= array();				
				$signup_array= array();
				$require_array= array();
				$myaccount_array= array();
				$max = sizeof($form_data['meta_name']);
				for($i = 0; $i < $max;$i++)
				{
					if($form_data['meta_name'][$i]!="" AND $form_data['meta_label'][$i]!=""){
						$opt_array[$form_data['meta_name'][$i]]=sanitize_text_field($form_data['meta_label'][$i]);	
						if(isset($form_data['field_type'][$i])){
							$opt_type_array[$form_data['meta_name'][$i]]=sanitize_text_field($form_data['field_type'][$i]);
							}else{
							$opt_type_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['field_type_value'][$i])){
							$opt_type_value_array[$form_data['meta_name'][$i]]=sanitize_text_field($form_data['field_type_value'][$i]);
							}else{
							$opt_type_value_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['field_user_role'.$i])){	
							$input_field_role_arr= array();
							foreach($form_data['field_user_role'.$i] as $field_role){
								$input_field_role_arr[]= sanitize_text_field($field_role);
							}							
							$opt_type_roles_array[$form_data['meta_name'][$i]]=$input_field_role_arr;
							}else{
							$opt_type_roles_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['signup'.$i])){							
							$signup_array[$form_data['meta_name'][$i]]='yes';							
							}else{							
							$signup_array[$form_data['meta_name'][$i]]='no';
						}
						if(isset($form_data['srequire'.$i])){							
							$require_array[$form_data['meta_name'][$i]]='yes';
							}else{
							$require_array[$form_data['meta_name'][$i]]='no';
						}
						if(isset($form_data['myaccountprofile'.$i])){							
							$myaccount_array[$form_data['meta_name'][$i]]='yes';
							}else{
							$myaccount_array[$form_data['meta_name'][$i]]='no';
						}
					}
				}
				update_option('listinghub_profile_fields', $opt_array );
				update_option('listinghub_field_type', $opt_type_array );
				update_option('listinghub_field_type_value', $opt_type_value_array );
				update_option('listinghub_field_type_roles', $opt_type_roles_array );
				update_option('listinghub_signup_fields', $signup_array );
				update_option('listinghub_myaccount_fields', $myaccount_array );				
				update_option('listinghub_signup_require', $require_array );
				if(isset($form_data['signup_profile_pic'])){
					update_option( 'listinghub_signup_profile_pic' ,sanitize_text_field($form_data['signup_profile_pic']));
					}else{
					update_option( 'listinghub_signup_profile_pic' ,'no') ;
				}
				if(isset($form_data['listinghub_payment_coupon'])){
					update_option( 'epjblistinghub_payment_coupon' ,sanitize_text_field($form_data['listinghub_payment_coupon']));
					}else{
					update_option( 'epjblistinghub_payment_coupon' ,'no') ;
				}
				if(isset($form_data['listinghub_payment_terms'])){
					update_option( 'listinghub_payment_terms' ,sanitize_text_field($form_data['listinghub_payment_terms']));
					}else{
					update_option( 'listinghub_payment_terms' ,'no') ;
				}
				echo json_encode(array('code' => 'Update Successfully'));
				exit(0);
			}
			public function listinghub_update_dir_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'dir-setting' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);	
				update_option('listinghub_user_can_publish',sanitize_text_field($form_data['listinghub_user_can_publish']));
				update_option('listinghub_archive_layout',sanitize_text_field($form_data['listinghub_archive_layout']));
				update_option('listinghub_listing_hide_opt',sanitize_text_field($form_data['listing_hide']));
				
				update_option('directoryprosinglepage',sanitize_text_field($form_data['directoryprosinglepage']));
				update_option('listing_single_custompage',sanitize_text_field($form_data['listing_single_custompage']));
				
				$custom_post_type=sanitize_text_field($form_data['listinghub_url']);
				$custom_post_type=strtolower($custom_post_type);
				$custom_post_type=str_replace(' ','',$custom_post_type);
				$custom_post_type=str_replace('-','',$custom_post_type);
				$custom_post_type=str_replace('*','',$custom_post_type);
				$custom_post_type=str_replace('$','',$custom_post_type);
				$custom_post_type=str_replace('#','',$custom_post_type);
				update_option('ep_listinghub_url',$custom_post_type);
				update_option('listinghub_dir_perpage',sanitize_text_field($form_data['listinghub_dir_perpage']));
				update_option('listinghub_listing_defaultimage',sanitize_text_field($form_data['listinghub_listing_defaultimage']));
				update_option('listinghub_location_defaultimage',sanitize_text_field($form_data['listinghub_location_defaultimage']));
				update_option('listinghub_category_defaultimage',sanitize_text_field($form_data['listinghub_category_defaultimage']));
				update_option('listinghub_banner_defaultimage',sanitize_text_field($form_data['listinghub_banner_defaultimage']));
				if(isset($form_data['listinghub_facet_listinglevel_show'])){
					update_option( 'listinghub_facet_listinglevel_show' ,sanitize_text_field($form_data['listinghub_facet_listinglevel_show']));
					}else{
					update_option( 'listinghub_facet_listinglevel_show' ,'no') ; 						
				}
				echo json_encode(array("code" => "success","msg"=> esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_profile_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'my-menu' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$opt_array2= array();
				if(isset($form_data['menu_title'])){
					$max = sizeof($form_data['menu_title']);
					for($i = 0; $i < $max;$i++)
					{	
						if($form_data['menu_title'][$i]!="" AND $form_data['menu_link'][$i]!=""){
							$opt_array2[$form_data['menu_title'][$i]]=$form_data['menu_link'][$i];
						}
					}			
					update_option('listinghub_profile_menu', $opt_array2 );
				}
				// remove menu******
				if(isset($form_data['listinghome'])){
					update_option('epjblistinghub_menu_listinghome' ,sanitize_text_field($form_data['listinghome'])); 
					}else{
					update_option('epjblistinghub_menu_listinghome' ,'no') ; 
				}
				if(isset($form_data['mylevel'])){
					update_option('epjblistinghub_mylevel' ,sanitize_text_field($form_data['mylevel'])); 
					}else{
					update_option('epjblistinghub_mylevel' ,'no') ; 
				}
				if(isset($form_data['menusetting'])){
					update_option('epjblistinghub_menusetting' ,sanitize_text_field($form_data['menusetting'])); 
					}else{
					update_option('epjblistinghub_menusetting' ,'no') ; 
				}
				if(isset($form_data['menuallpost'])){
					update_option('epjblistinghub_menuallpost' ,sanitize_text_field($form_data['menuallpost'])); 
					}else{
					update_option('epjblistinghub_menuallpost' ,'no') ; 
				}
				if(isset($form_data['menunecandidates'])){
					update_option('epjblistinghub_menunecandidates' ,sanitize_text_field($form_data['menunecandidates'])); 
					}else{
					update_option('epjblistinghub_menunecandidates' ,'no') ; 
				}
				if(isset($form_data['messageboard'])){
					update_option('epjblistinghub_messageboard' ,sanitize_text_field($form_data['messageboard'])); 
					}else{
					update_option('epjblistinghub_messageboard' ,'no') ; 
				}
				if(isset($form_data['notification'])){
					update_option('epjblistinghub_notification' ,sanitize_text_field($form_data['notification'])); 
					}else{
					update_option('epjblistinghub_notification' ,'no') ; 
				}
				
				if(isset($form_data['author_bookmarks'])){
					update_option('epjblistinghub_author_bookmarks' ,sanitize_text_field($form_data['author_bookmarks'])); 
					}else{
					update_option('epjblistinghub_author_bookmarks' ,'no') ; 
				}
				if(isset($form_data['listing_bookmark'])){
					update_option('epjblistinghub_listing_bookmarks' ,sanitize_text_field($form_data['listing_bookmark'])); 
					}else{
					update_option('epjblistinghub_listing_bookmarks' ,'no') ; 
				}
				echo json_encode(array('code' => esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_dir_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'fields' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$opt_array= array();				
				$opt_type_array= array();
				$opt_type_value_array= array();
				$opt_type_cat_array= array();			
				$max = sizeof($form_data['meta_name']);
				for($i = 0; $i < $max;$i++)
				{
					if($form_data['meta_name'][$i]!="" AND $form_data['meta_label'][$i]!=""){
						if(isset($form_data['meta_name'][$i])){
							$opt_array[sanitize_text_field($form_data['meta_name'][$i])]=sanitize_text_field($form_data['meta_label'][$i]);	
							}else{
							$opt_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['field_type'][$i])){
							$opt_type_array[$form_data['meta_name'][$i]]=sanitize_text_field($form_data['field_type'][$i]);
							}else{
							$opt_type_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['field_type_value'][$i])){
							$opt_type_value_array[$form_data['meta_name'][$i]]=sanitize_text_field($form_data['field_type_value'][$i]);
							}else{
							$opt_type_value_array[$form_data['meta_name'][$i]]='';
						}
						if(isset($form_data['field_categories'.$i])){							
							$opt_type_cat_array[$form_data['meta_name'][$i]]=$form_data['field_categories'.$i];
							}else{
							$opt_type_cat_array[$form_data['meta_name'][$i]]='';
						}	
					}
				}
				update_option('listinghub_li_fields', $opt_array );
				update_option('listinghub_li_field_type', $opt_type_array );
				update_option('listinghub_li_fieldtype_value', $opt_type_value_array );
				update_option('listinghub_field_type_cat', $opt_type_cat_array );				
				update_option('listinghub_listing_level',sanitize_text_field($form_data['listinghub_listing_level_all']));						
				update_option('listinghub_listing_status',sanitize_text_field($form_data['listinghub_listing_status_all']));
				update_option('listinghub_experience_range',sanitize_text_field($form_data['listing_listinghub_experience_range']));
				echo json_encode(array('code' => 'Update Successfully'));
				exit(0);
			}
			public function listinghub_update_package() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'package' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$post_content="";
				global $wpdb;			
				$post_title=sanitize_text_field($form_data['package_name']);
				$post_id=sanitize_text_field($form_data['package_id']);
				$newpost_id=$post_id;
				$post_content= ''; 
				$post_type = 'listinghub_pack';						
				$my_post = array(
				'ID'           => $post_id,
				'post_title'   => $post_title,
				'post_content'=>	$post_content,
				);
				wp_update_post( $my_post );
				update_post_meta($newpost_id, 'package_subtitle', sanitize_text_field($form_data['package_subtile']));
				if(isset($form_data['is_it_feature'])){
					update_post_meta($newpost_id, '_is_it_feature', sanitize_text_field($form_data['is_it_feature']));
					}else{
					update_post_meta($newpost_id, '_is_it_feature', '');
				}
				for($i=0;$i<30;$i++){			
					delete_post_meta($newpost_id, 'feature_icon_'.$i );
					delete_post_meta($newpost_id, 'package_feature_'.$i );
				}
				if(isset($form_data['package_feature'] )){
					$package_feature= $form_data['package_feature'];
					$image_icon= (isset($form_data['feature_icon']) ? $form_data['feature_icon']:'');
					$i=0;
					for($i=0;$i<30;$i++){
						if(isset($package_feature[$i])){
							if(trim($package_feature[$i])!=''){
								update_post_meta($newpost_id, 'package_feature_'.$i, $package_feature[$i]);
								if(isset($image_icon[$i])){
									update_post_meta($newpost_id, 'feature_icon_'.$i, sanitize_text_field($image_icon[$i]));
								}
							}
						}
					}
				}
				update_post_meta($newpost_id, 'listinghub_display_order', sanitize_text_field($form_data['package_order']));				
				update_post_meta($newpost_id, 'listinghub_package_cost', sanitize_text_field($form_data['package_cost']));
				update_post_meta($newpost_id, 'listinghub_package_initial_expire_interval', sanitize_text_field($form_data['package_initial_expire_interval']));							
				update_post_meta($newpost_id, 'listinghub_package_initial_expire_type', sanitize_text_field($form_data['package_initial_expire_type']));
				if(isset($form_data['package_recurring'])){
					update_post_meta($newpost_id, 'listinghub_package_recurring', sanitize_text_field($form_data['package_recurring']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_recurring', '');
				}
				if(isset($form_data['listinghub_stripe_plan'])){
					update_post_meta($newpost_id, 'listinghub_stripe_plan', sanitize_text_field($form_data['listinghub_stripe_plan']));
					}else{
					update_post_meta($newpost_id, 'listinghub_stripe_plan', '');
				}
				if(isset($form_data['package_recurring'])){
					update_post_meta($newpost_id, 'listinghub_package_recurring', sanitize_text_field($form_data['package_recurring']));
					update_post_meta($newpost_id, 'listinghub_package_recurring_cost_initial', sanitize_text_field($form_data['package_recurring_cost_initial']));
					update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_count', sanitize_text_field($form_data['package_recurring_cycle_count']));
					update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_type', sanitize_text_field($form_data['package_recurring_cycle_type']));
					update_post_meta($newpost_id, 'listinghub_package_recurring_cycle_limit', sanitize_text_field($form_data['package_recurring_cycle_limit']));
					if(isset($form_data['package_enable_trial_period'])){
						update_post_meta($newpost_id, 'listinghub_package_enable_trial_period', sanitize_text_field($form_data['package_enable_trial_period']));
						}else{
						update_post_meta($newpost_id, 'listinghub_package_enable_trial_period', 'no');
					}
					update_post_meta($newpost_id, 'listinghub_package_trial_amount', sanitize_text_field($form_data['package_trial_amount']));
					update_post_meta($newpost_id, 'listinghub_package_trial_period_interval', sanitize_text_field($form_data['package_trial_period_interval']));
					update_post_meta($newpost_id, 'listinghub_package_recurring_trial_type', sanitize_text_field($form_data['package_recurring_trial_type']));
				}
				//Woocommerce_products
				if(isset($form_data['Woocommerce_product'])){
					update_post_meta($newpost_id, 'listinghub_package_woocommerce_product', sanitize_text_field($form_data['Woocommerce_product']));				
				}
				update_post_meta($newpost_id, 'listinghub_package_max_post_no', sanitize_text_field($form_data['max_pst_no']));				
				if(isset($form_data['listing_hide'])){
					update_post_meta($newpost_id, 'listinghub_package_hide_exp', sanitize_text_field($form_data['listing_hide']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_hide_exp', 'no');
				}
				if(isset($form_data['listing_facilities'])){
					update_post_meta($newpost_id, 'listinghub_package_facilities', sanitize_text_field($form_data['listing_facilities']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_facilities', 'no');
				}
				if(isset($form_data['listing_deal'])){
					update_post_meta($newpost_id, 'listinghub_package_deal', sanitize_text_field($form_data['listing_deal']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_deal', 'no');
				}
				if(isset($form_data['listing_badge_vip'])){
					update_post_meta($newpost_id, 'listinghub_package_vip_badge', sanitize_text_field($form_data['listing_badge_vip']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_vip_badge', 'no');
				}						
				if(isset($form_data['listing_video'])){
					update_post_meta($newpost_id, 'listinghub_package_video', sanitize_text_field($form_data['listing_video']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_video', 'no');
				}
				if(isset($form_data['listing_plan'])){
					update_post_meta($newpost_id, 'listinghub_package_plan', sanitize_text_field($form_data['listing_plan']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_plan', 'no');
				}
				if(isset($form_data['listing_feature'])){
					update_post_meta($newpost_id, 'listinghub_package_feature', sanitize_text_field($form_data['listing_feature']));
					}else{
					update_post_meta($newpost_id, 'listinghub_package_feature', 'no');
				}
				// For Stripe*****
				// For Stripe Plan Edit*****
				if(isset($form_data['package_recurring'])){
					$iv_gateway = get_option('listinghub_payment_gateway');
					if($iv_gateway=='stripe'){
						require_once(ep_listinghub_DIR . '/admin/init.php');
						$post_name2='listinghub_stripe_setting';
						$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name2));
						if(isset($row->ID )){
							$stripe_id= $row->ID;
						}			
						$stripe_mode=get_post_meta( $stripe_id,'listinghub_stripe_mode',true);	
						if($stripe_mode=='test'){
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_secret_test',true);	
							}else{
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_live_secret_key',true);	
						}									
						$interval_count= ($form_data['package_recurring_cycle_count']=="" ? '1':sanitize_text_field($form_data['package_recurring_cycle_count']));
						$stripe_currency =get_post_meta($stripe_id, 'listinghub_stripe_api_currency',true);
						\Stripe\Stripe::setApiKey($stripe_api);
						$stripe_array=array();
						$post_package_one = get_post($newpost_id); 
						$error=0;
						$p_name = $post_package_one->post_name;
						$stripe_array['id']= $p_name;						
						$stripe_array['amount']=sanitize_text_field($form_data['package_recurring_cost_initial']) * 100;
						$stripe_array['interval']=sanitize_text_field($form_data['package_recurring_cycle_type']);									
						$stripe_array['interval_count']=$interval_count;
						$stripe_array['currency']=$stripe_currency;
						$stripe_array['product']=array('name' => $p_name);
						$trial=get_post_meta($newpost_id, 'listinghub_package_enable_trial_period', true);
						if($trial=='yes'){
							$trial_type = get_post_meta( $newpost_id,'listinghub_package_recurring_trial_type',true);
							$trial_cycle_count =get_post_meta($newpost_id, 'listinghub_package_trial_period_interval', true);
							switch ($trial_type) {
								case 'year':
								$periodNum =  365 * 1;
								break;
								case 'month':
								$periodNum =  30 * $trial_cycle_count;
								break;
								case 'week':
								$periodNum = 7 * $trial_cycle_count;
								break;
								case 'day':
								$periodNum = 1 * $trial_cycle_count;
								break;
							}									
							$stripe_array['trial_period_days']=$periodNum;
						}																	
						try {
							$p = \Stripe\Plan::retrieve($p_name);
							} catch (Exception $e) {
							$error==1;
						}
						if( $error==0){
							$p->delete();	
						}
						\Stripe\Plan::create($stripe_array);
					}	
				}
				// End Stripe Plan Create*****	
				echo json_encode(array('code' => 'success'));
				exit(0);
			}
			public function listinghub_update_paypal_settings() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'payment-gateway' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$post_content="";
				global $wpdb;		
				$post_name='listinghub_paypal_setting';
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name ));
				if(!isset($row->ID )){
					$my_post_form = array('post_title' => wp_strip_all_tags($post_name), 'post_name' => wp_strip_all_tags($post_name), 'post_content' => 'Paypal Setting', 'post_type'=>'iv_payment_setting','post_status' => 'draft', 'post_author' => get_current_user_id(),);
					$newpost_id = wp_insert_post($my_post_form);
					}else{
					$newpost_id= $row->ID;
				}
				update_post_meta($newpost_id, 'listinghub_paypal_mode', sanitize_text_field($form_data['paypal_mode']));
				update_post_meta($newpost_id, 'listinghub_paypal_username', sanitize_text_field($form_data['paypal_username']));
				update_post_meta($newpost_id, 'listinghub_paypal_api_password', sanitize_text_field($form_data['paypal_api_password']));
				update_post_meta($newpost_id, 'listinghub_paypal_api_signature', sanitize_text_field($form_data['paypal_api_signature']));
				update_post_meta($newpost_id, 'listinghub_paypal_api_currency', sanitize_text_field($form_data['paypal_api_currency']));						
				update_option('listinghub_api_currency', sanitize_text_field($form_data['paypal_api_currency'] ));
				echo json_encode(array('code' => 'success'));
				exit(0);
			}
			public function listinghub_update_openai_settings(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				if(isset($form_data['listinghub_openai_api_key'])){
					update_option('listinghub_openai_api_key' ,sanitize_text_field($form_data['listinghub_openai_api_key'])); 
					update_option('listinghub_openai_api_key_status' ,''); 
				}
				if(isset($form_data['listinghub_active_chatGPT'])){
					update_option('listinghub_active_chatGPT' ,sanitize_text_field($form_data['listinghub_active_chatGPT'])); 					
				}else{
					update_option('listinghub_active_chatGPT' ,'No'); 
				}
				if(isset($form_data['gpt_model'])){
					update_option('listinghub_gpt_model' ,sanitize_text_field($form_data['gpt_model'])); 					
				}
				$listinghub_gpt_model = get_option('listinghub_gpt_model');
				$listinghub_openai_api_key = get_option('listinghub_openai_api_key');		
                if($listinghub_openai_api_key != '') {
                    $listinghub_openai_api_key_status = get_option('listinghub_openai_api_key_status', true);
                    
                    if ($listinghub_openai_api_key_status == '') {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/completions"); // ✅ Fixed API URL
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true); // ✅ Ensures request is POST
                
                        $headers = array(
                            "Content-Type: application/json",
                            "Authorization: Bearer " . trim($listinghub_openai_api_key) // ✅ Ensures proper API key formatting
                        );
                
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                        $data = array(
                            "model" => $listinghub_gpt_model, // ✅ Uses correct model (change if needed)
                            "prompt" => "Hello,",
                            "max_tokens" => 5
                        );
                
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                
                        $response = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $error = curl_error($ch);
                        
                        curl_close($ch);
                
                        if ($error) {					
                            update_option('listinghub_openai_api_key_status', 'API key is not valid: cURL Error - ' . $error); 
                        } else {
                            if ($http_code == 200) {
                                update_option('listinghub_openai_api_key_status', 'clear');						
                            } else {
                                update_option('listinghub_openai_api_key_status', 'API key is not valid: HTTP ' . $http_code); 
                            }
                        }
                
                        $listinghub_openai_api_key_status = get_option('listinghub_openai_api_key_status', true);
                    }
                
                    if ($listinghub_openai_api_key_status == 'clear') {					
                        $key_status = esc_html__('API key is valid', 'listinghub');
                    } else {
                        $key_status = esc_html__('No, API key is not valid: ' . $listinghub_openai_api_key_status, 'listinghub');
                    }
                }

				
				echo json_encode(array("code" => "success","msg"=> esc_html__( 'Updated Successfully', 'listinghub') ,"api_key_status"=>$key_status));
				exit(0);
			}
			public function listinghub_update_stripe_settings() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'payment-gateway' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$post_content="";
				global $wpdb;			
				$post_name='listinghub_stripe_setting';
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name));
				if(!isset($row->ID )){
					$post_type = 'iv_payment_setting';
					$my_post_form = array('post_title' => wp_strip_all_tags($post_name), 'post_name' => wp_strip_all_tags($post_name), 'post_content' => 'stripe Setting','post_type'=>$post_type, 'post_status' => 'draft', 'post_author' => get_current_user_id(),);
					$newpost_id = wp_insert_post($my_post_form);								
					}else{
					$newpost_id= $row->ID;
				}
				update_post_meta($newpost_id, 'listinghub_stripe_mode', sanitize_text_field($form_data['stripe_mode']));
				update_post_meta($newpost_id, 'listinghub_stripe_live_secret_key', sanitize_text_field($form_data['secret_key']));update_post_meta($newpost_id, 'listinghub_stripe_live_publishable_key', sanitize_text_field($form_data['publishable_key']));
				update_post_meta($newpost_id, 'listinghub_stripe_secret_test', sanitize_text_field($form_data['secret_key_test']));						
				update_post_meta($newpost_id, 'listinghub_stripe_publishable_test', sanitize_text_field($form_data['stripe_publishable_test']));		
				update_post_meta($newpost_id, 'listinghub_stripe_api_currency', sanitize_text_field($form_data['stripe_api_currency']));
				update_option('listinghub_api_currency', sanitize_text_field($form_data['stripe_api_currency'] ));
				echo json_encode(array('code' => 'success'));
				exit(0);
			}
			public function listinghub_create_coupon() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'coupon' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$post_content="";
				global $wpdb;			
				$post_name=$form_data['coupon_name'];
				$coupon_data = array('post_title' => wp_strip_all_tags($post_name), 'post_name' => wp_strip_all_tags($post_name), 'post_content' => $post_name,'post_type'=>'listinghub_coupon', 'post_status' => 'draft', 'post_author' => get_current_user_id(),);
				$newpost_id = wp_insert_post($coupon_data);
				if($form_data['coupon_count']==""){
					$coupon_limit='99999';
					}else{
					$coupon_limit=sanitize_text_field($form_data['coupon_count']);
				}
				$pac='';
				if(isset($_POST['form_pac_ids'])){$pac=$_POST['form_pac_ids'];}
				$pck_ids =implode(",",$pac);						
				update_post_meta($newpost_id, 'listinghub_coupon_pac_id', $pck_ids);
				update_post_meta($newpost_id, 'listinghub_coupon_limit',$coupon_limit);
				update_post_meta($newpost_id, 'listinghub_coupon_start_date', sanitize_text_field($form_data['start_date']));
				update_post_meta($newpost_id, 'listinghub_coupon_end_date', sanitize_text_field($form_data['end_date']));
				update_post_meta($newpost_id, 'listinghub_coupon_amount', sanitize_text_field($form_data['coupon_amount']));
				update_post_meta($newpost_id, 'listinghub_coupon_type', sanitize_text_field($form_data['coupon_type']));
				echo json_encode(array('code' => 'success'));
				exit(0);
			}	
			public function listinghub_update_coupon() {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'coupon' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);						
				$post_content="";
				global $wpdb;	
				$post_title=sanitize_text_field($form_data['coupon_name']);
				$post_id=sanitize_text_field($form_data['coupon_id']);
				$newpost_id=$post_id;
				$my_post = array(
				'ID'           => $post_id,
				'post_title'   => $post_title,								
				);
				wp_update_post( $my_post );
				$pck_ids =implode(",",$_POST['form_pac_ids']);						
				update_post_meta($newpost_id, 'listinghub_coupon_pac_id', $pck_ids);
				update_post_meta($newpost_id, 'listinghub_coupon_limit', sanitize_text_field($form_data['coupon_count']));
				update_post_meta($newpost_id, 'listinghub_coupon_start_date', sanitize_text_field($form_data['start_date']));
				update_post_meta($newpost_id, 'listinghub_coupon_end_date', sanitize_text_field($form_data['end_date']));
				update_post_meta($newpost_id, 'listinghub_coupon_amount', sanitize_text_field($form_data['coupon_amount']));	update_post_meta($newpost_id, 'listinghub_coupon_type', sanitize_text_field($form_data['coupon_type']));
				echo json_encode(array('code' => 'success'));
				exit(0);
			}	
			public function listinghub_update_payment_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$iv_terms='no';
				if(isset($form_data['iv_terms'])){
					$iv_terms=sanitize_text_field($form_data['iv_terms']);
				}
				$terms_detail=sanitize_text_field($form_data['terms_detail']);
				$iv_coupon='';
				if(isset($form_data['iv_coupon'])){
					$iv_coupon=sanitize_text_field($form_data['iv_coupon']);
				}
				update_option('listinghub_payment_terms_text', $terms_detail );
				update_option('listinghub_payment_terms', $iv_terms );
				update_option('epjblistinghub_payment_coupon', $iv_coupon );
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_account_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$post_approved='no';
				if(isset($form_data['post_approved'])){
					$post_approved=sanitize_text_field($form_data['post_approved']);
				}
				$signup_redirect=sanitize_text_field($form_data['signup_redirect']);
				$private_profile_page  = sanitize_text_field($form_data['pri_profile_redirect']); 
				$pub_profile_redirect=sanitize_text_field($form_data['profile_redirect']);
				if(isset($form_data['hide_admin_bar'])){
					$admin_bar=$form_data['hide_admin_bar'];
					}else{
					$admin_bar='no';
				}
				update_option('listinghub_post_approved', $post_approved );
				update_option('listinghub_signup_redirect', $signup_redirect );
				update_option('epjblistinghub_profile_page', $private_profile_page );
				update_option('epjblistinghub_profile_public_page', $pub_profile_redirect );
				update_option('epjblistinghub_hide_admin_bar', $admin_bar );
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_protected_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				if(isset($form_data['active_visibility'])){
					$active_visibility=$form_data['active_visibility'];
					}else{
					$active_visibility='no';
				}		
				update_option('epjblistinghub_active_visibility', $active_visibility );
				if(isset($form_data['login_message'])){
					update_option('epjbiv_visibility_login_message', sanitize_text_field($form_data['login_message'] ));
				}
				if(isset($form_data['visitor_message'])){
					update_option('epjbiv_visibility_visitor_message', sanitize_text_field($form_data['visitor_message'] ));
				}
				update_option('epjbiv_visibility_serialize_role', $form_data);
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_page_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				$iv_terms='no';
				if(isset($form_data['iv_terms'])){
					$iv_terms=$form_data['iv_terms'];
				}
				$pricing_page=sanitize_text_field($form_data['pricing_page']);
				$signup_page=sanitize_text_field($form_data['signup_page']);
				$profile_page=sanitize_text_field($form_data['profile_page']);
				$profile_public=sanitize_text_field($form_data['profile_public']);
				$thank_you=sanitize_text_field($form_data['thank_you_page']);
				$login=sanitize_text_field($form_data['login_page']);
				update_option('epjblistinghub_price_table', $pricing_page); 
				update_option('epjblistinghub_registration', $signup_page); 
				update_option('epjblistinghub_profile_page', $profile_page);
				update_option('epjblistinghub_profile_public',$profile_public);
				update_option('epjblistinghub_thank_you_page',$thank_you); 
				update_option('epjblistinghub_login_page',$login); 				
				update_option('epjblistinghub_author_dir_page',sanitize_text_field($form_data['employer_dir'])); 
				update_option('epjblistinghub_candidate_dir_page',sanitize_text_field($form_data['candidate_dir']));
				update_option('epjblistinghubr_public_profile_page',sanitize_text_field($form_data['employer_public']));
				update_option('epjblistinghub_candidate_public_page',sanitize_text_field($form_data['candidate_public']));
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_email_setting(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				$allowed_html = wp_kses_allowed_html( 'post' );
				parse_str($_POST['form_data'], $form_data);
				update_option( 'listinghub_signup_email_subject',sanitize_text_field($form_data['listinghub_signup_email_subject']));			
				update_option( 'listinghub_signup_email',wp_kses( $form_data['signup_email_template'], $allowed_html));
				update_option( 'listinghub_forget_email_subject',sanitize_text_field($form_data['forget_email_subject']));				
				update_option( 'listinghub_forget_email',wp_kses( $form_data['forget_email_template'], $allowed_html));
				update_option('listinghub_admin_email', sanitize_text_field($form_data['listinghub_admin_email'])); 
				update_option('listinghub_order_client_email_sub', sanitize_text_field($form_data['listinghub_order_email_subject']));
				update_option( 'listinghub_order_client_email',wp_kses( $form_data['order_client_email_template'], $allowed_html));
				update_option('listinghub_order_admin_email_sub', sanitize_text_field($form_data['listinghub_order_admin_email_subject']));
				update_option( 'listinghub_order_admin_email',wp_kses( $form_data['order_admin_email_template'], $allowed_html));
				update_option( 'listinghub_reminder_email_subject',sanitize_text_field($form_data['listinghub_reminder_email_subject']));
				update_option( 'listinghub_reminder_email',wp_kses( $form_data['reminder_email_template'], $allowed_html));
				update_option('listinghub_reminder_day', sanitize_text_field($form_data['listinghub_reminder_day'])); 
				update_option( 'listinghub_contact_email_subject',sanitize_text_field($form_data['contact_email_subject']));				
				update_option( 'listinghub_contact_email',wp_kses( $form_data['message_email_template'], $allowed_html));
				update_option( 'listinghub_apply_email_subject',sanitize_text_field($form_data['listinghub_apply_email_subject']));				
				update_option( 'listinghub_apply_email',wp_kses( $form_data['apply_email_template'], $allowed_html));
				update_option( 'listinghub_notification_email_subject',sanitize_text_field($form_data['listinghub_notification_email_subject']));
				update_option( 'listinghub_notification_email',wp_kses( $form_data['notification_email_template'], $allowed_html));
				$bcc_message=(isset($form_data['bcc_message'])? sanitize_text_field($form_data['bcc_message']):'' );		
				update_option('epjblistinghub_bcc_message',$bcc_message);
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_add_home_page(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				$page_title='Home';
				$page_name='home';
				$page_content='';
				$post_iv = array(
				'post_title'    => wp_strip_all_tags( $page_title),
				'post_name'    => wp_strip_all_tags( $page_name),
				'post_content'  => $page_content,
				'post_status'   => 'publish',
				'post_author'   =>  get_current_user_id(),	
				'post_type'		=> 'page',
				);
				$newpost_id= wp_insert_post( $post_iv );
				update_option( 'page_on_front', $newpost_id );
				update_option( 'show_on_front', 'page' );
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Home page added', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_mailchamp_setting (){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				update_option('listinghub_mailchimp_api_key', sanitize_text_field($form_data['listinghub_mailchimp_api_key'])); 
				update_option('listinghub_mailchimp_confirmation', sanitize_text_field($form_data['listinghub_mailchimp_confirmation'])); 
				if(isset($form_data['listinghub_mailchimp_list'])){
					update_option('listinghub_mailchimp_list', sanitize_text_field($form_data['listinghub_mailchimp_list'])); 
				}
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_package_status (){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'package' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				global $wpdb;
				$package_id_update=trim($_POST['status_id']);
				$package_current_status=trim(sanitize_text_field($_POST['status_current']));
				if($package_current_status=="pending"){
					$package_st='draft';
					$pac_msg='Active';
					}else{
					$package_st='pending';
					$pac_msg='Inactive';
				}
				$post_type = 'listinghub_pack';
				$query =$wpdb->prepare( "UPDATE {$wpdb->prefix}posts SET post_status='%s' WHERE ID='%s' LIMIT 1",$package_st,$package_id_update );
				$wpdb->query($query);
				echo json_encode(array("code" => "success","msg"=>$pac_msg,"current_st"=>$package_st));
				exit(0);
			}
			public function listinghub_gateway_settings_update(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'payment-gateway' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				$payment_gateway = sanitize_text_field($_POST['payment_gateway']);
				global $wpdb;
				update_option('listinghub_payment_gateway', $payment_gateway);
				// For Stripe Plan Create*****
				$iv_gateway = get_option('listinghub_payment_gateway');
				if($iv_gateway=='stripe'){
					$stripe_id='';
					$post_name2='listinghub_stripe_setting';
					$row2 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name2 ));
					if(isset($row2->ID )){
						$stripe_id= $row2->ID;
					}
					$listinghub_pack='listinghub_pack';
					$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = '%s'",$listinghub_pack );
					$membership_pack = $wpdb->get_results($sql);
					if(is_array($membership_pack)){
						$i=0;
						include(ep_listinghub_DIR. '/admin/init.php');
						$stripe_mode=get_post_meta( $stripe_id,'listinghub_stripe_mode',true);	
						if($stripe_mode=='test'){
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_secret_test',true);	
							}else{
							$stripe_api =get_post_meta($stripe_id, 'listinghub_stripe_live_secret_key',true);	
						}	
						$stripe_currency =get_post_meta($stripe_id, 'listinghub_stripe_api_currency',true);
						\Stripe\Stripe::setApiKey($stripe_api);
						foreach ( $membership_pack as $row )
						{		$package_recurring=get_post_meta( $row->ID,'listinghub_package_recurring',true);	
							if($package_recurring=='on'){
								$interval_count= get_post_meta( $row->ID,'listinghub_package_recurring_cycle_count',true);
								$interval_count= ($interval_count=="" ? '1':$interval_count);
								$stripe_array=array();						
								$p_name = $row->post_name;
								$stripe_array['id']= $p_name;								
								$stripe_array['amount']=get_post_meta( $row->ID,'listinghub_package_recurring_cost_initial',true) * 100;
								$stripe_array['interval']=get_post_meta( $row->ID,'listinghub_package_recurring_cycle_type',true);
								$stripe_array['interval_count']=$interval_count;
								$stripe_array['currency']=$stripe_currency;
								$stripe_array['product']=array('name' => $p_name);
								$trial=get_post_meta($row->ID, 'listinghub_package_enable_trial_period', true);
								if($trial=='yes'){
									$trial_type = get_post_meta( $row->ID,'listinghub_package_recurring_trial_type',true);
									$trial_cycle_count =get_post_meta($row->ID, 'listinghub_package_trial_period_interval', true);
									switch ($trial_type) {
										case 'year':
										$periodNum =  365 * 1;
										break;
										case 'month':
										$periodNum =  30 * (int)$trial_cycle_count;
										break;
										case 'week':
										$periodNum = 7 * (int)$trial_cycle_count;
										break;
										case 'day':
										$periodNum = 1 * (int)$trial_cycle_count;
										break;
									}									
									$stripe_array['trial_period_days']=$periodNum;
								}																	
								try {
									\Stripe\Plan::retrieve($p_name);
									} catch (Exception $e) {
									if($stripe_array['amount']>0){
										\Stripe\Plan::create($stripe_array);
									}
								}
							}	
						}
					}
				}	
				// End Stripe Plan Create*****	
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully: Your current gateway is ', 'listinghub').$payment_gateway));
				exit(0);
			}
			public function listinghub_update_color_settings(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				if(isset($form_data['big_button_color'])){
					update_option('epjbdir_big_button_color',sanitize_text_field($form_data['big_button_color']));
				}
				if(isset($form_data['small_button_color'])){
					update_option('epjbdir_small_button_color',sanitize_text_field($form_data['small_button_color']));
				}
				if(isset($form_data['icon_color'])){
					update_option('epjbdir_icon_color',sanitize_text_field($form_data['icon_color']));
				}
				if(isset($form_data['title_color'])){
					update_option('epjbdir_title_color',sanitize_text_field($form_data['title_color']));
				}
				if(isset($form_data['button_font_color'])){
					update_option('epjbdir_button_font_color',sanitize_text_field($form_data['button_font_color']));
				}
				if(isset($form_data['button_small_font_color'])){
					update_option('epjbdir_button_small_font_color',sanitize_text_field($form_data['button_small_font_color']));
				}
				if(isset($form_data['content_font_color'])){
					update_option('epjbdir_content_font_color',sanitize_text_field($form_data['content_font_color']));
				}
				if(isset($form_data['border_color'])){
					update_option('epjbdir_border_color',sanitize_text_field($form_data['border_color']));
				}
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_map_settings(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}
				parse_str($_POST['form_data'], $form_data);
				update_option('epjbdir_map_api',sanitize_text_field($form_data['dir_map_api']));
				update_option('epjbdir_map_zoom',sanitize_text_field($form_data['dir_map_zoom']));				
				update_option('epjbdir_map_type',sanitize_text_field($form_data['map_type']));
				update_option('epjbdir_map_radius',sanitize_text_field($form_data['epjbdir_map_radius']));
				update_option('listinghub_near_to_me',sanitize_text_field($form_data['listinghub_near_to_me']));				
				update_option('listinghub_defaultlatitude',sanitize_text_field($form_data['listinghub_defaultlatitude']));
				update_option('listinghub_defaultlongitude',sanitize_text_field($form_data['listinghub_defaultlongitude']));
				if(isset($form_data['listinghub_forcelocation'])){
					update_option('listinghub_forcelocation',sanitize_text_field($form_data['listinghub_forcelocation']));
					}else{
					update_option('listinghub_forcelocation','no');
				}
				if(isset($form_data['listinghub_infobox_image'])){
					update_option('listinghub_infobox_image',sanitize_text_field($form_data['listinghub_infobox_image']));
					}else{
					update_option('listinghub_infobox_image','no');
				}
				if(isset($form_data['listinghub_infobox_title'])){
					update_option('listinghub_infobox_title',sanitize_text_field($form_data['listinghub_infobox_title']));
					}else{
					update_option('listinghub_infobox_title','no');
				}
				if(isset($form_data['listinghub_infobox_location'])){
					update_option('listinghub_infobox_location',sanitize_text_field($form_data['listinghub_infobox_location']));
					}else{
					update_option('listinghub_infobox_location','no');
				}
				if(isset($form_data['listinghub_infobox_direction'])){
					update_option('listinghub_infobox_direction',sanitize_text_field($form_data['listinghub_infobox_direction']));
					}else{
					update_option('listinghub_infobox_direction','no');
				}
				if(isset($form_data['listinghub_infobox_linkdetail'])){
					update_option('listinghub_infobox_linkdetail',sanitize_text_field($form_data['listinghub_infobox_linkdetail']));
					}else{
					update_option('listinghub_infobox_linkdetail','no');
				}
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_search_shortcodes_saved_delete(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}	
				parse_str($_POST['form_data'], $form_data);
				$new_field_arr= array();
				if(isset($form_data['shortcodearr'])){
					foreach ( $form_data['shortcodearr'] as $field_key => $field_value ) { 
						$new_field_arr[]= sanitize_text_field($field_value);
					}
				}				
				print_r($new_field_arr);
				update_option('listinghub_search_shortcodes_saved',$new_field_arr);
				echo json_encode(array("code" => "success","msg"=>''));
				exit(0);
			}
			public function listinghub_create_search_shortcode(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}	
				parse_str($_POST['form_data'], $form_data);
				$post_fields_type=array();		
				$i=0;$field_name='';	$field_type='';
				if(isset($form_data['listinghub_single_restrict'])){
					update_option('listinghub_single_restrict',sanitize_text_field($form_data['listinghub_single_restrict']));
					}else{
					update_option('listinghub_single_restrict','no');
				}
				$short_text='[listinghub_search ';
				$short_text=$short_text.'action="'.$form_data['listinghub_search_action_target'].'" ';
				foreach ( $form_data['search-field-name'] as $field_key => $field_value ) { 
					$post_fields_type[sanitize_text_field($field_value)]=sanitize_text_field($form_data['search-field-type'][$field_key]);	
					$field_name=$field_name.$field_value.',';
					$field_type=$field_type.sanitize_text_field($form_data['search-field-type'][$field_key]).',';
					$i++;
				}
				$short_text=$short_text. 'field-name="'.$field_name.'" field-type="'.$field_type.'"  ]';
				$short_text_all='';
				$short_text_saved=get_option('listinghub_search_shortcodes_saved' );
				if(is_array($short_text_saved)){
					$short_text_saved[]=$short_text;	
				}
				if($short_text_saved==''){
					$short_text_saved =array();
					$short_text_saved[]=$short_text;
				}
				update_option('listinghub_search_shortcodes_saved',$short_text_saved );
				echo json_encode(array("code" => "success","msg"=>$short_text));
				exit(0);
			}
			public function listinghub_update_single_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}	
				parse_str($_POST['form_data'], $form_data);				
				$post_fields_type=array();	
				$post_fields_icon=array();
				$i=0;
				foreach ( $form_data['search-field-name'] as $field_key => $field_value ) { 
					$post_fields_type[sanitize_text_field($field_value)]=sanitize_text_field($form_data['search-field-type'][$field_key]);$post_fields_icon[sanitize_text_field($field_value)]=sanitize_text_field($form_data['field_icon'][$field_key]);					
					$i++;
				}
				update_option('listinghub_single_fields_saved',$post_fields_type );
				update_option('listinghub_single_icon_saved',$post_fields_icon );				
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_archive_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}	
				parse_str($_POST['form_data'], $form_data);				
				$post_fields_type=array();
				$post_fields_icon=array();
				$i=0;
				foreach ( $form_data['search-field-name'] as $field_key => $field_value ) { 
					$post_fields_type[sanitize_text_field($field_value)]=sanitize_text_field($form_data['search-field-type'][$field_key]);
					$post_fields_icon[sanitize_text_field($field_value)]=sanitize_text_field($form_data['field_icon'][$field_key]);
					$i++;
				}
				update_option('listinghub_archive_fields_saved',$post_fields_type );
				update_option('listinghub_archive_icon_saved',$post_fields_icon );
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_search_fields(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}	
				parse_str($_POST['form_data'], $form_data);				
				$post_fields_type=array();		
				$i=0;
				foreach ( $form_data['search-field-name'] as $field_key => $field_value ) { 
					$post_fields_type[sanitize_text_field($field_value)]=sanitize_text_field($form_data['search-field-type'][$field_key]);					
					$i++;
				}
				update_option('listinghub_search_action_target',sanitize_text_field($form_data['listinghub_search_action_target']) );				
				update_option('listinghub_search_fields_saved',$post_fields_type );
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
			public function listinghub_update_user_settings(){
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'settings' ) ) {
					wp_die( 'Are you cheating:wpnonce?' );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 'Are you cheating:user Permission?' );
				}		
				parse_str($_POST['form_data'], $form_data);
				if(array_key_exists('wp_capabilities',$form_data)){
					wp_die( 'Are you cheating:wp_capabilities?' );
				}	
				$user_id=sanitize_text_field($form_data['user_id']);
				if($form_data['exp_date']!=''){
					$exp_d=date('Y-m-d', strtotime($form_data['exp_date']));	 
					update_user_meta($user_id, 'listinghub_exprie_date',$exp_d); 
				}		
				update_user_meta($user_id, 'listinghub_payment_status', sanitize_text_field($form_data['payment_status']));	
				update_user_meta($user_id, 'listinghub_package_id',sanitize_text_field($form_data['package_sel'])); 
			
				if(isset($form_data['topbanner_url']) AND $form_data['topbanner_url']!=''){				 
					update_user_meta($user_id,'topbanner', sanitize_url($form_data['topbanner_url']));
				}
				if(isset($form_data['profile_image_url']) AND $form_data['profile_image_url']!=''){				 
					update_user_meta($user_id,'listinghub_profile_pic_thum', sanitize_url($form_data['profile_image_url']));
				}
				$user = new WP_User( $user_id );
				$user->set_role(sanitize_text_field($form_data['user_role']));
				$field_type=array();
				$field_type_opt=  get_option( 'listinghub_field_type' );
				if($field_type_opt!=''){
					$field_type=get_option('listinghub_field_type' );
					}else{
					$field_type['first_name']='text';
					$field_type['last_name']='text';
					$field_type['phone']='text';								
					$field_type['address']='text';
					$field_type['city']='text';
					$field_type['zipcode']='text';
					$field_type['country']='text';
					$field_type['listing_title']='text';
					$field_type['gender']='radio';
					$field_type['occupation']='text';
					$field_type['description']='textarea';
					$field_type['web_site']='url';					
				}		
				foreach ( $form_data as $field_key => $field_value ) { 
					if(strtolower(trim($field_key))!='wp_capabilities'){						
						if(is_array($field_value)){
							$field_value =implode(",",$field_value);
						}
						if($field_type[$field_key]=='url'){							
							update_user_meta($user_id, sanitize_text_field($field_key), sanitize_url($field_value)); 
							}elseif($field_type[$field_key]=='textarea'){
							update_user_meta($user_id, sanitize_text_field($field_key), sanitize_textarea_field($field_value));  
							}else{
							update_user_meta($user_id, sanitize_text_field($field_key), sanitize_text_field($field_value)); 
						}
					}
				}
				echo json_encode(array("code" => "success","msg"=>esc_html__( 'Updated Successfully', 'listinghub')));
				exit(0);
			}
		}
	}
$eplugins_listinghub_admin = new eplugins_listinghub_Admin();