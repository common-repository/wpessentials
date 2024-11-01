<?php

defined( 'ABSPATH' ) || exit;


/**
 * Class for handling brute-force.
 *
 * @since 2020.02.21
 * @since 2020.02.26 Added minimum history period.
 * @since 2020.03.16 Added constants for email notification purposes and IP tracking hyperlink.
 */
class WPessentials_BF
{
  /**
   * The history period in seconds.
   *
   * @var int
   */
  public const HISTORY_PERIOD = 2419200; // 28 days

  /**
   * Get default footer for email notifications.
   *
   * @since 2020.03.16
   */
  public static function get_footer_msg()
  {
    return sprintf(
      __(
        'You receive this email because you have opted-in to be alerted when a lockout occurs.<br>
        If you wish to opt-out, <a href="%1$s">log-in</a> to your website and uncheck the option.<br>
        If you are not the intended recipient of this message, please <a href="mailto:%2$s">contact</a> the website administrator.<br>
        <br>Thank you for using Essentials!',
        'wpessentials'
      ),
      admin_url( 'options-general.php?page=wpessentials_security' ),
      get_option( 'admin_email' )
    );
  }

  /**
   * Get IP tracking hyperlink.
   *
   * @since 2020.03.16
   * @since 2020.03.26 Bugfix in lockout table where "null" was passed to generate ip adress link for non-ip lockout entries.
   */
  public static function get_ip_tracking_link( $ipadress )
  {
    if ( ! is_string( $ipadress ) ) {
      return '';
    }
    return '<a href="https://iplocation.io/ip/' . $ipadress . '" target="_blank" rel="noopener noreferrer">' . esc_html( $ipadress ) . '</a>';
  }

  /**
   * Database tables.
   *
   * @var string
   */
  private const _DB_LOGIN_ATTEMPTS = 'wpessentials_bf_failed_logins';
  private const _DB_LOCKOUTS       = 'wpessentials_bf_lockouts';

  /**
   * Remove lockout events that ran out of time.
   *
   * @since 2020.02.21
   * @since 2020.02.26 Added minimum history period.
   *
   * @global wpdb $wpdb
   */
  public static function cleanup_records()
  {
    global $wpdb;

    $db_login_attempts = $wpdb->prefix . self::_DB_LOGIN_ATTEMPTS;
    $db_lockouts       = $wpdb->prefix . self::_DB_LOCKOUTS;

    $setting = wpessentials_get_option( array( 'wpessentials_security' ) );

    /**
     * Filter the history period in seconds.
     *
     * @since 2020.02.26
     * @since 2020.12.10 Sanitize minimum and default values.
     */
    $history_period = apply_filters( 'wpessentials_bf_history_period', self::HISTORY_PERIOD );

    $atts_period          = max( 1, intval( $setting['bf_atts_period'] ?? 15 ) ); // DEFAULT VALUE !!
    $atts_thres_timestamp = time() - max( $atts_period * 60, $history_period );
    $atts_threshold       = gmdate( 'Y-m-d H:i:s', $atts_thres_timestamp );

    $lockout_period          = max( 1, intval( $setting['bf_lockout_period'] ?? 45 ) ); // DEFAULT VALUE !!
    $lockout_thres_timestamp = time() - max( $lockout_period * 60, $history_period );
    $lockout_threshold       = gmdate( 'Y-m-d H:i:s', $lockout_thres_timestamp );

    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$db_login_attempts}
        WHERE
        stamp < %s
        ;",
        $atts_threshold
      )
    );

    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$db_lockouts}
        WHERE
        stamp < %s
        ;",
        $lockout_threshold
      )
    );
  }

  /**
   * Release a lockout by removing the record from the lockouts table.
   *
   * @since 2020.03.14
   *
   * @global wpdb $wpdb
   *
   * @param int $id Lockout record ID.
   *
   * @return bool Whether the release was performed successfully.
   */
  public static function release_lockout( int $id )
  {
    global $wpdb;

    $db_lockouts = $wpdb->prefix . self::_DB_LOCKOUTS;

    $results = $wpdb->query(
      $wpdb->prepare(
        'DELETE FROM %s
        WHERE ID = %d;',
        $db_lockouts,
        $id
      )
    );

    if (
      $results !== null &&
      count( $results ) > 0
    ) {
      return true;
    }

    return false;
  }

  /**
   * Return brute-force events.
   *
   * @since 2020.03.14 Derived from `get_failed_login_events()` and `get_lockout_events()`, fixed bug regarding too less `wpdb::prepare()` arguments.
   *
   * @global wpdb $wpdb
   *
   * @param string $table The table to query.
   * @param array  $args {
   *   Optional. Pass a username and/or IP adress for the lookup.
   *  @type string $username A username
   *  @type string $ipadress An IP adress
   * }
   *
   * @return array|object|null Database query results.
   */
  private static function _query_bf_table( string $table, array $args = array() )
  {
    global $wpdb;

    $table = $wpdb->prefix . $table;

    $where_username = isset( $args['username'] ) ? $wpdb->prepare(
      '(
        username = %s AND
        username IS NOT NULL
      )',
      $args['username']
    ) : '';

    $where_ipadress = isset( $args['ipadress'] ) ? $wpdb->prepare(
      '(
        ipadress = %s AND
        ipadress IS NOT NULL
      )',
      $args['ipadress']
    ) : '';

    return $wpdb->get_results(
      "SELECT * FROM {$table}" .
      (
        isset( $args['username'] ) ||
        isset( $args['ipadress'] )
        ? '
        WHERE ' : ''
      ) .
      $where_username .
      (
        isset(
          $args['username'],
          $args['ipadress']
        ) ? ' OR ' : ''
      ) .
      $where_ipadress .
      '
      ORDER BY
      stamp DESC
      ;'
      // string $output = OBJECT
    );
  }

  /**
   * Return failed login events.
   *
   * @since 2020.02.21
   * @since 2020.03.14 Moved similar code to `_query_bf_table()`..
   *
   * @param array $args {
   *  Optional. Pass a username and/or IP adress for the lookup.
   *  @type string $username A username
   *  @type string $ipadress An IP adress
   * }
   *
   * @return array|object|null Database query results.
   */
  public static function get_failed_login_events( array $args = array() )
  {
    return self::_query_bf_table( self::_DB_LOGIN_ATTEMPTS, $args );
  }

  /**
   * Add a failed login event (and lockout event).
   *
   * @since 2020.02.21
   * @since 2020.03.14 Improved.
   *
   * @global wpdb $wpdb
   *
   * @param array $args {
   *  Optional. Pass a username and/or IP adress for the event.
   *  @type string $username A username
   *  @type string $ipadress An IP adress
   * }
   */
  public static function add_failed_login_event( array $args = array() )
  {
    global $wpdb;

    $args['ipadress'] = $args['ipadress'] ?? $_SERVER['REMOTE_ADDR'];
    $args['stamp']    = gmdate( 'Y-m-d H:i:s' );

    /**
     * Add new failed login event to database.
     */
    $db_login_attempts = $wpdb->prefix . self::_DB_LOGIN_ATTEMPTS;
    $wpdb->insert( "{$db_login_attempts}", $args );

    /**
     * Filter whether the failed login event should be registered.
     * This is meant for whitelist purposes.
     *
     * @since 2020.02.21
     * @since 2020.12.10 Sanitize minimum and default values.
     */
    if ( ! apply_filters( 'wpessentials_register_failed_login_event', true, $args ) ) {
      return; }

    /**
     * Check if login attempts exceed the maximum allowed, if so, add a lockout event.
     */
    $events = self::get_failed_login_events( $args );
    if ( $events !== null ) {
      $setting = wpessentials_get_option( array( 'wpessentials_security' ) );

      $atts_period          = max( 1, intval( $setting['bf_atts_period'] ?? 15 ) ); // DEFAULT VALUE !!
      $atts_thres_timestamp = time() - $atts_period * 60;
      $atts_threshold       = gmdate( 'Y-m-d H:i:s', $atts_thres_timestamp );

      $attempts_per_user = intval( $setting['bf_atts_user'] ?? -1 );
      $attempts_per_host = intval( $setting['bf_atts_host'] ?? -1 );

      $user_events = array();
      if ( isset( $args['username'] ) ) {
        $user_events = array_filter(
          $events,
          function ( $event ) use ( $args, $atts_threshold )
          {
            return (
              strtolower( $event->username ) === strtolower( $args['username'] ) &&
              $event->stamp >= $atts_threshold
            );
          }
        );
      }

      $host_events = array();
      if ( isset( $args['ipadress'] ) ) {
        $host_events = array_filter(
          $events,
          function ( $event ) use ( $args, $atts_threshold )
          {
            return (
              $event->ipadress === $args['ipadress'] &&
              $event->stamp >= $atts_threshold
            );
          }
        );
      }

      /**
       * Add new USER LOCKOUT event to database.
       */
      if (
        $attempts_per_user >= 0 &&
        count( $user_events ) > $attempts_per_user
      ) {
        $db_lockouts = $wpdb->prefix . self::_DB_LOCKOUTS;
        $wpdb->insert(
          "{$db_lockouts}",
          array(
            'username' => $args['username'],
            'stamp'    => $args['stamp'],
          )
        );
        /**
         * Fire action hook on USER LOCKOUT event.
         *
         * @since 2020.02.21
         */
        do_action( 'wpessentials_user_lockout_event', $args );
      }

      /**
       * Add new HOST LOCKOUT event to database.
       */
      if (
        (
          ! empty( $setting['bf_lockout_admin_atts'] ) &&
          isset( $args['username'] ) &&
          strtolower( $args['username'] ) === 'admin' ||
          strtolower( $args['username'] ) === 'administrator'
        ) ||
        (
          $attempts_per_host >= 0 &&
          count( $host_events ) > $attempts_per_host
        )
      ) {
        $db_lockouts = $wpdb->prefix . self::_DB_LOCKOUTS;
        $wpdb->insert(
          "{$db_lockouts}",
          array(
            'ipadress' => $args['ipadress'],
            'stamp'    => $args['stamp'],
          )
        );
        /**
         * Fire action hook on HOST LOCKOUT event.
         *
         * @since 2020.02.21
         */
        do_action( 'wpessentials_host_lockout_event', $args );
      }
    }
  }

  /**
   * Return lockout events.
   *
   * @since 2020.02.21
   * @since 2020.03.14 Moved similar code to `_query_bf_table()`.
   *
   * @param array $args {
   *  Optional. Pass a username and/or IP adress for the lookup.
   *  @type string $username A username
   *  @type string $ipadress An IP adress
   * }
   *
   * @return array|object|null Database query results.
   */
  public static function get_lockout_events( array $args = array() )
  {
    return self::_query_bf_table( self::_DB_LOCKOUTS, $args );
  }

  /**
   * Return active lockouts.
   *
   * @since 2020.03.14
   * @since 2020.12.10 Sanitize minimum and default values.
   *
   * @return array|object|null Database query results.
   */
  public static function get_lockouts()
  {
    $events = self::get_lockout_events();
    if ( $events !== null ) {
      $setting = wpessentials_get_option( array( 'wpessentials_security' ) );

      $lockout_period          = max( 1, intval( $setting['bf_lockout_period'] ?? 45 ) ); // DEFAULT VALUE !!
      $lockout_thres_timestamp = time() - $lockout_period * 60;
      $lockout_threshold       = gmdate( 'Y-m-d H:i:s', $lockout_thres_timestamp );

      $attempts_per_user = intval( $setting['bf_atts_user'] ?? -1 );
      $attempts_per_host = intval( $setting['bf_atts_host'] ?? -1 );

      return array_filter(
        $events,
        function ( $event ) use ( $lockout_threshold, $attempts_per_user, $attempts_per_host )
        {
          return (
            (
              (
                $attempts_per_user >= 0 &&
                isset( $event->username )
              ) ||
              (
                $attempts_per_host >= 0 &&
                isset( $event->ipadress )
              )
            ) && $event->stamp >= $lockout_threshold
          );
        }
      );
    }

    return null;
  }

  /**
   * Check whether a user or host is locked out.
   *
   * @since 2020.02.21
   * @since 2020.03.14 Logic partially moved to function `get_lockouts()`.
   *
   * @param array $args {
   *  Optional. Pass a username and/or IP adress for the check.
   *  @type string $username A username
   *  @type string $ipadress An IP adress
   * }
   *
   * @return bool Whether a username or ipadress is locked out.
   */
  public static function is_locked_out( array $args = array() )
  {
    $args['ipadress'] = $args['ipadress'] ?? $_SERVER['REMOTE_ADDR'];

    $is_locked_out = false;

    $lockouts = self::get_lockouts();

    if ( $lockouts !== null ) {
      $matches = array_filter(
        $lockouts,
        function ( $event ) use ( $args )
        {
          return (
            (
              isset( $args['username'] ) &&
              strtolower( $args['username'] ) === strtolower( $event->username )
            ) ||
            (
              isset( $args['ipadress'] ) &&
              $args['ipadress'] === $event->ipadress
            )
          );
        }
      );

      if ( count( $matches ) > 0 ) {
        /**
         * User or host is locked out ..
         */
        $is_locked_out = true;
      }
    }

    /**
     * Filter whether user/host is locked out.
     * This is meant for blacklist and whitelist purposes.
     *
     * @since 2020.02.21
     *
     * @param bool $is_locked_out Whether the user/host should be locked out.
     */
    return apply_filters( 'wpessentials_is_locked_out', $is_locked_out, $args );
  }

  /**
   * Perform a lockout for the current request.
   *
   * @since 2020.02.21
   */
  public static function do_lockout()
  {
    @header( 'HTTP/1.1 403 Forbidden' );
    @header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
    @header( 'Pragma: no-cache' );
    @header( 'Expires: Thu, 22 Jun 1978 00:28:00 GMT' );
    add_filter(
      'wp_die_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    add_filter(
      'wp_die_ajax_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    add_filter(
      'wp_die_json_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    add_filter(
      'wp_die_jsonp_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    add_filter(
      'wp_die_xml_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    add_filter(
      'wp_die_xmlrpc_handler',
      function ()
      {
        return '_scalar_wp_die_handler';
      }
    );
    wp_die(
      __( 'You are locked out.', 'wpessentials' ), // $message
      '', // $title
      array(
        'response' => 403,
        'exit'     => true, // default is "true", but just to ensure ..
      ) // $args
    );
  }
}
