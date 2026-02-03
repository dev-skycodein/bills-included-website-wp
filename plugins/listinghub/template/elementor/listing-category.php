<?php
	
	namespace Elementor;
	class listinghub_Category_Widget extends Widget_Base {
		public function get_name() {
			return 'listinghub_category';
		}
		public function get_title() {
			return esc_html__( 'Category', 'listinghub' );
		}
		public function get_icon() {
			return 'eicon-post-excerpt';
		}
		public function get_categories() {
			return [ 'listinghub_elements' ];
		}
		protected function register_controls() {
			$this->start_controls_section(
			'cat_post_settings',
			[
			'label' => esc_html__( 'Category Settings', 'listinghub' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
			]
			);
			$this->add_control(
			'post_count_filter',
			[
			'label'   => esc_html__( 'Number Of Category To Show', 'listinghub' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => - 1,
			'max'     => '',
			'step'    => 1,
			'default' => 6,
			]
			);
			$this->add_control(
			'category_filter',
			[
			'label'       => esc_html__( 'Categories', 'listinghub' ),
			'type'        => Controls_Manager::SELECT2,
			'label_block' => true,
			'multiple'    => true,
			'options'     => ep_listinghub_post_categories_cat(),			
			]
			);

			$this->end_controls_section();
		
		
		}
		//Render
		protected function render() {
			$settings = $this->get_settings_for_display();		
			$atts='';
			if ( ! empty( $settings['category_filter'] ) ) {
				if(is_array($settings['category_filter'])){
					$atts=$atts.' category="'.implode(",",$settings['category_filter']).'"';
					}else{
					$atts=$atts.' category="'.$settings['category'].'"';
				}
			}
			if ( ! empty( $settings['post_count_filter'] ) ) {			
				$atts=$atts.' post_limit="'.$settings['post_count_filter'].'"';
			}
			
			$shortcode ="[listinghub_categories  ".$atts." ]";		
		?>
		<div class="elementor-shortcode"><?php echo do_shortcode( shortcode_unautop( $shortcode ) );  ?></div>
		<?php
		}
		
				
		
	}
Plugin::instance()->widgets_manager->register( new listinghub_Category_Widget );

//Post Category
	function ep_listinghub_post_categories_cat() {
		$options = array();
		$listinghub_directory_url=get_option('ep_listinghub_url');
		if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
		$taxonomy = $listinghub_directory_url.'-category';
		$args = array(
		'orderby'           => 'name',
		'order'             => 'ASC',
		'hide_empty'        => true,	
		);
		$terms = get_terms($taxonomy,$args);
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}
	