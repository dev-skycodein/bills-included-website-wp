<?php
global 	$listinghub_directory_url;
$listinghub_directory_url=get_option('ep_listinghub_url');					
if($listinghub_directory_url==""){$listinghub_directory_url='listing';}	

wp_enqueue_script("jquery");	
wp_enqueue_style('fontawesome-browser', ep_listinghub_URLPATH . 'admin/files/css/fontawesome-browser.css');	
wp_enqueue_style('all-font-awesome', 			ep_listinghub_URLPATH . 'admin/files/css/fontawesome.css');


function listinghub_taxonomy_add_category_custom_field() {
    ?>	
	<div class="form-field ">
		<label for="cat-image"><?php esc_html_e('Select Icon','listinghub');?></label>
		<p>
		<input type="text" name="cat-icon"  id="caticoninput"  class="form-control" placeholder="<?php esc_html_e('Select Icon','listinghub');?>"  />
		 <a href="javascript:void(0);" onclick="listinghub_icon_uploader('caticoninput');"  class="button button-secondary"><?php esc_html_e('Upload Icon','listinghub');?></a>
		</p>
	</div>
	 <div class="form-field term-image-wrap">
        <label for="cat-marker"><?php esc_html_e('Map Marker','listinghub');?></label>
        <p><a href="#" class="aw_upload_image_button button button-secondary" id="upload_marker_btn"><?php esc_html_e('Upload Marker','listinghub');?></a></p>
        <input type="text" name="category_marker_url" id="category_marker_url"  value="" size="40" />
    </div>	
    <div class="form-field term-image-wrap">
        <label for="cat-image"><?php esc_html_e('Image[Best: 300 X 200px]','listinghub');?></label>		
        <p><a href="#" class="aw_upload_image_button button button-secondary" id="upload_image_btn"><?php esc_html_e('Upload Image','listinghub');?></a></p>
        <input type="text" name="category_image_url" id="category_image_url"  value="" size="40" />
    </div>	
 <?php
}
add_action( $listinghub_directory_url.'-category_add_form_fields', 'listinghub_taxonomy_add_category_custom_field', 10, 2 );
 
function listinghub_taxonomy_edit_category_custom_field($term) {
    $image = get_term_meta($term->term_id, 'listinghub_term_image', true);
	$caticon= get_term_meta($term->term_id, 'listinghub_term_icon', true);
	$map_marker= get_term_meta($term->term_id, 'listinghub_term_mapmarker', true);
    ?>
	 <tr class="form-field ">
        <th scope="row"><label ><?php esc_html_e('Select Icon','listinghub');?></label></th>
        <td>
		<p>
          <input type="text" name="cat-icon" id="caticoninputedit" value="<?php echo esc_attr($caticon); ?>"  class="form-control" placeholder="<?php esc_html_e('Select Icon','listinghub');?>"  />
		  <a href="javascript:void(0);" onclick="listinghub_icon_uploader('caticoninputedit');"  class="button button-secondary"><?php esc_html_e('Upload Icon Edit','listinghub');?></a></p>
          
        </td>
    </tr>
	 <tr class="form-field term-image-wrap">
        <th scope="row"><label for="category_marker_url"><?php esc_html_e('Map Marker','listinghub');?></label></th>
        <td>
            <p><a href="#" class="aw_upload_image_button button button-secondary" id="upload_marker_btn"><?php esc_html_e('Upload Map Marker','listinghub');?> </a>				
				<img src="<?php echo esc_url($map_marker); ?>" id="listinghub_term_marker_dis" width="100px">
			</p>			
			<br/>
            <input type="text" name="category_marker_url"  id="category_marker_url" value="<?php echo esc_url($map_marker); ?>" size="40" />
        </td>
    </tr>
	
	 <tr class="form-field term-image-wrap">
        <th scope="row"><label for="category_image_url"><?php esc_html_e('Image [Best: 300px X 200px]','listinghub');?></label></th>
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
add_action( $listinghub_directory_url.'-category_edit_form_fields', 'listinghub_taxonomy_edit_category_custom_field', 10, 2 );

// Save data
add_action('created_'.$listinghub_directory_url.'-category', 'listinghub_save_term_category_image', 10, 2);
function listinghub_save_term_category_image($term_id, $tt_id) {
    if (isset($_POST['category_image_url']) && '' !== $_POST['category_image_url']){
        $group = sanitize_url($_POST['category_image_url']);
        add_term_meta($term_id, 'listinghub_term_image', $group, true);
    }
	if (isset($_POST['category_marker_url']) && '' !== $_POST['category_marker_url']){
        $group = sanitize_url($_POST['category_marker_url']);
        add_term_meta($term_id, 'listinghub_term_mapmarker', $group, true);
    }
	
	if (isset($_POST['cat-icon']) && '' !== $_POST['cat-icon']){
        $caticon = sanitize_text_field($_POST['cat-icon']);
        add_term_meta($term_id, 'listinghub_term_icon', $caticon, true);
    }
	
}

///Now save the edited value
add_action('edited_'.$listinghub_directory_url.'-category', 'listinghub_update_image_upload_category', 10, 2);
function listinghub_update_image_upload_category($term_id, $tt_id) {
    if (isset($_POST['category_image_url']) && '' !== $_POST['category_image_url']){
        $group = sanitize_url($_POST['category_image_url']);
        update_term_meta($term_id, 'listinghub_term_image', $group);
    }
	if (isset($_POST['category_marker_url']) && '' !== $_POST['category_marker_url']){
		 $group = sanitize_url($_POST['category_marker_url']);
        update_term_meta($term_id, 'listinghub_term_mapmarker', $group);
    }
	
	if (isset($_POST['cat-icon']) && '' !== $_POST['cat-icon']){	
         $caticon = sanitize_text_field($_POST['cat-icon']);
         update_term_meta($term_id, 'listinghub_term_icon', $caticon);
    }
}

// Js add
function listinghub_image_uploader_enqueue_category() {
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
add_action( 'admin_enqueue_scripts', 'listinghub_image_uploader_enqueue_category' );