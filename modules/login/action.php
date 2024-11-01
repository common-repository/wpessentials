<?php

defined( 'ABSPATH' ) || exit;


/**
 * Always a good idea to replace the credits with those of the blog.
 *
 * @since 2020.02.21
 */
add_filter(
  'login_headerurl',
  function ()
  {
    return esc_url( home_url() );
  }
);
add_filter(
  'login_headertext',
  function ()
  {
    return get_bloginfo( 'name' );
  }
);


/**
 * Replace the image above the login form.
 *
 * @since 2020.02.21
 */
add_action(
  'login_enqueue_scripts',
  function ()
  {
    if ( $setting = wpessentials_get_option( array( 'wpessentials_login', 'logo_id' ) ) ) {
      $image_src = wp_get_attachment_image_src(
        intval( $setting ), // $attachment_id
        'full' // $size
      );
      if ( $image_src[1] > 320 ) {
        $size = array( 320, $image_src[2] * ( 320 / $image_src[1] ) );
      } else {
        $size = array( $image_src[1], $image_src[2] );
      }
      ?>
      <style type="text/css">
        #login h1 a {
          <?php
          if ( $image_src ) :
            ?>
            background-image: url('<?php echo $image_src[0]; ?>');
            background-repeat: no-repeat;
            background-size: <?php echo $size[0]; ?>px <?php echo $size[1]; ?>px;
            width: <?php echo $size[0]; ?>px;
            height: <?php echo $size[1]; ?>px;
            <?php
          endif;
          ?>
        }
      </style>
      <?php
    }
  }
);
