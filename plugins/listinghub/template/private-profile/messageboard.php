<div class="border-bottom pb-15 mb-3 toptitle-sub"><?php esc_html_e('Message', 'listinghub'); ?>
</div>
<?php
	$current_user = wp_get_current_user();
	$args = array(
	'post_type' => 'listinghub_message', 
	'post_status' => 'private',
	'posts_per_page'=> '-1',
	'orderby' => 'date',
	'order'   => 'DESC',
	);
	$user_to = array(
	'relation' => 'AND',
	array(
	'key'     => 'user_to',
	'value'   => $current_user->ID,
	'compare' => '='
	),
	);			
	$args['meta_query'] = array(
	$user_to,
	);
	$the_query = new WP_Query( $args );
?>
<table id="all-bookmark" class="table tbl-epmplyer-bookmark" >
	<thead>
		<tr class="">
			<th><?php  esc_html_e('Message','listinghub');?></th>
		</tr>
	</thead>
	<?php
		$i=0;
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) : $the_query->the_post();
				$id          = get_the_ID();
				$email       = get_post_meta( $id, 'from_email', true );
				$phone       = get_post_meta( $id, 'from_phone', true );
				$dir_url_raw = get_post_meta( $id, 'dir_url', true );
				$dir_url     = $dir_url_raw ? esc_url( $dir_url_raw ) : '';
				$date_label  = get_the_time( 'M d, Y h:m a', $id );
		?>
		<tr id="companybookmark_<?php echo esc_html( trim( $id ) ); ?>" >
			<td class="d-md-table-cell">
				<div class="listing-item bookmark">
					<div class="row align-items-center">
						<div class="col-md-12 listing-info px-0">
							<div class="text px-0 text-left">
								<span class="toptitle-sub"><?php echo esc_html( $the_query->post->post_title ); ?></span>
								<div class="table-content">
									<span class="location">
										<i class="fas fa-calendar-day mr-2"></i><?php echo esc_html( $date_label ); ?>
									</span>
								</div>
								<div class="location">
									<span class="location">
										<i class="far fa-envelope mr-2"></i><?php echo esc_html( $email ); ?>
									</span>
									<i class="fas fa-phone-volume mr-2"></i><?php esc_html_e( 'Phone', 'listinghub' ); ?> :
									<?php echo esc_html( $phone ); ?>
								</div>
								<?php if ( $dir_url ) : ?>
									<div class="table-content mt-2">
										<?php esc_html_e( 'Listing', 'listinghub' ); ?> :
										<a href="<?php echo $dir_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $dir_url; ?></a>
									</div>
								<?php endif; ?>
								<div class="table-content mt-2">
									<?php
									// Render message body inline.
									echo do_shortcode( $the_query->post->post_content );
									?>
								</div>
							</div>
							<div class="text-right">
								<button
									type="button"
									class="btn btn-small lh-message-view-btn"
									data-message-id="<?php echo esc_attr( $id ); ?>"
								>
									<i class="far fa-eye"></i>
								</button>
								<button
									type="button"
									class="btn btn-small"
									onclick="listinghub_delete_message_myaccount('<?php echo esc_attr( $id ); ?>','companybookmark')"
								>
									<i class="far fa-trash-alt"></i>
								</button>
							</div>
						</div>
					</div>
				</div>

				<?php
				// Hidden block with structured details for the popup viewer.
				?>
				<div id="lh-msg-details-<?php echo esc_attr( $id ); ?>" class="lh-msg-details" style="display:none;">
					<p><strong><?php esc_html_e( 'Subject', 'listinghub' ); ?>:</strong> <?php echo esc_html( get_the_title( $id ) ); ?></p>
					<p><strong><?php esc_html_e( 'Date', 'listinghub' ); ?>:</strong> <?php echo esc_html( $date_label ); ?></p>
					<p>
						<strong><?php esc_html_e( 'Email', 'listinghub' ); ?>:</strong>
						<?php if ( $email ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'N/A', 'listinghub' ); ?>
						<?php endif; ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Phone', 'listinghub' ); ?>:</strong>
						<?php echo $phone ? esc_html( $phone ) : esc_html__( 'N/A', 'listinghub' ); ?>
					</p>
					<?php if ( $dir_url ) : ?>
						<p>
							<strong><?php esc_html_e( 'Listing', 'listinghub' ); ?>:</strong>
							<a href="<?php echo $dir_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $dir_url; ?></a>
						</p>
					<?php endif; ?>
					<p>
						<strong><?php esc_html_e( 'Message', 'listinghub' ); ?>:</strong><br />
						<?php echo do_shortcode( $the_query->post->post_content ); ?>
					</p>
				</div>
			</td>
		</tr>
		<?php
			endwhile;
		}
	?>
</table>

<div id="lh-message-modal" style="display:none; position:fixed; z-index:9999; inset:0; background:rgba(0,0,0,0.5);">
	<div style="background:#fff; max-width:640px; margin:40px auto; padding:20px; border-radius:4px; position:relative;">
		<button type="button" class="lh-message-modal-close" style="position:absolute; top:10px; right:10px; border:none; background:transparent; font-size:18px; cursor:pointer;">
			&times;
		</button>
		<h3 style="margin-top:0;"><?php esc_html_e( 'Enquiry details', 'listinghub' ); ?></h3>
		<div id="lh-message-modal-body"></div>
	</div>
</div>

<script type="text/javascript">
	// Ensure ajaxurl is available on the front-end (for admin-ajax).
	if (typeof ajaxurl === 'undefined') {
		var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
	}

	(function($){
		'use strict';

		function lhShowMessageModal(html) {
			$('#lh-message-modal-body').html(html || '');
			$('#lh-message-modal').fadeIn(150);
		}

		function lhHideMessageModal() {
			$('#lh-message-modal').fadeOut(150, function () {
				$('#lh-message-modal-body').empty();
			});
		}

		$(document).on('click', '.lh-message-view-btn', function (e) {
			e.preventDefault();
			var messageId = $(this).data('message-id');
			if (!messageId) {
				if (window.console) {
					console.warn('lh-message-view-btn: missing messageId', this);
				}
				return;
			}
			if (window.console) {
				console.log('lh-message-view-btn: click', { messageId: messageId, ajaxurl: ajaxurl });
			}
			var details = $('#lh-msg-details-' + messageId).html() || '';
			lhShowMessageModal(details);

			// Log this as a "manage enquiries" activity for analytics (message actually viewed).
			$.post(ajaxurl, {
				action: 'listinghub_agency_message_view',
				message_id: messageId
			})
				.done(function (resp) {
					if (window.console) {
						console.log('listinghub_agency_message_view: success', resp);
					}
				})
				.fail(function (xhr, status, error) {
					if (window.console) {
						console.error('listinghub_agency_message_view: AJAX error', status, error, xhr && xhr.responseText);
					}
				});
		});

		$(document).on('click', '.lh-message-modal-close', function (e) {
			e.preventDefault();
			lhHideMessageModal();
		});

		$(document).on('click', '#lh-message-modal', function (e) {
			if (e.target === this) {
				lhHideMessageModal();
			}
		});
	})(jQuery);
</script>