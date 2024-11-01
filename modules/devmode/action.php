<?php

defined( 'ABSPATH' ) || exit;


/**
 * Make front-end available for logged-in users only.
 *
 * @since 2020.02.21
 */
if ( wpessentials_get_option( array( 'wpessentials_devmode', 'disable_frontend' ) ) ) {
  /**
   * Redirect logged-out users to the login page.
   *
   * @since 2020.02.21
   * @since 2020.03.05 Bugfix: `home_url()` was also used to check on admin urls, now `site_url()` is used.
   * @since 2021.10.20 Improved and bugfixed devmode disable front-end logic using whitelist and checking against both `site_url()` and `home_url()` variants.
   */
  add_action(
    'init',
    function ()
    {
      $whitelist = array(
        wp_login_url(),
        admin_url( 'admin-ajax.php' ),
        get_privacy_policy_url(),
      );
      array_walk(
        $whitelist,
        function ( &$url )
        {
          $url = rtrim( explode( '?', $url )[0], '/' );
        }
      );
      $requested_uri      = rtrim( explode( '?', $_SERVER['REQUEST_URI'] )[0], '/' );
      $requested_site_url = rtrim( site_url(), '/' ) . $requested_uri;
      $requested_home_url = rtrim( home_url(), '/' ) . $requested_uri;

      if (
        $_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']
        && ! is_admin() // request is not for an admin page
        && ! is_user_logged_in() // user is not logged-in
        && $requested_uri !== '/wp-login.php'
        && ! in_array( $requested_site_url, $whitelist, true )
        && ! in_array( $requested_home_url, $whitelist, true )
      ) {
        auth_redirect();
        exit;
      }
    }
  );

  /**
   * Display a message instead of the homepage hyperlink.
   *
   * @since 2020.02.21
   */
  add_action(
    'login_footer',
    function ()
    {
      if (
      $message = wpessentials_get_option( array( 'wpessentials_devmode', 'login_msg' ) )
      ) :
        ?>
      <script>
        document.querySelector('body.login div#login p#backtoblog').innerHTML =
          '<strong style="white-space: pre-wrap;"><?php echo esc_js( esc_html( $message ) ); ?></strong>';
      </script>
        <?php
    else :
      ?>
      <style>
        body.login div#login p#backtoblog {
          display: none !important;
        }
      </style>
      <?php
    endif;
    }
  );
}

/**
 * Print SQL queries in footer.
 *
 * @since 2020.12.14
 */
if ( wpessentials_get_option( array( 'wpessentials_devmode', 'savequeries' ) ) ) {
  add_action(
    'wp_footer',
    function ()
    {
      if ( current_user_can( 'manage_options' ) ) {
        global $wpdb;
        echo '<pre style="position:relative;z-index:9999;color:#fff;background-color:#000;font-family: monospace;font-size: 10pt;">';
        print_r( $wpdb->queries );
        echo '</pre>';
      }
    }
  );
}
