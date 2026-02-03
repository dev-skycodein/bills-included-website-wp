<?php
	namespace Elementor;
	class listinghub_Registration_Widget extends Widget_Base {
		public function get_name() {
			return 'listinghub_registration';
		}
		public function get_title() {
			return esc_html__( 'Registration', 'listinghub' );
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
			$shortcode ="[listinghub_form_wizard]";		
		?>
		<div class="elementor-shortcode"><?php echo do_shortcode( shortcode_unautop( $shortcode ) );  ?></div>
		<?php
		}
	} 
Plugin::instance()->widgets_manager->register( new listinghub_Registration_Widget );