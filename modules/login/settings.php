<?php

defined( 'ABSPATH' ) || exit;


/**
 * Register setting sections and fields (admin_init).
 *
 * @since 2020.02.21
 *
 * @link https://developer.wordpress.org/plugins/settings/
 */
function wpessentials_login_admin_init( array $module )
{
  /**
   * The module's main section.
   *
   * @since 2020.02.21
   */
  add_settings_section(
    'wpessentials_login_section', // $id*
    __( 'Login Form', 'wpessentials' ), // $title*
    function () use ( $module )
    {
      ?>
      <p class="description">
        <?php echo $module['desc']; ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_login' // $page*
  );

  /**
   * Login logo.
   *
   * @since 2020.02.21
   */
  wpessentials_add_settings_field(
    'wpessentials_login_logo_field', // $id*
    __( 'Login logo', 'wpessentials' ), // $title*
    function ( $args )
    {
      $setting = wpessentials_get_option( $args['setting'] );
      if (
        isset( $setting ) &&
        intval( $setting ) > 0
      ) {
        /**
         * Get the image html tag for previously set image.
         */
        $logo_id   = intval( $setting );
        $image_src = wp_get_attachment_image_src(
          $logo_id, // $attachment_id
          'medium' // $size
        );
      } else {
        /**
         * Show default image if no image is set.
         */
        $logo_id   = 0;
        $image_src = array( admin_url( 'images/wordpress-logo.svg' ) );
      }
      ?>
      <p>
        <img
        src="<?php echo $image_src[0]; ?>" alt=""
        id="wpessentials_login_logo_img"
        style="max-width: 320px;"
        />
      </p>
      <p>
        <input type="hidden"
        id="wpessentials_login_logo_id"
        name="<?php wpessentials_option_name_attr( $args['setting'] ); ?>"
        value="<?php echo esc_attr( $logo_id ); ?>"
        />
        <input type="button" style="display: none;"
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        class="button-secondary"
        value="<?php esc_attr_e( 'Select image', 'wpessentials' ); ?>"
        />
        <a
        id="wpessentials_login_logo_rem_link"
        <?php echo ( ! $logo_id ? 'style="display: none;"' : '' ); ?>
        >
        <?php _e( 'Remove image', 'wpessentials' ); ?>
        </a>
      </p>
      <p class="description">
        <?php
        _e(
          'Change the image shown above the login form.<br>
          The image should ideally not be wider than 320px!',
          'wpessentials'
        );
        ?>
      </p>
      <?php
    }, // $callback*
    'wpessentials_login', // $page*
    'wpessentials_login_section', // $section
    array(
      'setting'   => array( 'wpessentials_login', 'logo_id' ),
      'label_for' => 'wpessentials_login_logo_add_btn',
    ) // $args
  );
}


/**
 * Enqueue styles and scripts ..
 *
 * @since 2020.02.21
 */
function wpessentials_login_enqueue_scripts()
{
  wp_enqueue_style(
    'wpessentials_login_admin_styles', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-login-styles.css', // $src
    array(), // $deps
    WPESSENTIALS_PLUGIN_VERSION // $ver
  );

  wp_enqueue_media();

  ?>
  <script>
    const wpessentials_default_login_logo = '<?php echo admin_url( 'images/wordpress-logo.svg' ); ?>'
  </script>
  <?php

  wp_enqueue_script(
    'wpessentials_login_admin_scripts', // $handle
    plugin_dir_url( __FILE__ ) . 'assets/wpessentials-login-scripts.js', // $src
    array( 'jquery' ), // $deps
    WPESSENTIALS_PLUGIN_VERSION, // $ver
    true
  );
}
