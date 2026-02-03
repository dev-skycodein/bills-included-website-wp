<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	class listinghub_Elementor_Custom_Widget {
		private static $instance = null;
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
		public function listinghub_add_elementor_custom_widgets() {
			require_once( 'listing-post-all.php' );
			require_once( 'listing-post-all-top-map.php' );
			require_once( 'listing-post-all-no-map.php' );
			require_once( 'listing-filter.php' );
			require_once( 'listing-featured.php' );
			require_once( 'listing-map.php' );			
			require_once( 'listing-search.php' );
			require_once( 'listing-category.php' );
			require_once( 'listing-location.php' );
			require_once( 'listing-login.php' );
			require_once( 'listing-add-new.php' );			
			require_once( 'listing-my-account.php' );
			require_once( 'listing-author-directory.php' );			
			require_once( 'listing-pricing-table.php' );
			require_once( 'listing-registration.php' );
			// Detail page widgets
			//require_once( 'detail-page/image.php' );
			//require_once( 'detail-page/image-carousel.php' );
			
			
		}
		public function init() {
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'listinghub_add_elementor_custom_widgets' ) );
		}
	}
	listinghub_Elementor_Custom_Widget::get_instance()->init();
	// Add New Category In Elementor Widget
	function listinghub_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
		'listinghub_elements',
		[
		'title' => esc_html__( 'listinghub Elements', 'listinghub' ),
		]
		);
	}
add_action( 'elementor/elements/categories_registered', 'listinghub_elementor_widget_categories', 20, 1  );