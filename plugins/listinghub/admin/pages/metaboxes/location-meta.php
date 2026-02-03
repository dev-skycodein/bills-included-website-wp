<?php
global 	$listinghub_directory_url;
$listinghub_directory_url=get_option('ep_listinghub_url');					
if($listinghub_directory_url==""){$listinghub_directory_url='listing';}	

	
function listinghub_taxonomy_add_custom_field() {
    ?>
    <div class="form-field term-image-wrap">
        <label for="cat-image"><?php esc_html_e('Image[Best: 250px X 380px]','listinghub');?></label>
        <p><a href="#" class="aw_upload_image_button button button-secondary" id="upload_image_btn"><?php esc_html_e('Upload Image','listinghub');?></a></p>
        <input type="text" name="category_image_url" id="category_image_url"  value="" size="40" />
    </div>
    <?php
}
add_action( $listinghub_directory_url.'-locations_add_form_fields', 'listinghub_taxonomy_add_custom_field', 10, 2 );
 
function listinghub_taxonomy_edit_custom_field($term) {
    $image = get_term_meta($term->term_id, 'listinghub_term_image', true);
    ?>
    <tr class="form-field term-image-wrap">
        <th scope="row"><label for="category_image_url"><?php esc_html_e('Image [Best: 250px X 380px]','listinghub');?></label></th>
        <td>
            <p><a href="#" class="aw_upload_image_button button button-secondary" id="upload_image_btn"><?php esc_html_e('Upload Image','listinghub');?> </a>
				
				<img src="<?php echo esc_url($image); ?>" id="listinghub_term_image_dis" width="100px">
			</p>
			
			<br/>
            <input type="text" name="category_image_url"  id="category_image_url" value="<?php echo esc_url($image); ?>" size="40" />
        </td>
    </tr>
    <?php
}
add_action( $listinghub_directory_url.'-locations_edit_form_fields', 'listinghub_taxonomy_edit_custom_field', 10, 2 );

// Save data
add_action('created_'.$listinghub_directory_url.'-locations', 'listinghub_save_term_image', 10, 2);
function listinghub_save_term_image($term_id, $tt_id) {
    if (isset($_POST['category_image_url']) && '' !== $_POST['category_image_url']){
        $group = sanitize_url($_POST['category_image_url']);
        add_term_meta($term_id, 'listinghub_term_image', $group, true);
    }
}

///Now save the edited value
add_action('edited_'.$listinghub_directory_url.'-locations', 'listinghub_update_image_upload', 10, 2);
function listinghub_update_image_upload($term_id, $tt_id) {
    if (isset($_POST['category_image_url']) && '' !== $_POST['category_image_url']){
        $group = sanitize_url($_POST['category_image_url']);
        update_term_meta($term_id, 'listinghub_term_image', $group);
    }
}

// Js add
function listinghub_image_uploader_enqueue() {
    global $typenow,$listinghub_directory_url;	
    if( ($typenow == $listinghub_directory_url) ) { 
		wp_enqueue_media();
        wp_register_script( 'listinghub_meta-image', ep_listinghub_URLPATH . 'admin/files/js/meta-media-uploader.js', array( 'jquery' ) );
        wp_localize_script( 'listinghub_meta-image', 'meta_image',
            array(
                'title' => 'Upload an Image',
                'button' => 'Use this Image',
            )
        );
        wp_enqueue_script( 'listinghub_meta-image' );
    }
}
add_action( 'admin_enqueue_scripts', 'listinghub_image_uploader_enqueue' );