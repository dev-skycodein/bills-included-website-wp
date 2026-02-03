<?php
namespace Elementor;

class Listinghub_Image_Carousel_Widget extends Widget_Base {

    public function get_name() {
        return 'listinghub_image_carousel';
    }

    public function get_title() {
        return esc_html__( 'Detail Page -> Image Carousel (Custom)', 'listinghub' );
    }

    public function get_categories() {
        return [ 'listinghub_elements' ];
    }

    protected function render() {
        // Custom logic to get the images from the custom post type
        $listinghub_directory_url = get_option('ep_listinghub_url');
        if ($listinghub_directory_url == "") {
            $listinghub_directory_url = 'listing';
        }

        if (isset($_GET['detail'])) {
            $post = get_page_by_path(sanitize_text_field($_GET['detail']), OBJECT, $listinghub_directory_url);
            if ($post) {
                $listingid = $post->ID;

                // Get the gallery images
                $gallery_ids = get_post_meta($listingid, 'image_gallery_ids', true);
                $gallery_ids_array = array_filter(explode(",", $gallery_ids));

                // Prepare images array in the format Elementor expects
                $images = [];
				
                foreach ($gallery_ids_array as $attachment_id) {
                    $images[] = [
                        'id' => $attachment_id,
                        'url' => wp_get_attachment_url($attachment_id),
                    ];
                }

                // Set the widget settings dynamically
                $this->add_render_attribute('_wrapper', [
                    'data-carousel' => json_encode($images),
                ]);

                // Override default settings with custom dynamic images
                $this->set_settings('carousel', $images);
				
                // Now call the parent render method to handle the carousel output
                parent::render();
            }
        }
		wp_enqueue_style('slick', ep_listinghub_URLPATH . 'admin/files/css/slick-slider.css');
	    wp_enqueue_script('slick', ep_listinghub_URLPATH . 'admin/files/js/slick-slider.min.js');	
		
		
		 ?>
		 <script>
		jQuery(document).ready(function() {
			jQuery('.carousel-class').slick({
				slidesToShow: 3,  // Number of slides to show at once
				slidesToScroll: 1, // Number of slides to scroll at once
				autoplay: true,    // Enable autoplay
				autoplaySpeed: 2000,  // Set autoplay speed
			});
		});
		 </script>
		 <?php
		 	wp_reset_query();
    }
}

// Register the custom widget
Plugin::instance()->widgets_manager->register_widget_type(new Listinghub_Image_Carousel_Widget());