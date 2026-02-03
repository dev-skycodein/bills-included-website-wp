<style>
.custom-slider {
    display: flex;
    height: 380px;
    gap: 10px;
	position: relative;
}

.custom-slider .left-image {
    width: 70%;
}

.custom-slider .right-image {
    width: 30%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.custom-slider .left-image img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    border-radius: 5px;
}

.custom-slider .right-image img {height: 185px;width: 100%;object-fit: cover;border-radius: 5px;}

.custom-slider .custom-arrow {
    position: absolute;
    background: #fff;
    border: unset;
    height:40px;
    width: 40px;
    border-radius: 50%;
    font-size:18px;
    line-height: 0px;
}

.custom-slider .custom-arrow-left {
    top: 45%;
    margin: 0;
    left: 20px;
}

.custom-slider .custom-arrow-right {
    right: 20px;
    top: 45%;
}
.old-gal{
	display: none;
}
@media(max-width: 767px){
	.custom-slider{
		height: 230px;
	}
}
@media(max-width: 500px){
	.custom-slider{
		height: 200px;
	}
	.custom-slider .custom-arrow {
		height:30px;
		width: 30px;
		font-size: 16px;
	}
	.custom-slider .custom-arrow-left {
		top: 40%;
	}
	.custom-slider .custom-arrow-right {
		top: 40%;
	}
}

#imageModal {
    position: fixed;
    z-index: 9999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    padding: 20px;
    display: none; /* default hidden */
}

#imageModal.show {
    display: flex;
}

#imageModal img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
}

#closeModal {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 30px;
    color: white;
    cursor: pointer;
    z-index: 10000;
}
</style>
<div class="row custom-gallary">

	<div class="custom-slider">
		<input type="button" check-id="0" value="←" class="custom-arrow custom-arrow-left">
		<div class="left-image">
			<a id="anchor-image-1" href="#">
					<img id="slide-image-1" src="">
			</a>
		</div>
		<div class="right-image">
			<a id="anchor-image-2" href="#">
					<img id="slide-image-2" src="">
			</a>
			<a id="anchor-image-3" href="#">
					<img id="slide-image-3" src="">
			</a>
		</div>
		<input type="button" check-id="4" value="→" class="custom-arrow custom-arrow-right">
	</div>
	<div class="old-gal">
<?php
		$gallery_ids=get_post_meta($listingid ,'image_gallery_ids',true);
		$gallery_ids_array = array_filter(explode(",", $gallery_ids));
		$i=1;
		foreach($gallery_ids_array as $slide){
			if($slide!=''){ ?>
			<div class=" p-2 counter col-md-6 ">
				<a href="<?php echo wp_get_attachment_url( $slide ); ?>">
					<img class="img-fluid rounded float" data-image="<?=$i?>" src="<?php echo wp_get_attachment_url( $slide ); ?>" >
				</a>
			</div>
			<?php
				$i++;
			}
		}
		//image_gallery_urls
		$gallery_urls=get_post_meta($listingid ,'image_gallery_urls',true);
		$gallery_urls_array = array_filter(explode(",", $gallery_urls));
		foreach($gallery_urls_array as $slide){
			if($slide!=''){ ?>
			<div class="p-2 counter col-md-6">
				<a href="<?php echo esc_attr($slide); ?>">
					<img class="img-fluid rounded float" data-image="<?=$i?>" src="<?php echo esc_attr($slide); ?>">
				</a>
			</div>
			<?php
				$i++;
			}
		}
	?>
	</div>
	<div id="imageModal">
    <span id="closeModal">&times;</span>
    <img id="modalImage" src="" alt="Modal Image">
	</div>
</div>
<script>
	jQuery(document).ready(function($){
		let total_image = $(".custom-gallary .counter img").length;
		if(total_image < 3){
			$(".old-gal").css("display","flex");
			$(".custom-slider").hide();
		}

		let currentIndex = 0;

		// Function to update the slider images
		function updateSlider(index) {
			let i1 = index % total_image;
			let i2 = (index + 1) % total_image;
			let i3 = (index + 2) % total_image;

			const img1 = $(".custom-gallary .counter img[data-image='" + (i1 + 1) + "']").attr("src");
			const img2 = $(".custom-gallary .counter img[data-image='" + (i2 + 1) + "']").attr("src");
			const img3 = $(".custom-gallary .counter img[data-image='" + (i3 + 1) + "']").attr("src");

			$("#slide-image-1").attr("src", img1);
			$("#slide-image-2").attr("src", img2);
			$("#slide-image-3").attr("src", img3);

			$("#anchor-image-1").attr("href", img1);
			$("#anchor-image-2").attr("href", img2);
			$("#anchor-image-3").attr("href", img3);
		}

		// Initial setup
		updateSlider(currentIndex);

		$('#anchor-image-1, #anchor-image-2, #anchor-image-3').on('click', function(e) {
			e.preventDefault();
			let src = $(this).attr('href');
			if (src) {
				$('#modalImage').attr('src', src);
				$('#imageModal').addClass('show');
			}
		});

		// Close modal when clicking the close button
		$('#closeModal').on('click', function() {
			$('#imageModal').removeClass('show');
			$('#modalImage').attr('src', ''); // Clear image to prevent flash
		});

		// Close modal when clicking outside the image
		$('#imageModal').on('click', function(e){
			if (e.target.id === 'imageModal') {
				$('#imageModal').removeClass('show');
				$('#modalImage').attr('src', '');
			}
		});

		// Right arrow click
		$(".custom-arrow-right").click(function() {
			currentIndex = (currentIndex + 1) % total_image;
			updateSlider(currentIndex);
		});

		// Left arrow click
		$(".custom-arrow-left").click(function() {
			// Handle wraparound (e.g., -1 becomes last)
			currentIndex = (currentIndex - 1 + total_image) % total_image;
			updateSlider(currentIndex);
		});
	});
</script>