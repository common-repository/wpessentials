<?php

defined( 'ABSPATH' ) || exit;


/**
 * Disable the built-in file editor in the dashboard.
 *
 * @since 2020.02.21
 */
if ( wpessentials_get_option( array( 'wpessentials_security', 'disable_file_editor' ) ) ) {
  define( 'DISALLOW_FILE_EDIT', true );
}


/**
 * Check if the current request should be locked out.
 *
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * @since 2020.02.21
 */
add_action(
  'init',
  function ()
  {
    // echo '[init]';
    if ( WPessentials_BF::is_locked_out() ) {
      WPessentials_BF::do_lockout();
    }
  }
);

/**
 * Filters whether a set of user login credentials are valid.
 *
 * @since 2020.02.21
 */
add_filter(
  'authenticate',
  function ( $user, $username )
  {
    // echo '[authenticate]';
    if ( WPessentials_BF::is_locked_out( array( 'username' => $username ) ) ) {
      return new WP_Error(
        'wpessentials_lockout', // $code
        __( 'You are locked out.', 'wpessentials' ) // $message
      );
    }
    return $user;
  },
  30,
  2
);

/**
 * Fires after a user login has failed to register the failed login event.
 *
 * @since 2020.02.21
 * @since 2020.02.26 Only add failed login event if the current request is not yet locked out.
 */
add_action(
  'wp_login_failed',
  function ( $username )
  {
    // echo '[wp_login_failed]';
    if ( ! WPessentials_BF::is_locked_out( array( 'username' => $username ) ) ) {
      WPessentials_BF::add_failed_login_event( array( 'username' => $username ) );
    } else {
      add_filter(
        'login_errors',
        function ()
        {
          return __( 'You are locked out.', 'wpessentials' ); // $message
        }
      );
    }
    WPessentials_BF::cleanup_records();
  }
);

/**
 * Send email on lockout event.
 *
 * @since 2020.02.26
 * @since 2020.03.14 Return early if email should only be send for lockouts of existing users but user does not exist.
 */
if ( wpessentials_get_option( array( 'wpessentials_security', 'bf_email_lockout_event' ) ) ) {
  $security_setting = wpessentials_get_option( array( 'wpessentials_security' ) );
  $mailto           = empty( $security_setting['bf_email_lockout_event_to'] ) ? get_option( 'admin_email' ) : $security_setting['bf_email_lockout_event_to'];

  $headers = array( 'Content-Type: text/html; charset=UTF-8' );

  /**
   * On USER lockout.
   */
  add_action(
    'wpessentials_user_lockout_event',
    function ( $args ) use ( $security_setting, $mailto, $headers )
    {
      /**
       * Return early if email should only be send for lockouts of existing users but user does not exist.
       */
      if (
        ! empty( $security_setting['bf_email_lockout_event_existing_user'] ) &&
        count( get_users( array( 'login' => $args['username'] ) ) ) === 0
      ) {
        return;
      }

      $message =
        'User "' . $args['username'] . '" has been locked out from your website <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a> due to too many failed login attempts.<br><br>' .
        WPessentials_BF::get_footer_msg();

      wp_mail( $mailto, 'User lockout event at ' . get_bloginfo( 'name' ), $message, $headers );
    }
  );

  /**
   * On HOST lockout.
   */
  add_action(
    'wpessentials_host_lockout_event',
    function ( $args ) use ( $security_setting, $mailto, $headers )
    {
      /**
       * Return early if email should only be send for lockouts of existing users.
       */
      if ( ! empty( $security_setting['bf_email_lockout_event_existing_user'] ) ) {
        return;
      }

      $message =
        'Host ' . WPessentials_BF::get_ip_tracking_link( $args['ipadress'] ) . ' has been locked out from your website <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a> due to too many failed login attempts.<br><br>' .
        WPessentials_BF::get_footer_msg();

      wp_mail( $mailto, 'Host lockout event at ' . get_bloginfo( 'name' ), $message, $headers );
    }
  );
}


/**
 * Disable XML-RPC.
 *
 * @since 2020.02.21
 * @since 2020.03.30 Simplified.
 */
if ( wpessentials_get_option( array( 'wpessentials_security', 'disable_xml_rpc' ) ) ) {
  add_filter( 'xmlrpc_enabled', '__return_null', PHP_INT_MAX );
}

/**
 * Disable REST API.
 *
 * @since 2020.02.21
 * @since 2020.03.30 Simplified and improved and now fully disables the REST API for non-authorized requests.
 */
if ( wpessentials_get_option( array( 'wpessentials_security', 'rest_api_restriction' ) ) ) {
  if ( version_compare( get_bloginfo( 'version' ), '4.4', '<' ) ) {
    return;
  }

  /**
   * Remove REST API info from head and headers.
   */
  remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd', PHP_INT_MAX );
  remove_action( 'wp_head', 'rest_output_link_wp_head', PHP_INT_MAX );
  remove_action( 'template_redirect', 'rest_output_link_header', PHP_INT_MAX );

  /**
   * Disable jsonp.
   */
  add_filter( 'rest_jsonp_enabled', '__return_false', PHP_INT_MAX );

  /**
   * For versions of WordPress <4.7, disable the REST API via filters.
   */
  if ( version_compare( get_bloginfo( 'version' ), '4.7', '<' ) ) {
    add_filter( 'rest_enabled', '__return_false', PHP_INT_MAX );
  }

  /**
   * WordPress >=4.7 disables the REST API via authentication short-circuit.
   */
  else {
    add_filter(
      'rest_authentication_errors',
      function ( $access )
      {
        if ( is_user_logged_in() ) {
          return $access;
        }
        $error_message = esc_html( 'Only authenticated users can access the REST API.' );
        if ( is_wp_error( $access ) ) {
          $access->add( 'rest_cannot_access', $error_message, array( 'status' => rest_authorization_required_code() ) );
          return $access;
        }
        return new WP_Error( 'rest_cannot_access', $error_message, array( 'status' => rest_authorization_required_code() ) );
      },
      PHP_INT_MAX
    );
  }
}
