<?php

defined( 'ABSPATH' ) || exit;


/**
 * Toggle field callback (admin_init).
 *
 * @since 2020.02.21
 *
 * @access private
 */
function _wpessentials_toggle_field_cb( array $args )
{
  if ( $_GET['page'] === 'wpessentials' ) {
    $args['desc'] = __( 'Enabled', 'wpessentials' );
  }

  wpessentials_print_input_tag( $args, 'checkbox' );

  if ( $_GET['page'] === 'wpessentials' ) :
    ?>
    <p
    class="description">
    <?php echo $args['module']['desc']; ?>
    </p>
    <a
    <?php // class="button button-secondary" ?>
    href="<?php echo admin_url( 'options-general.php?page=wpessentials_' . $args['module']['slug'] ); ?>"
    ><?php _e( 'Manage', 'wpessentials' ); ?>
    </a>
    <?php
  endif;
}


/**
 * Register module settings to multiple option groups (admin_init).
 *
 * @since 2020.02.21
 *
 * @global array $wpessentials_modules
 *
 * @access private
 */
function _wpessentials_register_module_settings()
{
  global $wpessentials_modules;

  foreach ( $wpessentials_modules as $module ) {
    if ( current_user_can( $module['capability'] ) ) {
      /**
       * Register the module settings.
       *
       * @ignore These must always be registered BEFORE the _enabled setting,
       * @ignore then it is possible to read the updated settings from within the enable/disable callbacks.
       */
      register_setting(
        'wpessentials_' . $module['slug'], // $option_group*
        'wpessentials_' . $module['slug'], // $option_name*
        'wpessentials_' . $module['slug'] . '_sanitize_cb' // $args|$sanitize_cb
      );

      /**
       * Register the _enabled setting to the plugin's main admin page.
       */
      register_setting(
        'wpessentials', // $option_group*
        'wpessentials_' . $module['slug'] . '_enabled', // $option_name*
        array(
          'type'              => 'boolean',
          'sanitize_callback' => function ( $value ) use ( $module )
          {
            /**
             * Enable/disable action hooks.
             *
             * @since 2020.02.21
             */
            if ( ! empty( $value ) ) {
              do_action( 'wpessentials_' . $module['slug'] . '_on_enable' );
            } else {
              do_action( 'wpessentials_' . $module['slug'] . '_on_disable' );
            }

            return $value;
          },
        ) // $args
      );

      /**
       * Register the _enabled setting to the module's settings page.
       */
      register_setting(
        'wpessentials_' . $module['slug'], // $option_group*
        'wpessentials_' . $module['slug'] . '_enabled', // $option_name*
        array(
          'type'              => 'boolean',
          'sanitize_callback' => function ( $value ) use ( $module )
          {
            /**
             * Enable/disable action hooks.
             *
             * @since 2020.02.21
             */
            if ( ! empty( $value ) ) {
              do_action( 'wpessentials_' . $module['slug'] . '_on_enable' );
            } else {
              do_action( 'wpessentials_' . $module['slug'] . '_on_disable' );
            }

            return $value;
          },
        ) // $args
      );

      /**
       * Add toggle field to the plugin's main admin page.
       */
      add_settings_field(
        'wpessentials_toggle_' . $module['slug'] . '_field', // $id*
        $module['page_title'], // $title*
        '_wpessentials_toggle_field_cb', // $callback*
        'wpessentials', // $page*
        'wpessentials_section', // $section
        array(
          'module'    => $module,
          'setting'   => array( 'wpessentials_' . $module['slug'] . '_enabled' ),
          'label_for' => 'wpessentials_toggle_' . $module['slug'],
          'class'     => 'wpessentials-toggle',
        ) // $args
      );

      /**
       * Add toggle field to the module's settings page.
       */
      add_settings_field(
        'wpessentials_' . $module['slug'] . '_toggle_field', // $id*
        __( 'Enable', 'wpessentials' ), // $title*
        '_wpessentials_toggle_field_cb', // $callback*
        'wpessentials_' . $module['slug'], // $page*
        'wpessentials_' . $module['slug'] . '_section', // $section
        array(
          'module'    => $module,
          'setting'   => array( 'wpessentials_' . $module['slug'] . '_enabled' ),
          'label_for' => 'wpessentials_toggle_' . $module['slug'],
          'desc'      => __( 'Enable', 'wpessentials' ) . ' ' . $module['page_title'] . ' module',
          'class'     => 'wpessentials-toggle',
        ) // $args
      );
    }

    call_user_func( 'wpessentials_' . $module['slug'] . '_admin_init', $module );
  }

  /**
   * Add Link Finder reference to the plugin's main admin page.
   */
  add_settings_field(
    'wpessentials_link_finder_ref_field', // $id*
    __( 'Link Finder', 'wpessentials' ), // $title*
    function ()
    {
      $link_finder_active = wpessentials_is_plugin_active( 'link-finder/linkfinder.php' );
      $link_finder_url    = $link_finder_active
        ? admin_url( 'tools.php?page=linkfinder' )
        : ( $link_finder_active === false
        ? admin_url( 'plugins.php?' )
        : admin_url( 'plugin-install.php?tab=plugin-information&plugin=link-finder' ) );
      ?>
      <a
      <?php // class="button button-secondary" ?>
      href="<?php echo $link_finder_url; ?>"
      ><?php echo $link_finder_active ? __( 'Manage', 'wpessentials' ) : __( 'Install', 'wpessentials' ); ?></a>
      <?php
    }, // $callback*
    'wpessentials', // $page*
    'wpessentials_section', // $section
    array(
      'class' => 'wpessentials-toggle',
    ) // $args
  );
}


/**
 * Markup for the module's settings pages (admin_menu).
 *
 * @since 2020.02.21
 * @since 2020.03.05 Added "submit_button" filter.
 *
 * @access private
 */
function _wpessentials_module_submenu_page_cb( array $module )
{
  ?>
  <div class="wrap wpessentials-page wpessentials-settings-page">
    <h1>Essentials</h1>
    <form action="options.php" method="post">
  <?php

  // Output security fields for the registered setting.
  settings_fields( 'wpessentials_' . $module['slug'] ); // $option_group

  // Output setting sections and their fields.
  do_settings_sections( 'wpessentials_' . $module['slug'] ); // $page

  // The submit button.
  submit_button( __( 'Save changes', 'wpessentials' ) );

  /**
   * Action to append content after the submit button.
   *
   * @since 2020.12.10
   */
  do_action( 'wpessentials_' . $module['slug'] . '_after_submit_button' );

  ?>
    </form>
  </div>
  <?php
}


/**
 * Add the module's settings pages (admin_menu).
 *
 * @since 2020.02.21
 *
 * @global array $wpessentials_modules
 *
 * @access private
 */
function _wpessentials_add_module_submenu_pages()
{
  global $wpessentials_modules;

  foreach ( $wpessentials_modules as $module ) {
    add_options_page(
      $module['page_title'], // $page_title
      $module['menu_title'], // $menu_title
      $module['capability'], // $capability
      'wpessentials_' . $module['slug'], // $menu_slug
      function () use ( $module )
      {
        _wpessentials_module_submenu_page_cb( $module );
      } // $function
    );
  }
}


/**
 * Enqueue styles and scripts (admin_enqueue_scripts).
 *
 * @since 2020.02.21
 *
 * @global array $wpessentials_modules
 *
 * @access private
 */
function _wpessentials_enqueue_module_scripts()
{
  global $wpessentials_modules;

  foreach ( $wpessentials_modules as $module ) {
    if (
      isset( $_GET['page'] ) &&
      $_GET['page'] === 'wpessentials_' . $module['slug']
    ) {
      wp_enqueue_script(
        'wpessentials_module_scripts', // $handle
        plugin_dir_url( __FILE__ ) . '../assets/wpessentials-module-scripts.js', // $src
        array( 'jquery' ), // $deps
        WPESSENTIALS_PLUGIN_VERSION // $ver
      );

      if ( function_exists( 'wpessentials_' . $module['slug'] . '_enqueue_scripts' ) ) {
        call_user_func( 'wpessentials_' . $module['slug'] . '_enqueue_scripts' );
      }
    }
  }
}


/**
 * Add settings field and wrap it inside the <fieldset> tag.
 *
 * @since 2020.02.21
 *
 * @param string   $id       Slug-name to identify the field. Used in the 'id' attribute of tags.
 * @param string   $title    Formatted title of the field. Shown as the label for the field during output.
 * @param callable $callback Function that fills the field with the desired form inputs. The function should echo its output.
 * @param string   $page     The slug-name of the settings page on which to show the section (general, reading, writing, ...).
 * @param string   $section  The slug-name of the section of the settings page in which to show the box.
 * @param array    $args     Extra arguments used when outputting the field.
 */
function wpessentials_add_settings_field(
  $id,
  $title,
  $callback,
  $page,
  $section,
  $args = array()
)
{
  add_settings_field(
    $id,
    $title,
    function ( $args ) use ( $callback )
    {
      ?>
      <fieldset>
      <?php
      call_user_func( $callback, $args );
      ?>
      </fieldset>
      <?php
    },
    $page,
    $section,
    $args
  );
}


/**
 * Get setting and always return a value.
 *
 * @since 2020.02.21
 * @since 2020.02.26 Added `$default` return value.
 * @since 2020.03.26 Correction, `$default` variable was not used in `get_option()`, `null` was used instead.
 *
 * @param array $option An array holding the option name [0] and an optional suboption name [1].
 * @return string|bool  The value of the given option, defaults to false if option was not found.
 */
function wpessentials_get_option( array $option, $default = false )
{
  $setting = get_option( $option[0], $default );
  if ( isset( $option[1] ) ) {
    return $setting[ $option[1] ] ?? $default;
  }
  return $setting ?? $default;
}


/**
 * Echo option name for html attribute.
 *
 * @since 2020.02.21
 *
 * @param array $option An array holding the option name [0] and an optional suboption name [1].
 */
function wpessentials_option_name_attr( array $option )
{
  echo esc_attr( isset( $option[1] ) ? $option[0] . '[' . $option[1] . ']' : $option[0] );
}


/**
 * Print a settings field input tag.
 *
 * @since 2020.02.21
 * @since 2020.02.26 Added `$type` "email".
 * @since 2020.02.26 Added "placeholder" support.
 * @since 2020.03.01 Added `$type` "radio" and made argument position of `$value` make more sense.
 * @since 2020.03.14 Added `$append`.
 * @since 2020.12.10 Added `$attr`.
 *
 * @ignore ADD <SELECT> !!
 * @ignore ADD ADDITIONAL WRAPPER FUNCTION TO EFFICIENTLY PRINT "RADIO"/"SELECT" TAGS !!
 *
 * @param array  $args {
 *   Required.
 *  @type array   $setting      Required. The setting.
 *  @type dynamic $value        Optional. A value to use instead of calling `get_option()`. Recommended for type "radio".
 *  @type dynamic $value_attr   Optional. Override the "value" attribute value. Required for type "radio".
 *  @type string  $label_for    Optional. To set a label "for" attribute.
 *  @type string  $desc         Optional. A description to append after the input tag.
 *  @type string  $append       Optional. Append after description but outside the label tag.
 *  @type string  $placeholder  Optional. A placeholder for text-alike input tags.
 *  @type array   $attr         Optional. Additional html attributes for input element.
 * }
 * @param string $type   The type of input to print.
 */
function wpessentials_print_input_tag( array $args, string $type )
{
  /**
   * Check requirements.
   *
   * @ignore Throw an exception ? !!
   */
  if (
    $type === 'radio' &&
    empty( $args['value_attr'] )
  ) {
    return;
  }

  ?>
  <label for="<?php echo esc_attr( $args['label_for'] ?? '' ); ?>">
  <?php
  $setting = $args['value'] ?? wpessentials_get_option( $args['setting'] );

  // if `$type` === "radio" || "checkbox" || "number" || "text" || "email"
  if (
    $type === 'radio' ||
    $type === 'checkbox' ||
    $type === 'number' ||
    $type === 'text' ||
    $type === 'email'
  ) :
    ?>
    <input type="<?php echo esc_attr( $type ); ?>"
    <?php
    if ( isset( $args['label_for'] ) ) {
      echo 'id="' . esc_attr( $args['label_for'] ) . '" ';
    }
    switch ( $type ) {
      case 'number':
        echo 'class="small-text" ';
        break;

      case 'text':
        echo 'class="regular-text" ';
        break;

      case 'email':
        echo 'class="regular-text" ';
        break;
    }
    ?>
    name="<?php wpessentials_option_name_attr( $args['setting'] ); ?>"
    <?php
    if ( isset( $args['attr'] ) ) {
      foreach ( $args['attr'] as $attr => $value ) {
        echo ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
      }
    }
    if ( $type === 'radio' ) {
      echo 'value="' . esc_attr( $args['value_attr'] ) . '" ';

      if ( $setting == $args['value_attr'] ) {
        echo 'checked';
      }
    } elseif ( $setting ) {
      if ( $type === 'checkbox' ) {
        echo 'checked';

      } else { // if `$type` === "number" || "text" || "email"
        echo 'value="' . esc_attr( $args['value_attr'] ?? $setting ) . '" ';
      }
    }
    if (
      (
        $type === 'text' ||
        $type === 'email'
      ) &&
      isset( $args['placeholder'] )
    ) {
      echo 'placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
    }
    ?>
    />
    <?php

    /**
     * if `$type` != "radio" || "checkbox" || "number" || "text" || "email".
     */
    elseif ( $type === 'textarea' ) :
      ?>
      <textarea
      <?php
      if ( isset( $args['label_for'] ) ) {
        echo 'id="' . esc_attr( $args['label_for'] ) . '" ';
      }
      ?>
      class="regular-text"
      name="<?php wpessentials_option_name_attr( $args['setting'] ); ?>"
      ><?php echo esc_textarea( $args['value_attr'] ?? ( $setting ?? '' ) ); ?></textarea>
      <?php
    endif;

    echo isset( $args['desc'] ) ? /*esc_html( */$args['desc']/* )*/ : ''; // allow for HTML markup.
    ?>
  </label><?php echo isset( $args['append'] ) ? ' ' . /*esc_html( */$args['append']/* )*/ : ''; // allow HTML markup. ?>
  <?php
}


/**
 * Remove line breaks and double whitespaces from string.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Added `$delim` to override the replacement for spaces and line breaks.
 *
 * @ignore @.param/return !!
 */
function wpessentials_trim( string $string, string $delim = ' ' )
{
  return trim( preg_replace( '/([\s\t\v\0\r]|\r?\n)+/', $delim, $string ) );
}


/**
 * Sanitize options inside array.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Return `$default` if `$option` contains an empty string.
 *
 * @ignore @.param/return !!
 */
function wpessentials_sanitize( array $args, string $option, string $as = null, $default = '' )
{
  if ( isset( $args[ $option ] ) ) {
    if ( $args[ $option ] === '' ) {
      $args[ $option ] = $default;
      return $args;
    }

    switch ( $as ) {
      case 'int':
        /**
         * Returns the integer value of a variable.
         */
        $args[ $option ] = intval( $args[ $option ] );
        break;

      case 'url':
        /**
         * Checks and cleans a URL.
         */
        $args[ $option ] = esc_url_raw( $args[ $option ] );
        break;

      case 'email':
        /**
         * Strips out all characters that are not allowable in an email.
         */
        $args[ $option ] = sanitize_email( $args[ $option ] );
        break;

      case 'filename':
        /**
         * Sanitizes a filename, replacing whitespace with dashes.
         */
        $args[ $option ] = sanitize_file_name( $args[ $option ] );
        break;

      case 'textarea':
        /**
         * Sanitizes a multiline string from user input or from the database.
         * The function is like sanitize_text_field(), but preserves new lines (\n) and other whitespace.
         */
        $args[ $option ] = sanitize_textarea_field( $args[ $option ] );
        break;

      default:
        /**
         * Sanitizes a string from user input or from the database.
         */
        $args[ $option ] = sanitize_text_field( $args[ $option ] );
    }
  }
  return $args;
}


/**
 * Determine a plugin installed/active status.
 *
 * @since 2020.02.21
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 *
 * @ignore @.param/return !!
 */
function wpessentials_is_plugin_active( string $plugin )
{
  if ( is_plugin_active( $plugin ) ) {
    return true;
  }

  if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
    return false;
  }

  return null;
}


/**
 * Checks whether the website is using HTTPS.
 *
 * Backcompat for WordPress < v5.7
 *
 * @since 2021.03.12
 *
 * @link https://developer.wordpress.org/reference/functions/wp_is_using_https/
 */
function wpessentials_wp_is_using_https()
{
  if ( function_exists( 'wp_is_using_https' ) ) {
    return wp_is_using_https();
  }

  /**
   * @link https://developer.wordpress.org/reference/functions/wp_is_home_url_using_https/
   */
  if ( ! 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ) {
    return false;
  }

  /**
   * @link https://developer.wordpress.org/reference/functions/wp_is_site_url_using_https/
   */
  // Use direct option access for 'siteurl' and manually run the 'site_url'
  // filter because `site_url()` will adjust the scheme based on what the
  // current request is using.
  /** This filter is documented in wp-includes/link-template.php */
  $site_url = apply_filters( 'site_url', get_option( 'siteurl' ), '', null, null );

  return 'https' === wp_parse_url( $site_url, PHP_URL_SCHEME );
}

/**
 * Update the "home" and "siteurl" option to use the HTTPS variant of their URL.
 *
 * Backcompat for WordPress < v5.7
 *
 * @since 2021.03.12
 *
 * @link https://developer.wordpress.org/reference/functions/wp_update_urls_to_https/
 */
function wpessentials_wp_update_urls_to_https()
{
  if ( function_exists( 'wp_update_urls_to_https' ) ) {
    return wp_update_urls_to_https();
  }

  // Get current URL options.
  $orig_home    = get_option( 'home' );
  $orig_siteurl = get_option( 'siteurl' );

  // Get current URL options, replacing HTTP with HTTPS.
  $home    = str_replace( 'http://', 'https://', $orig_home );
  $siteurl = str_replace( 'http://', 'https://', $orig_siteurl );

  // Update the options.
  update_option( 'home', $home );
  update_option( 'siteurl', $siteurl );

  if ( ! wpessentials_wp_is_using_https() ) {
    // If this did not result in the site recognizing HTTPS as being used,
    // revert the change and return false.
    update_option( 'home', $orig_home );
    update_option( 'siteurl', $orig_siteurl );

    return false;
  }

  return true;
}
