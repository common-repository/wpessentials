<?php

defined( 'ABSPATH' ) || exit;


/**
 * Register setting sections and fields (admin_init).
 *
 * @since 2020.02.21
 * @since 2021.03.12 Removed analytics.js, now gtag.js only.
 *
 * @link https://developer.wordpress.org/plugins/settings/
 */
function wpessentials_analytics_admin_init( array $module )
{
  /**
   * The module's main section.
   *
   * @since 2020.02.21
   */
  add_settings_section(
    'wpessentials_analytics_section', // $id*
    'Google Analytics', // $title*
    function () use ( $module )
    {
      ?>
      <p class="description">
        <?php echo $module['desc']; ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_analytics' // $page*
  );

  /**
   * Tracking IDs.
   *
   * @ignore ADD SUPPORT FOR MULTIPLE UNLIMITED IDs AS GTAG SUPPORTS MORE SERVICES THAN JUST ANALYTICS !!
   *
   * @since 2020.02.21
   */
  /*wpessentials_*/add_settings_field(
    'wpessentials_analytics_trackingid_field', // $id*
    __( 'Tracking ID', 'wpessentials' ), // $title*
    function ( $args )
    {
      wpessentials_print_input_tag( $args, 'text' );
      ?>
      <p class="description">
        <?php
        _e(
          'Find your Tracking ID under "Admin" >> "Tracking Info" >> "Tracking Code" in your <a href="https://analytics.google.com/analytics/" target="_blank" rel="noopener noreferrer">Google Analytics account</a>.<br>
          After saving settings, make sure Google Analytics is receiving traffic from your website using the "Send test traffic" button.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting'   => array( 'wpessentials_analytics', 'trackingid' ),
      'label_for' => 'wpessentials_analytics_trackingid',
    ) // $args
  );

  /**
   * Header or footer.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_analytics_snippet_location_field', // $id*
    __( 'Tag location', 'wpessentials' ), // $title*
    function ( $args )
    {
      $setting = wpessentials_get_option( $args['setting'] );
      ?>
      <select
      id="<?php echo esc_attr( $args['label_for'] ); ?>"
      name="<?php wpessentials_option_name_attr( $args['setting'] ); ?>"
      >
        <option value="header"
        <?php selected( '', $setting ); ?>
        >
          Header
        </option>
        <option value="footer"
        <?php selected( 'footer', $setting ); ?>
        >
          Footer
        </option>
      </select>
      <p class="description">
        <?php
        _e(
          'Google recommends including the tag in the page header, but including it in the footer could benefit page performance.<br>
          If in doubt, go with the default header option.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting'   => array( 'wpessentials_analytics', 'snippet_location' ),
      'label_for' => 'wpessentials_analytics_snippet_location',
    ) // $args
  );

  /**
   * Anonymize.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_analytics_anonymize_field', // $id*
    __( 'Anonymize', 'wpessentials' ), // $title*
    function ( $args )
    {
      wpessentials_print_input_tag(
        array(
          'setting'   => $args['setting'],
          'label_for' => $args['label_for'],
          'desc'      => __( 'Anonymize the last 3 digits of the IP-adress (<a href="https://support.google.com/analytics/answer/2763052" target="_blank" rel="noopener noreferrer">Explanation</a>)', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <p class="description">
        <?php
        _e(
          'TIP! Accept the "Data processing amendment" for your Google Analytics account and disallow Google to share Analytics data with their other services.<br>
          Then - with this option enabled - you don\'t need to ask your visitors for permission to place Analytics cookies. How To: <a href="https://support.google.com/analytics/answer/1011397/" target="_blank" rel="noopener noreferrer">Google</a>; <a href="https://daan.dev/wordpress/analytics-gdpr-anonymize-ip-cookie-notice/" target="_blank" rel="noopener noreferrer">Daan.dev</a>.',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting'   => array( 'wpessentials_analytics', 'anonymize' ),
      'label_for' => 'wpessentials_analytics_anonymize',
    ) // $args
  );

  /**
   * Disable advertising.
   *
   * @since 2020.12.05
   */
  wpessentials_add_settings_field(
    'wpessentials_analytics_disable_ads_field', // $id*
    __( 'Disable ads', 'wpessentials' ), // $title*
    function ( $args )
    {
      wpessentials_print_input_tag(
        array(
          'setting'   => $args['setting'],
          'label_for' => $args['label_for'],
          'desc'      => __( 'Disable advertising features (<a href="https://support.google.com/analytics/answer/9050852" target="_blank" rel="noopener noreferrer">Explanation</a>)', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>
      <?php
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting'   => array( 'wpessentials_analytics', 'disable_ads' ),
      'label_for' => 'wpessentials_analytics_disable_ads',
    ) // $args
  );

  /**
   * Admin area.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_analytics_admin_area_field', // $id*
    __( 'Admin area', 'wpessentials' ), // $title*
    function ( $args )
    {
      wpessentials_print_input_tag(
        array(
          'setting'   => $args['setting'],
          'label_for' => $args['label_for'],
          'desc'      => __( 'Enable Google Analytics in the Admin area', 'wpessentials' ),
        ),
        'checkbox'
      );
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting'   => array( 'wpessentials_analytics', 'admin_area' ),
      'label_for' => 'wpessentials_analytics_admin_area',
    ) // $args
  );

  /**
   * User tracking.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_analytics_bypass_administrators_field', // $id*
    __( 'User tracking', 'wpessentials' ), // $title*
    function ( $args )
    {
      ?>
      <p><?php _e( 'Your current IP adress:', 'wpessentials' ); ?> <code><?php echo esc_html( $_SERVER['REMOTE_ADDR'] ); ?></code></p>

      <br>
      <?php
      /**
       * Bypass administrators.
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'bypass_administrators' ),
          'label_for' => 'wpessentials_analytics_bypass_administrators',
          'desc'      => __( 'Disable tracking of Administrator users', 'wpessentials' ),
        ),
        'checkbox'
      );
      ?>

      <br>
      <?php
      /**
       * Bypass logged in users.
       */
      wpessentials_print_input_tag(
        array(
          'setting'   => array( $args['setting'], 'bypass_loggedin' ),
          'label_for' => 'wpessentials_analytics_bypass_loggedin',
          'desc'      => __( 'Disable tracking of all logged-in users', 'wpessentials' ),
        ),
        'checkbox'
      );
    }, // $callback*
    'wpessentials_analytics', // $page*
    'wpessentials_analytics_section', // $section
    array(
      'setting' => 'wpessentials_analytics',
    ) // $args
  );
}


/**
 * Enqueue styles and scripts ..
 *
 * @since 2020.02.21
 */
function wpessentials_analytics_enqueue_scripts()
{
  wp_enqueue_script(
    'wpessentials_analytics_admin_scripts', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-analytics-scripts.js', // $src
    array( 'jquery' ), // $deps
    WPESSENTIALS_PLUGIN_VERSION // $ver
  );
}


/**
 * Sanitize callback.
 *
 * @since 2020.02.21
 * @since 2020.02.21 `snippet_location` defaults to "header" if passed an empty string.
 *
 * @link https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
 */
function wpessentials_analytics_sanitize_cb( array $args )
{
  $args = wpessentials_sanitize( $args, 'trackingid' );
  $args = wpessentials_sanitize( $args, 'snippet_location', null, 'header' );

  return $args;
}
