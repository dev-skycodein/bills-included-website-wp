<?php
	namespace Elementor;
	class listinghub_Search_Widget extends Widget_Base {
		public function get_name() {
			return 'listinghub_search';
		}
		public function get_title() {
			return esc_html__( 'Search Form', 'listinghub' );
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
			$shortcode ="[listinghub_search]";		
		?>
		<div class="elementor-shortcode"><?php echo do_shortcode( shortcode_unautop( $shortcode ) );  ?></div>
		<?php
		}
	}
Plugin::instance()->widgets_manager->register( new listinghub_Search_Widget );