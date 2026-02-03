<?php
	namespace Elementor;
	class listinghub_Add_new_listing_Widget extends Widget_Base {
		public function get_name() {
			return 'listinghub_add_new_listing';
		}
		public function get_title() {
			return esc_html__( 'Add Listing', 'listinghub' );
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
			$shortcode ="[listinghub_add_listing]";		
		?>
		<div class="elementor-shortcode"><?php echo do_shortcode( shortcode_unautop( $shortcode ) );  ?></div>
		<?php
		}
	}
Plugin::instance()->widgets_manager->register( new listinghub_Add_new_listing_Widget );