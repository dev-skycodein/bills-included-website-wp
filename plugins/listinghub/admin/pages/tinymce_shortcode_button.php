<?php	
function listinghub_loadMyBlock() {
  wp_enqueue_script(
    'listinghub-block',
    ep_listinghub_URLPATH . 'admin/files/js/gutenberg-block.js',
    array('wp-blocks','wp-editor'),
    true
  );
}
   
add_action('enqueue_block_editor_assets', 'listinghub_loadMyBlock');

// Block Category
function listinghub_filter_block_categories_when_post_provided( $block_categories, $editor_context ) {
    if ( ! empty( $editor_context->post ) ) {
        array_push(
            $block_categories,
            array(
                'slug'  => 'listinghub-category',
				'icon'  => 'dashicons-before dashicons-universal-access-alt',
                'title' => esc_html__( 'listinghub', 'listinghub' ),                
            )
        );
    }
    return $block_categories;
}
 
add_filter( 'block_categories_all', 'listinghub_filter_block_categories_when_post_provided', 10, 2 );
