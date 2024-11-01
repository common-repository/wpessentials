<?php

defined( 'ABSPATH' ) || exit;


/**
 * Register setting sections and fields (admin_init).
 *
 * @since 2020.02.21
 *
 * @link https://developer.wordpress.org/plugins/settings/
 */
function wpessentials_devmode_admin_init( array $module )
{
  /**
   * The module's main section.
   *
   * @since 2020.02.21
   */
  add_settings_section(
    'wpessentials_devmode_section', // $id*
    __( 'Development Mode', 'wpessentials' ), // $title*
    function () use ( $module )
    {
      ?>
      <p class="description">
        <?php echo $module['desc']; ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_devmode' // $page*
  );

  /**
   * Debugging.
   *
   * @since 2020.02.21
   * @since 2020.02.24 Toggling `WP_DEBUG` now also toggles `WP_DISABLE_FATAL_ERROR_HANDLER` and `ini_set('display_errors', 'On'|'Off')`.
   * @since 2020.06.17 Retrieve debug.log filepath depending on WP_DEBUG_LOG value.
   * @since 2020.12.14 Added SAVEQUERIES option.
   */
  wpessentials_add_settings_field(
    'wpessentials_devmode_debug_field', // $id*
    __( 'Debugging', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * Enable debug.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'debug' ),
          // 'value' => defined( 'WP_DEBUG' ) && WP_DEBUG,
          'label_for' => 'wpessentials_devmode_debug',
          'desc'      => __( 'Enable debug', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Display all PHP errors, notices and warnings by setting <a href="https://wordpress.org/support/article/debugging-in-wordpress/#wp_debug" target="_blank" rel="noopener noreferrer">WP_DEBUG</a> and <a href="https://wordpress.org/support/article/editing-wp-config-php/#wp_disable_fatal_error_handler" target="_blank" rel="noopener noreferrer">WP_DISABLE_FATAL_ERROR_HANDLER</a> "true".',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Enable logging.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'logging' ),
          // 'value' => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
          'label_for' => 'wpessentials_devmode_logging',
          'desc'      => __( 'Enable logging', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
      <?php
      printf(
        __(
          'Log all PHP errors, notices and warnings to <a href="%s" target="_blank" rel="noopener noreferrer">wp-content/debug.log</a> instead of displaying them,<br>
          by setting <a href="https://wordpress.org/support/article/debugging-in-wordpress/#wp_debug_log" target="_blank" rel="noopener noreferrer">WP_DEBUG_LOG</a> "true" and <a href="https://wordpress.org/support/article/debugging-in-wordpress/#wp_debug_display" target="_blank" rel="noopener noreferrer">WP_DEBUG_DISPLAY</a> "false".',
          'wpessentials'
        ),
        esc_html( WPessentials_Debug_Log_Read::get_debug_log_path()['url'] )
      );
      ?>
      </p>

      <br>
      <?php
      /**
       * Save queries.
       *
       * @since 2020.12.14
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'savequeries' ),
          // 'value' => defined( 'SAVEQUERIES' ) && SAVEQUERIES,
          'label_for' => 'wpessentials_devmode_savequeries',
          'desc'      => __( 'Save queries', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Saves the database queries to an array by setting <a href="https://wordpress.org/support/article/debugging-in-wordpress/#savequeries" target="_blank" rel="noopener noreferrer">SAVEQUERIES</a> "true"<br>
          and displays it in the front-end footer for logged in administrators to help analyze those queries.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_devmode', // $page*
    'wpessentials_devmode_section', // $section
    array(
      'setting' => 'wpessentials_devmode',
    ) // $args
  );

  /**
   * Disable front-end.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_devmode_disable_frontend_field', // $id*
    __( 'Disable front-end', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * Disable front-end.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'disable_frontend' ),
          'label_for' => $args['label_for'],
          'desc'      => __( 'Make front-end accessible for logged-in users only', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Logged-out visitors are redirected to the login page.<br>
          Search engines are discouraged from indexing this site.<br>
          <strong>The Privacy Policy page will always be accessible.</strong>',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Login message.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting' => array( $args['setting'], 'login_msg' ),
        ),
        'textarea'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'If front-end is accessible for logged-in users only,<br>
          this is shown below the login form, instead of the homepage hyperlink.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_devmode', // $page*
    'wpessentials_devmode_section', // $section
    array(
      'setting'   => 'wpessentials_devmode',
      'label_for' => 'wpessentials_devmode_disable_frontend',
    ) // $args
  );
}

/**
 * Append some file contents after the submit button.
 *
 * @since 2020.12.10
 */
add_action(
  'wpessentials_devmode_after_submit_button',
  function ()
  {
    /**
     * Current wp-config.php contents.
     *
     * @since 2020.12.10
     */
    /*
    ?><pre><code><br><?php echo esc_html( ( new WPessentials_WP_Config_Edit() )->contents ); ?></code></pre><?php
    */

    /**
     * Current debug.log contents.
     *
     * @since 2020.12.10
     */
    if ( file_exists( WPessentials_Debug_Log_Read::get_debug_log_path()['path'] ) ) :
      ?>
  <div>
    <p>
        <?php _e( 'debug.log file contents:', 'wpessentials' ); ?>
      (<a
        onclick="wpessentials_clear_debug_log(this, '<?php echo esc_js( admin_url( 'admin-ajax.php?action=wpessentials_clear_debug_log' ) ); ?>')"
        ><?php _e( 'clear', 'wpessentials' ); ?></a>)
    </p>
      <pre><code><br><?php echo esc_html( ( new WPessentials_Debug_Log_Read() )->contents ); ?></code></pre>
  </div>
      <?php
  endif;
  }
);


/**
 * Enqueue styles and scripts ..
 *
 * @since 2020.02.21
 */
function wpessentials_devmode_enqueue_scripts()
{
  wp_enqueue_script(
    'wpessentials_devmode_admin_scripts', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-devmode-scripts.js', // $src
    array( 'jquery' ), // $deps
    WPESSENTIALS_PLUGIN_VERSION // $ver
  );
}


/**
 * Sanitize callback.
 *
 * @since 2020.02.21
 *
 * @link https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
 */
function wpessentials_devmode_sanitize_cb( array $args )
{
  $args = wpessentials_sanitize( $args, 'login_msg', 'textarea' );

  return $args;
}


/**
 * AJAX hook to clear the debug log file.
 *
 * @since 2020.02.21
 * @since 2020.06.17 Delete debug.log file.
 */
add_action(
  'wp_ajax_wpessentials_clear_debug_log',
  function ()
  {
    if (
      ! wp_doing_ajax() ||
      ! is_user_logged_in()
    ) {
      wp_die(
        '',
        '',
        array(
          'response' => 401,
          'exit'     => true,
        )
      );
    }

    wp_die(
      '',
      '',
      array(
        'response' => ( unlink( WPessentials_Debug_Log_Read::get_debug_log_path()['path'] ) ? 200 : 409 ),
        'exit'     => true,
      )
    );
  }
);


/**
 * Update wp-config.php according to settings.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Toggling `WP_DEBUG` now also toggles `WP_DISABLE_FATAL_ERROR_HANDLER` and `ini_set('display_errors', 'On'|'Off')`.
 * @since 2020.02.24 Settings are now reversed on failure.
 * @since 2020.03.01 Checking if performed successfully now only required when calling `write()`.
 * @since 2020.12.14 Less aggressive development setup if logging is enabled to support logging for production sites with less performance impact.
 *
 * @return bool Whether the updates succeeded.
 */
function wpessentials_devmode_update_wp_config()
{
  $setting = wpessentials_get_option( array( 'wpessentials_devmode' ) );

  $wp_config_editor = new WPessentials_WP_Config_Edit();

  /**
   * Update contents of wp-config.php.
   */
  $wp_config_editor->set_php_ini( 'display_errors', ! empty( $setting['debug'] ) && empty( $setting['logging'] ) );

  $wp_config_editor->set_constant( 'WP_ENVIRONMENT_TYPE', ! empty( $setting['debug'] ) && empty( $setting['logging'] ) ? 'development' : null );
  $wp_config_editor->set_constant( 'WP_DEBUG', ! empty( $setting['debug'] ) );
  $wp_config_editor->set_constant( 'WP_DISABLE_FATAL_ERROR_HANDLER', ! empty( $setting['debug'] ) );

  $wp_config_editor->set_constant( 'SCRIPT_DEBUG', ! empty( $setting['debug'] ) && empty( $setting['logging'] ) );

  $wp_config_editor->set_constant( 'WP_DEBUG_LOG', ! empty( $setting['debug'] ) && ! empty( $setting['logging'] ) );
  $wp_config_editor->set_constant( 'WP_DEBUG_DISPLAY', ! empty( $setting['debug'] ) && empty( $setting['logging'] ) );

  $wp_config_editor->set_constant( 'SAVEQUERIES', ! empty( $setting['debug'] ) && ! empty( $setting['savequeries'] ) );

  /**
   * Write changes to wp-config.php, reverse settings on failure.
   */
  if ( ! $wp_config_editor->write() ) {
    $setting['debug']       = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $setting['logging']     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $setting['savequeries'] = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

    update_option( 'wpessentials_devmode', $setting );

    return false;
  }

  return true;
}


/**
 * On enable hook.
 *
 * @since 2020.02.21
 */
add_action(
  'wpessentials_devmode_on_enable',
  function ()
  {
    wpessentials_devmode_update_wp_config();

    if ( ! wpessentials_get_option( array( 'wpessentials_devmode', 'disable_frontend' ) ) ) {
      update_option( 'blog_public', true );
    } else {
      update_option( 'blog_public', false );
    }
  }
);

/**
 * On disable hook.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Updated defaults.
 */
add_action(
  'wpessentials_devmode_on_disable',
  function ()
  {
    $wp_config_editor = new WPessentials_WP_Config_Edit();

    $wp_config_editor->set_php_ini( 'display_errors', null );

    $wp_config_editor->set_constant( 'WP_ENVIRONMENT_TYPE', null );
    $wp_config_editor->set_constant( 'WP_DEBUG', null );
    $wp_config_editor->set_constant( 'WP_DISABLE_FATAL_ERROR_HANDLER', null );

    $wp_config_editor->set_constant( 'SCRIPT_DEBUG', null );

    $wp_config_editor->set_constant( 'WP_DEBUG_LOG', null );
    $wp_config_editor->set_constant( 'WP_DEBUG_DISPLAY', null );

    $wp_config_editor->set_constant( 'SAVEQUERIES', null );

    $wp_config_editor->write();

    update_option( 'blog_public', true );
  }
);
