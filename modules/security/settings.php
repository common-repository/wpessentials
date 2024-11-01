<?php

defined( 'ABSPATH' ) || exit;


/**
 * Register setting sections and fields (admin_init).
 *
 * @since 2020.02.21
 *
 * @link https://developer.wordpress.org/plugins/settings/
 *
 * @ignore ADD SERVER FILE/FOLDER PERMISSIONS CHECK !!
 */
function wpessentials_security_admin_init( array $module )
{
  /**
   * The module's main section.
   *
   * @since 2020.02.21
   */
  add_settings_section(
    'wpessentials_security_section', // $id*
    __( 'Security', 'wpessentials' ), // $title*
    function () use ( $module )
    {
      ?>
      <p class="description">
        <?php echo $module['desc']; ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security' // $page*
  );

  /**
   * Regenerate secret keys.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_security_generate_secret_keys', // $id*
    __( 'Secret Keys and Salts', 'wpessentials' ), // $title*
    function ()
    {
      ?>
      <input
      type="button"
      class="button button-secondary"
      value="<?php esc_attr_e( 'Regenerate', 'wpessentials' ); ?>"
      onclick="wpessentials_generate_secret_keys('<?php echo esc_js( admin_url( 'admin-ajax.php?action=wpessentials_generate_secret_keys' ) ); ?>')"
      />
      <p class="description">
        <?php
        _e(
          'Secret keys make your site harder to successfully attack by adding random elements to the password (<a href="https://wordpress.org/support/article/editing-wp-config-php/#security-keys" target="_blank" rel="noopener noreferrer">Security Keys</a>).<br>
          You can regenerate these at any point in time to invalidate all existing cookies. <strong>This will force all users to have to log in again!</strong>',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'class' => 'wpessentials-toggle-ignore',
    ) // $args
  );

  /**
   * Protection.
   *
   * @since 2020.12.10
   */
  wpessentials_add_settings_field(
    'wpessentials_security_protection_field', // $id*
    __( 'Protection', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * Protect system files.
       *
       * @since 2020.12.10
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'protect_sys_files' ),
          'label_for' => 'wpessentials_security_protect_sys_files',
          'desc'      => __( 'Protect system files', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Denies public access to critical system files such as<br>
          .htaccess, log files, install.php, wp-config.php and readme\'s.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Disable dir browsing.
       *
       * @since 2020.12.10
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'disable_dir_browsing' ),
          'label_for' => 'wpessentials_security_disable_dir_browsing',
          'desc'      => __( 'Disable directory browsing', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Prevents public users from seeing a list of files in a directory.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Disable PHP in uploads.
       *
       * @since 2020.12.10
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'disable_php_upload_dir' ),
          'label_for' => 'wpessentials_security_disable_php_upload_dir',
          'desc'      => __( 'Disable PHP execution in uploads directory' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Blocks requests to maliciously uploaded PHP files in the uploads directory.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'setting' => 'wpessentials_security',
    ) // $args
  );

  /**
   * Reduce spam.
   *
   * @since 2020.12.10
   */
  wpessentials_add_settings_field(
    'wpessentials_security_reduce_spam_field', // $id*
    __( 'Reduce spam', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * No useragent, no post.
       *
       * @since 2020.12.10
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'no_useragent_no_post_request' ),
          'label_for' => 'wpessentials_security_no_useragent_no_post_request',
          'desc'      => __( 'No UserAgent, no POST request' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Denies POST requests by blank user-agents.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * No referer, no comment.
       *
       * @since 2020.12.10
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'no_referer_no_comment' ),
          'label_for' => 'wpessentials_security_no_referer_no_comment',
          'desc'      => __( 'No referer, no comment' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Denies any comment attempt with a blank HTTP_REFERER field.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'setting' => 'wpessentials_security',
    ) // $args
  );

  /**
   * Miscellaneous.
   *
   * @since 2020.02.21
   * @since 2020.12.14 Added force SSL.
   */
  wpessentials_add_settings_field(
    'wpessentials_security_miscellaneous_field', // $id*
    __( 'Miscellaneous', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * Disable file editor.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'disable_file_editor' ),
          'label_for' => 'wpessentials_security_disable_file_editor',
          'desc'      => __( 'Disable file editor', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Disables the file editor for plugins and themes<br>
          requiring users to have access to the file system to modify files.',
          'wpessentials'
        );
        ?>
      </p>

      <?php
      if ( ! function_exists( 'wp_is_https_supported' ) || wp_is_https_supported() ) {
        echo '<br>';
        /**
         * Force SSL.
         *
         * @since 2020.12.14
         * @since 2021.07.26 For newer WordPress versions, this option is only displayed if https is supported.
         */
        wpessentials_print_input_tag(
          array(
            'setting'   => array( $args['setting'], 'force_ssl' ),
            'value'     => wpessentials_get_option( array( $args['setting'], 'force_ssl' ) ) && wpessentials_wp_is_using_https(),
            'label_for' => 'wpessentials_security_force_ssl',
            'desc'      => __( 'Force SSL', 'wpessentials' ),
          ),
          'checkbox'
        );
        ?>
        <p class="description">
          <?php
          _e(
            'Force a secure SSL (HTTPS) connection.<br>
            <strong>Only check this if you have an up and running SSL certificate!</strong><br>
            Updates "siteurl" and "home" options to their "https://" equivalent and sets <a href="https://wordpress.org/support/article/administration-over-ssl/" target="_blank" rel="noopener noreferrer">FORCE_SSL_ADMIN</a> "true".',
            'wpessentials'
          );
          ?>
        </p>
        <?php
      }
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'setting' => 'wpessentials_security',
    ) // $args
  );

  /**
   * WordPress endpoints.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_security_endpoints_field', // $id*
    __( 'Endpoints', 'wpessentials' ), // $title*
    function ( $args )
    {
      /**
       * Disable REST API.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'rest_api_restriction' ),
          'label_for' => 'wpessentials_security_rest_api_restriction',
          'desc'      => __( 'Disable REST API', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'This means that all requests will require a logged in user,<br>
          blocking public requests for potentially-private data.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Disable XML-RPC.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'disable_xml_rpc' ),
          'label_for' => 'wpessentials_security_disable_xml_rpc',
          'desc'      => __( 'Disable XML-RPC' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Highly recommended if Jetpack, the WordPress mobile app,<br>
          pingbacks and other services that use XML-RPC are not used.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'setting' => 'wpessentials_security',
    ) // $args
  );

  /**
   * Brute-force.
   *
   * @since 2020.02.21
   * @since 2020.02.26 Added "bf_email_lockout_event(_to)" settings.
   * @since 2020.03.14 Added "bf_email_lockout_event_existing_user" setting.
   * @since 2020.03.14 Overview of users that have the same value for "user_nicename" or "display_name" as their "login_name".
   * @since 2020.03.16 IP adress in lockouts table now links to the IP tracking website.
   * @since 2020.03.16 Replaced timestamp column in lockouts table for a "since" and "time left" column.
   * @since 2020.03.30 Support to update "display_name" for users where its value is equal to their "login_name".
   * @since 2020.12.10 Added minimum number value attributes.
   */
  wpessentials_add_settings_field(
    'wpessentials_security_bf_field', // $id*
    __( 'Brute-force protection', 'wpessentials' ), // $title*
    function ( $args )
    {
      $setting = wpessentials_get_option( array( $args['setting'] ) );

      /**
       * Risky user display names table ..
       */
      $users = array_filter(
        get_users( array() ),
        function ( $user )
        {
          return (
            strcasecmp( $user->user_login, $user->display_name ) === 0 ||
            strcasecmp( $user->user_login, $user->user_nicename ) === 0
          );
        }
      );
      if (
        count( $users ) > 0
      ) :
        ?>
        <div id="wpessentials_user_public_names_div" class="wpessentials_table" style="display:none;">
        <span class="notice notice-warning" style="display: inline-block;">
        <?php
        _e(
          'The below user(s) have the same "user_nicename" or "display_name" as their "login_name", meaning these usernames are revealed publicly.<br>
          Consider changing their "display names" to something different than their "usernames" in order to avoid brute-force attacks with valid usernames.<br>
          When updating user data, Essentials makes sure "user_nicename" also updates according to the value for "display_name".',
          'wpessentials'
        );
        ?>
        </span>
        <table id="wpessentials_user_public_names_table"></table>
        <script>
          // Initiate the users table.
          wpessentials_print_user_public_names_table(<?php echo json_encode( wpessentials_get_publicly_revealed_usernames_users() ); ?>)
        </script>
        <br>
        <input
        type="button"
        class="button button-secondary"
        value="<?php esc_attr_e( 'Update users', 'wpessentials' ); ?>"
        onclick="wpessentials_update_user_public_names(this, '<?php echo esc_js( admin_url( 'admin-ajax.php?action=wpessentials_update_user_public_names' ) ); ?>')"
        /><span class="wpessentials-loader" style="margin-left: 1em;"></span>
        <br>
        <br>
        </div>
        <?php
      endif; // count( $users ) > 0

      /**
       * Lockouts table ..
       */
      $lockouts = WPessentials_BF::get_lockouts();
      if (
        $lockouts !== null &&
        count( $lockouts ) > 0
      ) :
        ?>
        <span class="notice notice-warning" style="display: inline-block;">
        <?php
        _e( 'There are active lockouts at this moment:', 'wpessentials' );
        ?>
        </span>
        <div class="wpessentials_table">
        <table id="wpessentials_lockouts_table">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>IP adress</th>
            <th style="text-align: right;">Since [min]</th>
            <th style="text-align: right;">Time left [min]</th>
          </tr>
          <?php
          $lockout_period = intval( $setting['bf_lockout_period'] ?? 0 );
          foreach ( $lockouts as $event ) {
            $lockout_record_onclick =
              'wpessentials_release_lockout(this, \'' .
              esc_js( admin_url( 'admin-ajax.php?action=wpessentials_release_lockout' ) ) .
              '\', ' . $event->id . ')';

            $event_time  = strtotime( $event->stamp );
            $event_since = floor( ( time() - $event_time ) / 60 );
            $event_until = ceil( ( $event_time + $lockout_period * 60 - time() ) / 60 );

            echo '<tr>';
            echo '<td class="lockout_id"><a onclick="' . $lockout_record_onclick . '">
                    <span class="wpessentials-red-text" title="Click to release lockout.">X</span><span>' . esc_html( $event->id ) . '</span></a></td>';
            echo '<td>' . esc_html( $event->username ) . '</td>';
            echo '<td>' . WPessentials_BF::get_ip_tracking_link( $event->ipadress ) . '</td>';
            echo '<td style="text-align: right;">' . esc_html( $event_since ) . '</td>';
            echo '<td style="text-align: right;">' . esc_html( $event_until ) . '</td>';
            echo '</tr>';
          }
          ?>
        </table>
        </div>
        <?php
      else : // $lockouts ?
        ?>
        <span class="notice notice-success" style="display: inline-block;">
        <?php
          _e( 'No active lockouts at this moment.', 'wpessentials' );
        ?>
        </span>
        <?php
      endif; // $lockouts ?

      ?>
      <br>
      <p class="description">
        <?php
        /**
         * Get the maximum history period in days.
         *
         * @since 2020.03.01
         */
        $history_period_days = max(
          ceil( ( $setting['bf_atts_period'] ?? 0 ) / 1440 ),
          ceil( ( $setting['bf_lockout_period'] ?? 0 ) / 1440 ),
          ceil( apply_filters( 'wpessentials_bf_history_period', WPessentials_BF::HISTORY_PERIOD ) / 86400 )
        );
        ?>
        <strong>
        <?php
        printf(
          __( 'To protect your legitimate interests, lockouts and failed login attempts are stored in the database for %s days.', 'wpessentials' ),
          esc_html( $history_period_days )
        );
        ?>
        </strong>
      </p>

      <br>
      <?php
      /**
       * Attempts per user.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'bf_atts_user' ),
          'value'     => $setting['bf_atts_user'] ?? -1,
          'label_for' => 'wpessentials_security_bf_atts_user',
          'desc'      => __( 'attempts per user.', 'wpessentials' ),
          'attr'      => array( 'min' => -1 ),
        ),
        'number'
      );
      ?>

      <br>
      <?php
      /**
       * Attempts per host.
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'bf_atts_host' ),
          'value'     => $setting['bf_atts_host'] ?? -1,
          'label_for' => 'wpessentials_security_bf_atts_host',
          'desc'      => __( 'attempts per host.', 'wpessentials' ),
          'attr'      => array( 'min' => -1 ),
        ),
        'number'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'Set the amount of incorrect login attempts that are allowed before a user or host is being locked out.<br>
          Set to "-1" to allow infinite attempts, i.e. disable brute-force.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <?php
      /**
       * Lockout "admin*".
       *
       * @since 2020.02.21
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'bf_lockout_admin_atts' ),
          'label_for' => 'wpessentials_security_bf_lockout_admin_atts',
          'desc'      => __( 'Immediately lockout hosts attempting "admin" or "administrator" as username', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>

      <br>
      <br>
      <p>
        <label for="wpessentials_security_bf_atts_period"
        ><?php _e( 'Attempt period', 'wpessentials' ); ?></label>

        <br>
        <?php
        /**
         * Attempt period.
         *
         * @since 2020.02.21
         */
        wpessentials_print_input_tag(
          array(
            'setting'   => array( $args['setting'], 'bf_atts_period' ),
            'value'     => $setting['bf_atts_period'] ?? 0,
            'label_for' => 'wpessentials_security_bf_atts_period',
            'desc'      => __( 'minutes.', 'wpessentials' ),
            'attr'      => array( 'min' => 1 ),
          ),
          'number'
        );
        ?>
      </p>
      <p class="description">
        <?php
        _e(
          'The number of minutes in which incorrect login attempts are remembered.',
          'wpessentials'
        );
        ?>
      </p>

      <br>
      <p>
        <label for="wpessentials_security_bf_lockout_period"
        ><?php _e( 'Lockout duration', 'wpessentials' ); ?></label>

        <br>
        <?php
        /**
         * Lockout period.
         *
         * @since 2020.02.21
         */
        wpessentials_print_input_tag(
          array(
            'setting'   => array( $args['setting'], 'bf_lockout_period' ),
            'value'     => $setting['bf_lockout_period'] ?? 0,
            'label_for' => 'wpessentials_security_bf_lockout_period',
            'desc'      => __( 'minutes.', 'wpessentials' ),
            'attr'      => array( 'min' => 1 ),
          ),
          'number'
        );
        ?>
      </p>
      <p class="description">
        <?php
        _e(
          'The period in which a username is blocked or a host is banned.',
          'wpessentials'
        );
        ?>
      </p>
      <br>

      <p>
        <?php
        /**
         * Email lockout event.
         *
         * @since 2020.02.26
         */
        wpessentials_print_input_tag(
          array(
            'setting'   => array( $args['setting'], 'bf_email_lockout_event' ),
            'value'     => $setting['bf_email_lockout_event'] ?? false,
            'label_for' => 'wpessentials_security_bf_email_lockout_event',
            'desc'      => __( 'When a lockout occurs, send an email to:', 'wpessentials' ),
          ),
          'checkbox'
        );
        ?>

        <br>
        <?php
        /**
         * Email to.
         *
         * @since 2020.02.26
         */
        wpessentials_print_input_tag(
          array(
            'setting'     => array( $args['setting'], 'bf_email_lockout_event_to' ),
            'value'       => $setting['bf_email_lockout_event_to'] ?? '',
            'placeholder' => get_option( 'admin_email', '' ),
          ),
          'email'
        );
        ?>

        <br>
        <?php
        /**
         * Email only if existing user is locket out.
         *
         * @since 2020.03.14
         */
        wpessentials_print_input_tag(
          array(
            'setting'   => array( $args['setting'], 'bf_email_lockout_event_existing_user' ),
            'value'     => $setting['bf_email_lockout_event_existing_user'] ?? false,
            'label_for' => 'wpessentials_security_bf_email_lockout_event_existing_user',
            'desc'      => __( 'Only send an email when an existing user is locked out', 'wpessentials' ),
          ),
          'checkbox'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_security', // $page*
    'wpessentials_security_section', // $section
    array(
      'setting' => 'wpessentials_security',
    ) // $args
  );

  /**
   * Fire action hook to add settings fields with plugin extension packs.
   *
   * @since 2020.02.21
   */
  do_action( 'wpessentials_' . $module['slug'] . '_after_settings_fields', $module );
}


/**
 * Enqueue styles and scripts ..
 *
 * @since 2020.02.21
 */
function wpessentials_security_enqueue_scripts()
{
  wp_enqueue_style(
    'wpessentials_login_admin_styles', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-security-styles.css', // $src
    array(), // $deps
    WPESSENTIALS_PLUGIN_VERSION // $ver
  );

  wp_enqueue_script(
    'wpessentials_security_admin_scripts', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-security-scripts.js', // $src
    array( 'jquery' ), // $deps
    WPESSENTIALS_PLUGIN_VERSION // $ver
  );
}

/**
 * Sanitize callback.
 *
 * @since 2020.02.21
 * @since 2020.02.26 Added `bf_email_lockout_event_to` setting.
 * @since 2020.12.10 Sanitize minimum values for number inputs.
 *
 * @link https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
 */
function wpessentials_security_sanitize_cb( array $args )
{
  $args = max( -1, wpessentials_sanitize( $args, 'bf_atts_user', 'int' ) );
  $args = max( -1, wpessentials_sanitize( $args, 'bf_atts_host', 'int' ) );
  $args = max( 1, wpessentials_sanitize( $args, 'bf_atts_period', 'int' ) );
  $args = max( 1, wpessentials_sanitize( $args, 'bf_lockout_period', 'int' ) );
  $args = wpessentials_sanitize( $args, 'bf_email_lockout_event_to', 'email' );

  return $args;
}


/**
 * AJAX hook to regenerate Authentication Unique Keys and Salts.
 *
 * @since 2020.02.21
 * @since 2020.03.01 Only force a logout if changing keys was performed successfully.
 * @since 2020.03.14 Added `wp_doing_ajax()` check.
 */
add_action(
  'wp_ajax_wpessentials_generate_secret_keys',
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

    $wp_config_editor = new WPessentials_WP_Config_Edit();

    if (
      $wp_config_editor->regenerate_keys() &&
      $wp_config_editor->write()
    ) {
      wp_logout();
    }
  }
);

/**
 * AJAX hook to release lockouts.
 *
 * @since 2020.03.14
 */
add_action(
  'wp_ajax_wpessentials_release_lockout',
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

    header( 'Content-Type: text/plain' );

    /**
     * Release lockout.
     */
    if ( ! empty( $_POST['lockout_id'] ) ) {
      if ( WPessentials_BF::release_lockout( $_POST['lockout_id'] ) ) {
        echo 'true';
      }
    }

    wp_die(
      '',
      '',
      array(
        'response' => 200,
        'exit'     => true,
      )
    );
  }
);

/**
 * Returns users that have a publicly revealed username.
 *
 * @since 2020.03.30
 */
function wpessentials_get_publicly_revealed_usernames_users()
{
  $users = array_filter(
    get_users( array() ),
    function ( $user )
    {
      return (
        strcasecmp( $user->user_login, $user->display_name ) === 0 ||
        strcasecmp( $user->user_login, $user->user_nicename ) === 0
      );
    }
  );

  $arr = array();

  foreach ( $users as $user ) {
    $arr[] = array(
      'ID'          => $user->ID,
      'username'    => $user->user_login,
      'fullname'    => $user->first_name . ' ' . $user->last_name,
      'nicename'    => $user->user_nicename,
      'displayname' => $user->display_name,
      'roles'       => implode( ',', $user->roles ),
    );
  }

  return $arr;
}

/**
 * AJAX hook to update users.
 *
 * @since 2020.03.30
 */
add_action(
  'wp_ajax_wpessentials_update_user_public_names',
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

    header( 'Content-Type: application/json' );

    /**
     * Loop through posted user ID's and update their display names.
     */
    if (
      isset( $_POST['users'] )
      // $_SERVER['REQUEST_METHOD'] === 'POST'
    ) {
      foreach ( $_POST['users'] as $user ) {
        /**
         * Verify the ID.
         */
        if (
          ! isset( $user['ID'] ) ||
          ! is_numeric( $user['ID'] ) ||
          ! isset( $user['name'] )
        ) {
          continue;
        }

        /**
         * Sanitize $display_name.
         */
        $display_name = sanitize_text_field( $user['name'] );

        /**
         * Update user.
         */
        wp_update_user(
          array(
            'ID'            => $user['ID'],
            'user_nicename' => $display_name,
            'display_name'  => $display_name,
            'nickname'      => $display_name,
          )
        );
      }
    }

    /**
     * Echo users that have a publicly revealed username.
     */
    echo json_encode( wpessentials_get_publicly_revealed_usernames_users() );

    wp_die(
      '',
      '',
      array(
        'response' => 200,
        'exit'     => true,
      )
    );
  }
);


/**
 * Update .htaccess according to settings.
 *
 * @since 2020.12.10
 *
 * @return bool Whether the updates succeeded.
 */
function wpessentials_security_update_htaccess()
{
  $setting = wpessentials_get_option( array( 'wpessentials_security' ) );

  $htaccess_editor = new WPessentials_HTAccess_Edit();

  /**
   * Update contents of .htaccess.
   */
  $htaccess_editor->regenerate_rules(
    array(
      'disable_dir_browsing'         => ! empty( $setting['disable_dir_browsing'] ),
      'protect_sys_files'            => ! empty( $setting['protect_sys_files'] ),
      'disable_php_upload_dir'       => ! empty( $setting['disable_php_upload_dir'] ),
      'no_useragent_no_post_request' => ! empty( $setting['no_useragent_no_post_request'] ),
      'no_referer_no_comment'        => ! empty( $setting['no_referer_no_comment'] ),
    )
  );

  /**
   * Write changes to .htaccess, reverse settings on failure.
   */
  if ( ! $htaccess_editor->write() ) {
    $setting['disable_dir_browsing']         = $htaccess_editor->options['disable_dir_browsing'];
    $setting['protect_sys_files']            = $htaccess_editor->options['protect_sys_files'];
    $setting['disable_php_upload_dir']       = $htaccess_editor->options['disable_php_upload_dir'];
    $setting['no_useragent_no_post_request'] = $htaccess_editor->options['no_useragent_no_post_request'];
    $setting['no_referer_no_comment']        = $htaccess_editor->options['no_referer_no_comment'];

    update_option( 'wpessentials_security', $setting );

    return false;
  }

  return true;
}

/**
 * Update wp-config.php according to settings.
 *
 * @since 2020.12.14
 * @since 2021.03.12 Using the new wp_update_urls_to_https() function if exists, since WordPress v5.7
 * @since 2021.03.12 Fixed bug where "devmode" setting was reversed instead of "security" on failure.
 *
 * @return bool Whether the updates succeeded.
 */
function wpessentials_security_update_wp_config()
{
  $setting = wpessentials_get_option( array( 'wpessentials_security' ) );

  $do_ssl = ! empty( $setting['force_ssl'] ) && wpessentials_wp_update_urls_to_https();

  $wp_config_editor = new WPessentials_WP_Config_Edit();

  /**
   * Update contents of wp-config.php.
   */
  $wp_config_editor->set_constant( 'FORCE_SSL_ADMIN', ! empty( $setting['force_ssl'] ) ? true : null );

  /**
   * Write changes to wp-config.php, reverse settings on failure.
   */
  if ( ! $do_ssl || ! $wp_config_editor->write() ) {
    $setting['force_ssl'] = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN;

    update_option( 'wpessentials_security', $setting );

    return false;
  }

  return true;
}


/**
 * On enable hook.
 *
 * @since 2020.12.10
 * @since 2020.12.14 Added wp-config.php update call.
 */
add_action(
  'wpessentials_security_on_enable',
  function ()
  {
    wpessentials_security_update_htaccess();

    wpessentials_security_update_wp_config();
  }
);

/**
 * On disable hook.
 *
 * @since 2020.12.10
 * @since 2020.12.14 Added removal of FORCE_SSL_ADMIN.
 */
add_action(
  'wpessentials_security_on_disable',
  function ()
  {
    $htaccess_editor = new WPessentials_HTAccess_Edit();

    $htaccess_editor->remove_rules();

    $htaccess_editor->write();

    // $wp_config_editor = new WPessentials_WP_Config_Edit();

    // $wp_config_editor->set_constant( 'FORCE_SSL_ADMIN', null );

    // $wp_config_editor->write();
  }
);
