<?php

/**
 * Plugin Name:       Essentials
 * Version:           2022.02.11
 * Requires at least: 4.9.6
 * Requires PHP:      7.2
 * Description:       Adds essential functionalities to your WordPress installation. Security and development options, Google Analytics and more.
 * Author:            Bob Vandevliet
 * Author URI:        https://www.bvandevliet.nl/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl.html
 * Text Domain:       wpessentials
 * Domain Path:       /languages
 *
 * @package wpessentials
 * @link https://plugins.svn.wordpress.org/wpessentials/
 */

defined( 'ABSPATH' ) || exit;

define( 'WPESSENTIALS_PLUGIN_VERSION', '2022.02.11' );


/**
 * Register modules using the global modules variable ..
 *
 * @since 2020.02.21
 * @since 2021.03.12 Named parameters for better readability.
 *
 * @ignore Place modules in "/modules/{slug}/":
 * @ignore "/settings.php" is required for the settings page.
 * @ignore "/action.php" is required only if the module is enabled.
 *
 * @ignore Callbacks:
 * @ignore wpessentials_{slug}_admin_init( $module )*
 * @ignore wpessentials_{slug}_sanitize_cb( $args )
 * @ignore wpessentials_{slug}_enqueue_scripts()
 *
 * @ignore Action hooks:
 * @ignore wpessentials_{slug}_on_enable
 * @ignore wpessentials_{slug}_on_disable
 */
$wpessentials_modules = array(

  // Security.
  array(
    'slug'       => 'security',
    'page_title' => __( 'Security', 'wpessentials' ),
    'menu_title' => __( 'Security', 'wpessentials' ),
    'desc'       => __( 'Basic security options and brute-force protection.', 'wpessentials' ),
    'capability' => 'manage_options',
  ),

  // Development Mode.
  array(
    'slug'       => 'devmode',
    'page_title' => __( 'Development Mode', 'wpessentials' ),
    'menu_title' => __( 'Devmode', 'wpessentials' ),
    'desc'       => __( 'Use these options when your WordPress website is being developed.', 'wpessentials' ),
    'capability' => 'manage_options',
  ),

  // Login Form.
  array(
    'slug'       => 'login',
    'page_title' => __( 'Login Form', 'wpessentials' ),
    'menu_title' => __( 'Login Form', 'wpessentials' ),
    'desc'       => __( 'Change the WordPress logo shown above the login form to your own brand\'s logo.', 'wpessentials' ),
    'capability' => 'manage_options',
  ),

  // Google Analytics.
  array(
    'slug'       => 'analytics',
    'page_title' => __( 'Google Analytics', 'wpessentials' ),
    'menu_title' => __( 'Analytics', 'wpessentials' ),
    'desc'       => __( 'Implement Google Analytics using gtag.js.', 'wpessentials' ),
    'capability' => 'edit_pages',
  ),

);


/**
 * Include plugin resources.
 *
 * @since 2020.02.21
 * @since 2020.03.14 Added "wp-extensions.php".
 * @since 2020.12.10 Added "brute-force.php" included by default fixing errors on settings page if module is disabled.
 */
require dirname( __FILE__ ) . '/inc/functions.php';
require dirname( __FILE__ ) . '/inc/wp-extensions.php';
require dirname( __FILE__ ) . '/inc/file-editor.php';
require dirname( __FILE__ ) . '/inc/brute-force.php';

/**
 * Include plugin modules.
 */
foreach ( $wpessentials_modules as $module ) {
  require_once dirname( __FILE__ ) . '/modules/' . $module['slug'] . '/settings.php';

  if (
    wpessentials_get_option( array( 'wpessentials_' . $module['slug'] . '_enabled' ) ) &&
    file_exists( dirname( __FILE__ ) . '/modules/' . $module['slug'] . '/action.php' )
  ) {
    require_once dirname( __FILE__ ) . '/modules/' . $module['slug'] . '/action.php';
  }
}


/**
 * Register activation/deactivation/uninstall hooks.
 *
 * @since 2020.02.21
 */
require dirname( __FILE__ ) . '/inc/install-hooks.php';
register_activation_hook( __FILE__, 'wpessentials_create_db_tables' );
register_activation_hook( __FILE__, 'wpessentials_set_default_settings' );

// The function BEFORE the update is triggered instead of AFTER the update :( !!
add_action( 'upgrader_process_complete', 'wpessentials_set_default_settings' );

register_deactivation_hook( __FILE__, 'wpessentials_run_disable_hooks' );
register_uninstall_hook( __FILE__, 'wpessentials_delete_all_settings' );


/**
 * Force the languages to load.
 *
 * @since 2020.03.14
 * @since 2021.10.14 Added plugin_basename() which did the trick making it work.
 */
add_action(
  'init',
  function ()
  {
    load_plugin_textdomain( 'wpessentials', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
  }
);


/**
 * Append subtile call-to-action write review link below plugin at the plugins admin page.
 *
 * @since 2020.12.10
 */
add_filter(
  'plugin_action_links_' . plugin_basename( __FILE__ ),
  function ( $actions )
  {
    array_unshift(
      $actions,
      '<a href="' . admin_url( 'admin.php?page=wpessentials' ) . '">' . __( 'Settings', 'wpessentials' ) . '</a>',
      '<a href="https://wordpress.org/plugins/wpessentials/#reviews" target="_blank" rel="noopener">' . __( 'Rate', 'wpessentials' ) . ' &#9733;</a>'
    );

    return $actions;
  }
);


/**
 * Daily cronjobs.
 *
 * @since 2020.12.10
 */
add_action(
  'wpessentials_cronjobs_daily',
  function ()
  {
    WPessentials_BF::cleanup_records();
  }
);


/**
 * Register setting sections and fields (admin_init).
 *
 * @since 2020.02.21
 *
 * @link https://developer.wordpress.org/plugins/settings/
 */
add_action(
  'admin_init',
  function ()
  {
    /**
     * Fire action hook to include additional resources for extension packs.
     *
     * @since 2020.02.21
     */
    do_action( 'wpessentials_extension_resources' );

    /**
     * The plugin's main section.
     */
    add_settings_section(
      'wpessentials_section', // $id*
      null, // $title*
      function ()
      {
        ?>
        <p class="description">
          <?php
          _e(
            'An overview of all available modules.<br>
            Enable/disable the modules you require or quickly goto a module\'s admin page.',
            'wpessentials'
          );
          ?>
        </p>
        <?php
      }, // $callback*
      'wpessentials' // $page*
    );

    // Register module settings.
    _wpessentials_register_module_settings();
  }
);


/**
 * Add the plugin menu's and pages (admin_menu).
 *
 * @since 2020.02.21
 * @since 2020.12.14 Display basic system information at the bottom.
 *
 * @link https://developer.wordpress.org/plugins/administration-menus/
 */
add_action(
  'admin_menu',
  function ()
  {
    add_menu_page(
      'Essentials', // $page_title
      'Essentials', // $menu_title
      'edit_pages', // $capability
      'wpessentials', // $menu_slug
      /**
       * The page ..
       */
      function ( $args )
      {
        /**
         * Check whether the user has submitted the settings.
         * WordPress will add the "settings-updated" $_GET parameter to the url.
         */
        if ( ! empty( $_GET['settings-updated'] ) ) {
          add_settings_error(
            'wpessentials',
            'wpessentials_successmsg',
            __( 'Settings saved', 'wpessentials' ),
            'updated'
          );
        }

        // Print submit messages.
        settings_errors( 'wpessentials' );
        ?>

      <div class="wrap wpessentials-page wpessentials-main-page">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
          <?php

          // Output security fields for the registered setting.
          settings_fields( 'wpessentials' ); // $option_group

          // Output setting sections and their fields.
          do_settings_sections( 'wpessentials' ); // $page

          // Submit ..
          submit_button( __( 'Save changes', 'wpessentials' ) );

          ?>
          <a href="https://wordpress.org/plugins/wpessentials/#reviews" target="_blank" rel="noopener noreferrer"><?php esc_attr_e( 'Rate this plugin', 'wpessentials' ); ?> &#9733;</a>
        </form>
      </div>

        <?php
      }, // $function
      'dashicons-admin-generic', // $icon_url
      '99' // $position
    );

    // Add module submenu pages.
    _wpessentials_add_module_submenu_pages();
  }
);


/**
 * Enqueue admin styles and scripts.
 *
 * @since 2020.02.21
 */
add_action(
  'admin_enqueue_scripts',
  function ()
  {
    /**
     * General ..
     */
    wp_enqueue_style(
      'wpessentials_styles', // $handle
      plugin_dir_url( __FILE__ ) . 'assets/wpessentials-styles.css', // $src
      array(), // $deps
      WPESSENTIALS_PLUGIN_VERSION // $ver
    );

    /**
     * Enqueue module styles and scripts.
     */
    _wpessentials_enqueue_module_scripts();
  }
);
