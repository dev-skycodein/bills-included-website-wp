"use strict";
var big_button_color =listinghub_color.big_button;
var small_button =listinghub_color.small_button;
var button_font =listinghub_color.button_font;
var title_color =listinghub_color.title_color;
var icon_color =listinghub_color.icon_color;
var content_color_font =listinghub_color.content_font_color;
var max_border_color =listinghub_color.max_border_color;
var button_small_font =listinghub_color.button_small_font; 

jQuery( function() { 	
		document.documentElement.style.setProperty('--btn-big-color', big_button_color);
		document.documentElement.style.setProperty('--btn-small-color', small_button);
		document.documentElement.style.setProperty('--btn-font-color', button_font);
		document.documentElement.style.setProperty('--btn-small-font-color', button_small_font);
		document.documentElement.style.setProperty('--title-color', title_color);
		document.documentElement.style.setProperty('--content-color', content_color_font);	
		document.documentElement.style.setProperty('--icon_color', icon_color);	
		document.documentElement.style.setProperty('--max-border-color', max_border_color);	
		
}); 