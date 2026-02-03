"use strict";

jQuery( document ).ready(function() { 
jQuery(".grid").imagesLoaded(function() {
    jQuery(".grid").masonry({
      itemSelector: ".grid-item"
    });
  });
});  