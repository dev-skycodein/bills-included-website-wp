<?php
	namespace Elementor;
	class listinghub_Image_Widget extends Widget_Base {
		public function get_name() {
			return 'listinghub_detail_page_image';
		}
		public function get_title() {
			return esc_html__( 'Detail Page -> Image', 'listinghub' );
		}
		public function get_icon() {
			return 'eicon-post-excerpt';
		}
		public function get_categories() {
			return [ 'listinghub_elements' ];
		}
		protected function register_controls() {
		}
		//Render
		protected function render() {
		 if (isset($_GET['detail'])) {
			$listinghub_directory_url=get_option('ep_listinghub_url');
			if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
			
			$post = get_page_by_path($_GET['detail'], OBJECT, $listinghub_directory_url);
			if ($post) {
				$listingid =$post->ID;
			}
			if (has_post_thumbnail($listingid)) {
                    // Display the featured image 
                    echo get_the_post_thumbnail($listingid, 'full');
                } else {
                    // Fallback if no featured image is set
                    echo '<img src="' . esc_url(ep_listinghub_URLPATH . '/assets/images/default-directory.jpg') . '" alt="' . esc_attr__('Default Image', 'listinghub') . '">';
                }
		 }
		
		}
	} 
Plugin::instance()->widgets_manager->register( new listinghub_Image_Widget );