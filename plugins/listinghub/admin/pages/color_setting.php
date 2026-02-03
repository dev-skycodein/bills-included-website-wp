<?php
	$big_button_color=get_option('epjbdir_big_button_color');	
	if($big_button_color==""){$big_button_color='#2e7ff5';}	
?>
<form class="form-horizontal" role="form"  name='color_settings' id='color_settings'>	
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Big Button','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="big_button_color" id="big_button_color" value='<?php echo esc_attr($big_button_color); ?>' >			
		</div>
	</div>
	<?php
	$button_font_color=get_option('epjbdir_button_font_color');	
	if($button_font_color==""){$button_font_color='#fffff';}	
?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Font: Big Button','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input type="color" name="button_font_color" id="button_font_color" value='<?php echo esc_attr($button_font_color); ?>' >			
		</div>
	</div>
	
	<?php
	$small_button_color=get_option('epjbdir_small_button_color');	
	if($small_button_color==""){$small_button_color='#5f9df7';}	
?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Small Button','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="small_button_color" id="small_button_color" value='<?php echo esc_attr($small_button_color); ?>' >			
		</div>
	</div>
	<?php
	$button_font_color=get_option('epjbdir_button_small_font_color');	
	if($button_font_color==""){$button_font_color='#ffffff';}	
?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Font: Small Button','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="button_small_font_color" id="button_small_font_color" value='<?php echo esc_attr($button_font_color); ?>' >			
		</div>
	</div>
	
	
	
	<?php
	$icon_color=get_option('epjbdir_icon_color');	
	if($icon_color==""){$icon_color='#5b5b5b';}	
	?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Icon','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input type="color" name="icon_color" id="icon_color" value='<?php echo esc_attr($icon_color); ?>' >			
		</div>
	</div>
	
	<?php
	$title_color=get_option('epjbdir_title_color');	
	if($title_color==""){$title_color='#5b5b5b';}	
	?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Title','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="title_color" id="title_color" value='<?php echo esc_attr($title_color); ?>' >			
		</div>
	</div>
	<?php
	$content_font_color=get_option('epjbdir_content_font_color');	
	if($content_font_color==""){$content_font_color='#66789C';}	
	?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Content Font','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="content_font_color" id="content_font_color" value='<?php echo esc_attr($content_font_color); ?>' >			
		</div>
	</div>
		<?php
	$border_color=get_option('epjbdir_border_color');	
	if($border_color==""){$border_color='#E0E6F7';}	
	?>
	<div class="form-group row">
		<label  class="col-md-4 control-label"> <?php esc_html_e('Border','listinghub');  ?>		
		</label>
		<div class="col-md-8">																		
			<input  type="color" name="border_color" id="border_color" value='<?php echo esc_attr($border_color); ?>' >			
		</div>
	</div>
	
	<div class="row">
		
		<div class="col-md-12 col-12">
			<hr/>
			<div id="success_message_color_setting"></div>	
			<button type="button" onclick="return  listinghub_update_color_settings();" class="button button-primary"><?php esc_html_e( 'Update', 'listinghub' );?></button>
		</div>	
	</div>	
</form>