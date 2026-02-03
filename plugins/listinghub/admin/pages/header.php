<?php
	$main_class = new eplugins_listinghub;
	wp_enqueue_style('admin-listinghub', ep_listinghub_URLPATH . 'admin/files/css/admin.css');
	wp_enqueue_style('dataTables', ep_listinghub_URLPATH . 'admin/files/css/vue-admin.css');
?>	
<div class="bootstrap-wrapper">
	<div class=" container-fluid">	
		<div class="listinghub-admin-header row">
			<div class="listinghub-admin-header-logo">
				<img src="<?php echo ep_listinghub_URLPATH."assets/images/admin-logo.png";?>" alt="listinghub Logo">
				<span class="listinghub-admin-header-version"><?php echo esc_html($main_class->version); ?></span>
			</div>
			<div class="listinghub-admin-header-menu">
				<div class="menu-item">
					<div class="menu-icon">
						<i class="fa-solid fa-question"></i>
						<div class="dropdown">
							<h3><?php  esc_html_e('Get Help','listinghub'); ?></h3>
							<div class="list-item">  
							
							 <a href="<?php echo esc_url('https://www.youtube.com/playlist?list=PLLRcfoNnzUb6z_9jEWVqw4XjPbhZ2E-LD');?>" target="_blank">
									<span class="listinghub-icon">
										<i class="fa-brands fa-youtube"></i>
									</span>
									<?php  esc_html_e('Video Tutorial','listinghub'); ?>
								</a>
								
								<a href="<?php echo esc_url('https://e-plugins.com/support/');?>" target="_blank">
									<span class="listinghub-icon">
										<i class="fa-regular fa-comments"></i>
									</span>
									<?php  esc_html_e('Get Support','listinghub'); ?>
								</a>
								<a href="<?php echo esc_url('https://help.eplug-ins.com/listinghub');?>" target="_blank">
									<div class="listinghub-icon">
										<i class="fa-solid fa-file-lines"></i>
									</div>
									<?php  esc_html_e('Documentation','listinghub'); ?>
								</a>
								
								<a href="#" target="_blank">
									<div class="listinghub-icon">
										<i class="fa-regular fa-comments"></i>
									</div>
								<?php  esc_html_e('FAQ','listinghub'); ?>
								</a>
								<a href="<?php echo esc_url('https://listinghub.e-plugins.com/request-a-feature/');?>" target="_blank">
									<div class="listinghub-icon">
										<i class="fa-regular fa-lightbulb"></i>
									</div>
								<?php  esc_html_e('Request a Feature  ','listinghub'); ?>                      </a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>