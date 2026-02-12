<?php
add_action( 'wp_enqueue_scripts', 'suqat_enqueue_styles' );
function suqat_enqueue_styles() {
  $parent_style = 'suqat-style';
  wp_enqueue_style( $parent_style, get_template_directory_uri() . '/assets/css/styles.css', array('themify-icons', 'flaticon', 'bootstrap', 'animate','owl-carousel','owl-theme', 'slick', 'slick-theme','owl-transitions','fancybox','fancybox') );
  wp_enqueue_style( 'suqat-child',
      get_stylesheet_directory_uri() . '/style.css',
      array( $parent_style ),
      wp_get_theme()->get('Version')
    );
}
if( ! function_exists( 'suqat_child_theme_language_setup' ) ) {
  function suqat_child_theme_language_setup(){
    load_theme_textdomain( 'suqat-child', get_template_directory() . '/languages' );
  }
  add_action('after_setup_theme', 'suqat_child_theme_language_setup');
}
