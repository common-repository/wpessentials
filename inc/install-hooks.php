<?php

defined( 'ABSPATH' ) || exit;


/**
 * Create database tables.
 *
 * @since 2020.02.21
 * @since 2020.12.28 Added current charset and collate.
 * @since 2021.03.12 Removed irrelevant "guest" role.
 *
 * @global wpdb $wpdb
 */
function wpessentials_create_db_tables()
{
  global $wpdb;

  $collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

  $tables =
    "CREATE TABLE {$wpdb->prefix}wpessentials_bf_failed_logins (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(255),
      ipadress VARCHAR(255),
      -- attempts INT,
      stamp DATETIME -- DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $collate;
    CREATE TABLE {$wpdb->prefix}wpessentials_bf_lockouts (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(255),
      ipadress VARCHAR(255),
      -- lockouts INT,
      stamp DATETIME -- DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $collate;";

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  \dbDelta( $tables, true );
}


/**
 * Trigger disable hooks.
 *
 * @since 2020.02.24
 *
 * @global array $wpessentials_modules
 */
function wpessentials_run_disable_hooks()
{
  global $wpessentials_modules;

  foreach ( $wpessentials_modules as $module ) {
    do_action( 'wpessentials_' . $module['slug'] . '_on_disable' );
  }
}

/**
 * Initialize default settings.
 *
 * @since 2020.02.21
 * @since 2020.03.01 Updated the method to set default settings.
 * @since 2020.12.05 Only send an email when an existing user is locked out default true.
 * @since 2020.12.10 Added on_enable hooks to execute if module is enabled.
 * @since 2020.12.10 Added cronjobs.
 * @since 2020.12.28 Making sure the correct charset and collate is used.
 *
 * @global wpdb $wpdb
 */
function wpessentials_set_default_settings()
{
  global $wpdb;

  $collate = $wpdb->get_charset_collate();

  $wpdb->query( str_replace( 'CONVERT TO DEFAULT', 'CONVERT TO', "ALTER TABLE {$wpdb->prefix}wpessentials_bf_failed_logins CONVERT TO $collate;" ) );
  $wpdb->query( str_replace( 'CONVERT TO DEFAULT', 'CONVERT TO', "ALTER TABLE {$wpdb->prefix}wpessentials_bf_lockouts CONVERT TO $collate;" ) );

  wpessentials_run_disable_hooks();

  // Remove cronjobs.
  wp_clear_scheduled_hook( 'wpessentials_cronjobs_daily' );

  /**
   * Security.
   */
  update_option(
    'wpessentials_security',
    wp_parse_args(
      get_option( 'wpessentials_security', array() ),
      array(
        'protect_sys_files'                    => true,
        'force_ssl'                            => defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN,
        'bf_atts_user'                         => 7,
        'bf_atts_host'                         => 5,
        'bf_atts_period'                       => 15,
        'bf_lockout_period'                    => 45,
        'bf_email_lockout_event_existing_user' => true,
      )
    )
  );
  add_option( 'wpessentials_security_enabled', true );
  if ( get_option( 'wpessentials_security_enabled' ) ) {
    do_action( 'wpessentials_security_on_enable' );
  }

  /**
   * Development Mode.
   */
  update_option(
    'wpessentials_devmode',
    wp_parse_args(
      get_option( 'wpessentials_devmode', array() ),
      array(
        'debug'     => defined( 'WP_DEBUG' ) && WP_DEBUG,
        'logging'   => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
        'login_msg' => 'This website is being developed and is currently not accessible to the public.',
      )
    )
  );
  if ( get_option( 'wpessentials_devmode_enabled' ) ) {
    do_action( 'wpessentials_devmode_on_enable' );
  }

  /**
   * Login Form.
   */
  add_option( 'wpessentials_login_enabled', true );

  /**
   * Google Analytics.
   */
  update_option(
    'wpessentials_analytics',
    wp_parse_args(
      get_option( 'wpessentials_analytics', array() ),
      array(
        'anonymize'             => true,
        'snippet_location'      => 'header',
        'bypass_administrators' => true,
      )
    )
  );

  // Set cronjobs.
  wp_schedule_event( time(), 'daily', 'wpessentials_cronjobs_daily' );
}

/**
 * Delete all settings.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Triggering disable hooks moved to separate function.
 * @since 2020.12.10 Removed disable hooks trigger as this is done already by deactivation.
 * @since 2020.12.10 Added cronjobs removal.
 *
 * @global wpdb $wpdb
 */
function wpessentials_delete_all_settings()
{
  global $wpdb;

  // Remove cronjobs.
  wp_clear_scheduled_hook( 'wpessentials_cronjobs_daily' );

  // Remove settings from database.
  $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpessentials_%';" );

  // Remove tables from database.
  $wpdb->query( "DROP TABLE {$wpdb->prefix}wpessentials_bf_failed_logins;" );
  $wpdb->query( "DROP TABLE {$wpdb->prefix}wpessentials_bf_lockouts;" );
}
