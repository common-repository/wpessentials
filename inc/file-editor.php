<?php

defined( 'ABSPATH' ) || exit;


/**
 * Class for reading files on the server.
 *
 * @since 2020.02.21
 * @since 2020.12.10 Contents property is now protected and readonly for outside logic.
 */
class WPessentials_File_Reader
{
  /**
   * Holds the filepath.
   *
   * @var string
   */
  protected $file_path;

  /**
   * Holds the file content.
   *
   * @var string
   */
  protected $contents = null;

  /**
   * Make specific private properties readonly.
   *
   * @since 2020.02.21
   *
   * @param string $prop  Property name.
   * @return dynamic|null Property value or null on failure.
   */
  public function __get( string $prop )
  {
    if ( $prop === 'contents' ) {
      $this->read();
      return $this->contents;
    } elseif ( in_array( $prop, array( 'file_path' ) ) ) {
      return $this->$prop;
    }
    return null;
  }

  /**
   * Constructor.
   *
   * Stores the filepath.
   *
   * @since 2020.02.21
   *
   * @param string $file_path Filepath.
   */
  public function __construct( string $file_path )
  {
    $this->file_path = $file_path;
  }

  /**
   * Read file contents and store it in a placeholder property.
   *
   * @since 2020.02.21
   * @since 2020.06.17 Moved checks that where in the specific readers before to this general method to avoid duplicate code.
   *
   * @return bool Whether the file was read successfully.
   */
  public function read()
  {
    /**
     * If content is already read, return early.
     */
    if (
      $this->contents !== null &&
      $this->contents !== false
    ) {
      return true;
    }

    /**
     * If content is not yet read, do it now.
     */
    if ( $this->contents === null ) {
      $this->contents = @file_get_contents( $this->file_path );
    }

    /**
     * If content could not be read, return false.
     */
    return $this->contents !== false;
  }

  /**
   * Write contents currently stored in the placeholder property to wp-config.php.
   *
   * @since 2020.02.21
   * @since 2020.03.01 Also return false if `read()` was not (yet) performed successfully.
   *
   * @return bool Whether wp-config.php was written successfully.
   */
  public function write()
  {
    if (
      $this->contents === null ||
      $this->contents === false
    ) {
      return false;
    }
    if ( file_put_contents( $this->file_path, $this->contents, LOCK_EX ) === false ) {
      return false;
    }
    return true;
  }
}


/**
 * Class for modifying constants in wp-config.php.
 *
 * @since 2020.02.21
 * @since 2020.02.24 Removed non-necessary function `get_constant( $name )`.
 * @since 2020.12.10 Now extends WPessentials_File_Reader class.
 */
class WPessentials_WP_Config_Edit extends WPessentials_File_Reader
{
  /**
   * Constructor.
   *
   * Creates an instance of "WPessentials_File_Reader" for wp-config.php.
   *
   * @since 2020.02.21
   */
  public function __construct()
  {
    parent::__construct( self::get_wp_config_path() );
  }

  /**
   * Retrieve the path to wp-config.php.
   *
   * @since 2020.02.21
   * @since 2020.02.26 Improved.
   * @since 2020.12.10 Cleaner code.
   *
   * @return string The filepath for wp-config.php.
   */
  public static function get_wp_config_path()
  {
    if (
      ! file_exists( $path = ABSPATH . 'wp-config.php' ) &&
      file_exists( dirname( ABSPATH ) . '/wp-config.php' ) &&
      ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' )
    ) {
      $path = dirname( ABSPATH ) . '/wp-config.php';
    }

    /**
     * Filter the wp-config.php filepath, return an empty string to avoid modifications to this file.
     *
     * @since 2020.02.26
     */
    return apply_filters( 'wpessentials_wp_config_path', $path );
  }

  /**
   * Updates the given php .ini setting and value.
   * If the setting was not yet defined, it is added.
   *
   * @since 2020.02.24
   * @since 2020.03.05 Added delimiter "/" to `preg_quote()` because the "/pattern/" format is used.
   * @since 2020.12.10 Improved validation logic.
   *
   * @param string           $name   The name of the php .ini setting to define.
   * @param string|bool|null $value  The value of the php .ini setting or null to remove its declaration.
   * @return bool                     Whether the php .ini setting was updated, added or removed successfully.
   */
  public function set_php_ini( string $name, $value )
  {
    /**
     * If content could not be read, return false.
     */
    if ( $this->read() === false ) {
      return false;
    }

    /**
     * Sanitize.
     */
    $name = wpessentials_trim( wp_strip_all_tags( $name ), '' );

    /**
     * Validation: $name should be whitelisted and $value should be of type bool or be null.
     * This is done to restrict editing freedom to the critical wp-config.php file.
     */
    if (
      ! in_array(
        $name,
        array(
          'display_errors',
          'log_errors',
        )
      )
    ) {
      return false;
    }

    /**
     * Remove php .ini setting declarations from content if NULL was passed.
     */
    if ( $value === null ) {
      $this->contents = preg_replace(
        '/\s*?@?ini_set\(\s*?[\'"]' . preg_quote( $name, '/' ) . '[\'"]\s*?,\s*?(.*?)\s*?\);\s*?(\r|\r?\n)/im',
        PHP_EOL,
        $this->contents
      );

      return true;
    }

    /**
     * If BOOL ..
     */
    elseif (
      is_bool( $value ) ||
      (
        is_string( $value ) &&
        strtolower( $value ) === 'on'
      )
    ) {
      $content_value = $value == true ? '\'On\'' : '\'Off\'';
    }

    /**
     * Else, bail.
     */
    else {
      return false;
    }

    /**
     * Update the first php .ini setting declaration in content.
     */
    if ( preg_match( '/@?ini_set\(\s*?[\'"]' . preg_quote( $name, '/' ) . '[\'"]\s*?,\s*?(.*?)\s*?\);/im', $this->contents, $constants ) ) {
      $this->contents = str_replace(
        $constants[0],
        '@ini_set( \'' . $name . '\', ' . $content_value . ' );',
        $this->contents
      );
    }

    /**
     * Add new php .ini setting declaration to content.
     */
    else {
      $this->contents = preg_replace(
        '/(\/\*.*?stop\sediting!.*?\*\/)/im',
        '@ini_set( \'' . $name . '\', ' . $content_value . ' );' . PHP_EOL . PHP_EOL . '$1',
        $this->contents
      );
    }

    return true;
  }

  /**
   * Updates the given constant and value.
   * If the constant was not yet defined, it is added.
   *
   * @since 2020.02.21
   * @since 2020.02.24 Improved regex patterns.
   * @since 2020.02.24 Whitelisted "WP_DISABLE_FATAL_ERROR_HANDLER".
   * @since 2020.12.10 Added WP_ENVIRONMENT_TYPE and improved validation logic.
   * @since 2020.12.14 Added SAVEQUERIES and FORCE_SSL_ADMIN.
   *
   * @param string           $name   The name of the constant to define.
   * @param string|bool|null $value  The value of the constant or null to remove its declaration.
   * @return bool                     Whether the constant was updated, added or removed successfully.
   */
  public function set_constant( string $name, $value )
  {
    /**
     * If content could not be read, return false.
     */
    if ( $this->read() === false ) {
      return false;
    }

    /**
     * Sanitize.
     */
    $name = wpessentials_trim( wp_strip_all_tags( $name ), '' );

    /**
     * Validation: $name should be whitelisted and $value should be in array or be null.
     * This is done to restrict editing freedom to the critical wp-config.php file.
     */
    $params = array(
      'WP_ENVIRONMENT_TYPE'            => array( 'local', 'development', 'staging', 'production' ),
      'WP_DEBUG'                       => array( true, false ),
      'WP_DISABLE_FATAL_ERROR_HANDLER' => array( true, false ),
      'SCRIPT_DEBUG'                   => array( true, false ),
      'WP_DEBUG_LOG'                   => array(),            // could also contain a path string
      'WP_DEBUG_DISPLAY'               => array( true, false ),
      'SAVEQUERIES'                    => array( true, false ),
      'FORCE_SSL_ADMIN'                => array( true, false ),
    );
    if (
      ! array_key_exists( $name, $params ) ||
      (
        $value !== null &&
        ! empty( $params[ $name ] ) &&
        ! in_array( $value, $params[ $name ] )
      )
    ) {
      return false;
    }

    /**
     * Remove constant declarations from content if NULL was passed.
     */
    if ( $value === null ) {
      $this->contents = preg_replace(
        '/\s*?define\(\s*?[\'"]' . preg_quote( $name, '/' ) . '[\'"]\s*?,\s*?(.*?)\s*?\);\s*?(\r|\r?\n)/im',
        PHP_EOL,
        $this->contents
      );

      return true;
    }

    /**
     * If BOOL ..
     */
    elseif (
      is_bool( $value ) ||
      (
        is_string( $value ) &&
        strtolower( $value ) === 'on'
      )
    ) {
      $content_value = json_encode( $value == true );
    }

    /**
     * If STRING ..
     */
    elseif ( is_string( $value ) ) {
      $content_value = "'" . addslashes( wp_strip_all_tags( $value ) ) . "'";
    }

    /**
     * Else, bail.
     */
    else {
      return false;
    }

    /**
     * Update the first constant declaration in content.
     */
    if ( preg_match( '/define\(\s*?[\'"]' . preg_quote( $name, '/' ) . '[\'"]\s*?,\s*?(.*?)\s*?\);/im', $this->contents, $constants ) ) {
      $this->contents = str_replace(
        $constants[0],
        'define( \'' . $name . '\', ' . $content_value . ' );',
        $this->contents
      );
    }

    /**
     * Add new constant declaration to content.
     */
    else {
      $this->contents = preg_replace(
        '/(\/\*.*?stop\sediting!.*?\*\/)/im',
        'define( \'' . $name . '\', ' . $content_value . ' );' . PHP_EOL . PHP_EOL . '$1',
        $this->contents
      );
    }

    return true;
  }

  /**
   * Changes Authentication Unique Keys and Salts.
   * Generated using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
   *
   * @since 2020.02.21
   * @since 2020.12.10 Improved regex patterns to prevent false-positive matches.
   *
   * @return bool Whether the secret keys were changed successfully.
   */
  public function regenerate_keys()
  {
    /**
     * If content could not be read, return false.
     */
    if ( $this->read() === false ) {
      return false;
    }

    $regex =
    '/(define\s*\(\s*[\'"](AUTH_KEY|SECURE_AUTH_KEY|LOGGED_IN_KEY|NONCE_KEY|AUTH_SALT|SECURE_AUTH_SALT|LOGGED_IN_SALT|NONCE_SALT)[\'"]\s*,\s*[\'"].*[\'"]\s*\)\s*;\s*(\r|\r?\n)+){8}(?=\/\*\*\#@\-\*\/)/im';

    if (
      ! is_wp_error( $response = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' ) )
      // && preg_match( $regex, $response['body'] . `\n/**#@-*/` ) // NOT WORKING ?? !!
    ) {
      $this->contents = preg_replace( $regex, $response['body'], $this->contents );

      return true;
    }

    return false;
  }
}


/**
 * Class for modifying .htaccess.
 *
 * @since 2020.12.10
 */
class WPessentials_HTAccess_Edit extends WPessentials_File_Reader
{
  /**
   * Default options.
   *
   * @var array
   */
  private $options = array(
    'disable_dir_browsing'         => false,
    'protect_sys_files'            => false,
    'disable_php_upload_dir'       => false,
    'no_useragent_no_post_request' => false,
    'no_referer_no_comment'        => false,
    // 'require_ssl' => false,
    // 'prevent_common_exploits' => false,
  );

  /**
   * Make specific private properties readonly.
   *
   * @since 2020.12.10
   * @since 2020.12.10 Added parent __get method to allow for reading contents.
   *
   * @param string $prop  Property name.
   * @return dynamic|null Property value or null on failure.
   */
  public function __get( string $prop )
  {
    if ( in_array( $prop, array( 'options' ), true ) ) {
      return $this->$prop;
    }
    return parent::__get( $prop );
  }

  /**
   * Constructor.
   *
   * Creates an instance of "WPessentials_File_Reader" for wp-config.php.
   *
   * @since 2020.12.10
   */
  public function __construct()
  {
    parent::__construct( self::get_htaccess_path() );

    /**
     * Set current options.
     */
    if ( $this->read() !== false ) {
      $this->options['disable_dir_browsing'] =
      true === preg_match( '/# BEGIN wpessentials.*## Directory protection.*# END wpessentials/ims', $this->contents );

      $this->options['protect_sys_files'] =
      true === preg_match( '/# BEGIN wpessentials.*## Protect system files.*# END wpessentials/ims', $this->contents );

      $this->options['disable_php_upload_dir'] =
      true === preg_match( '/# BEGIN wpessentials.*## Disable PHP in uploads directory.*# END wpessentials/ims', $this->contents );

      $this->options['no_useragent_no_post_request'] =
      true === preg_match( '/# BEGIN wpessentials.*## No UserAgent, no POST request.*# END wpessentials/ims', $this->contents );

      $this->options['no_referer_no_comment'] =
      true === preg_match( '/# BEGIN wpessentials.*## No referer, no comment.*# END wpessentials/ims', $this->contents );
    }
  }

  /**
   * Retrieve the path to .htaccess.
   *
   * @since 2020.12.10
   *
   * @return string The filepath for .htaccess.
   */
  public static function get_htaccess_path()
  {
    if (
      ! file_exists( $path = ABSPATH . '.htaccess' ) &&
      file_exists( dirname( ABSPATH ) . '/.htaccess' )
    ) {
      $path = dirname( ABSPATH ) . '/.htaccess';
    }

    /**
     * Filter the .htaccess filepath, return an empty string to avoid modifications to this file.
     *
     * @since 2020.12.10
     */
    return apply_filters( 'wpessentials_htaccess_path', $path );
  }

  /**
   * Get the path portion of a URL.
   *
   * @since 2020.12.10
   *
   * @param string $url The URL to extract the path from.
   *
   * @return string|bool The relative path portion or false if the path could not be determined.
   */
  public static function get_relative_url_path( $url )
  {
    $url      = parse_url( $url, PHP_URL_PATH );
    $home_url = parse_url( home_url(), PHP_URL_PATH );

    $path = preg_replace( '/^' . preg_quote( $home_url, '/' ) . '/', '', $url, 1, $count );

    if ( 1 === $count ) {
      return trim( $path, '/' );
    }

    return false;
  }

  /**
   * Remove the code block from .htaccess.
   *
   * @since 2020.12.10
   */
  public function remove_rules()
  {
    /**
     * If content could not be read, return false.
     */
    if ( $this->read() === false ) {
      return false;
    }

    $this->contents = preg_replace( '/(\r|\r?\n)*# BEGIN wpessentials.*# END wpessentials/ims', PHP_EOL, $this->contents );

    return true;
  }

  /**
   * Regenerate the code block in .htaccess containing the preferred configuration.
   *
   * @since 2020.12.10
   *
   * @link https://www.askapache.com/htaccess/
   */
  public function regenerate_rules( array $options )
  {
    /**
     * If content could not be read, return false.
     */
    if ( $this->read() === false ) {
      return false;
    }

    $options = wp_parse_args( $options, $this->options );

    $htaccess_block = "# BEGIN wpessentials\n";

    if ( ! empty( $options['disable_dir_browsing'] ) ) {
      $htaccess_block .= "\n\t## Directory protection\n";

      $htaccess_block .= "\tOptions -Indexes\n";
    }

    if ( ! empty( $options['protect_sys_files'] ) ) {
      $files = implode(
        '|',
        array(
          '\.htaccess',
          '\.htpasswd',
          '\.ini',
          '\.phps',
          '\.fla',
          '\.psd',
          '\.log',
          '\.sh',
          '/wp-admin/includes/.*',
          '/wp-admin/install\.php',
          'wp-config\.php',
          'readme\.(txt|html?)',
        )
      );

      $htaccess_block .= "\n\t## Protect system files\n";

      $htaccess_block .= "\t<FilesMatch \"($files)$\">\n";
      $htaccess_block .= "\t\tOrder Allow,Deny\n";
      $htaccess_block .= "\t\tDeny from all\n";
      $htaccess_block .= "\t</FilesMatch>\n";
    }

    if ( ! empty( $options['disable_php_upload_dir'] ) ) {
      $url = self::get_relative_url_path( wp_upload_dir()['baseurl'] );

      if ( ! empty( $url ) ) {
        $url = preg_quote( $url );

        $htaccess_block .= "\n\t## Disable PHP in uploads directory\n";

        $htaccess_block .= "\tRewriteRule ^$url/.*\.(phps?\d?|pht|phtml?)$ - [NC,F,L]\n";
      }
    }

    if ( ! empty( $options['no_useragent_no_post_request'] ) ) {
      $htaccess_block .= "\n\t## No UserAgent, no POST request\n";

      $htaccess_block .= "\tRewriteCond %{REQUEST_METHOD} POST\n";
      $htaccess_block .= "\tRewriteCond %{HTTP_USER_AGENT} ^-?$\n";
      $htaccess_block .= "\tRewriteRule .* - [NS,F,L]\n";
    }

    if ( ! empty( $options['no_referer_no_comment'] ) ) {
      $htaccess_block .= "\n\t## No referer, no comment\n";

      $htaccess_block .= "\tRewriteCond %{REQUEST_METHOD} POST\n";
      $htaccess_block .= "\tRewriteCond %{REQUEST_URI} .*/wp-comments-post\.php$\n";
      $htaccess_block .= "\tRewriteCond %{HTTP_REFERER} ^-?$\n";
      $htaccess_block .= "\tRewriteRule .* - [NS,F,L]\n";
    }

    $htaccess_block .= "\n# END wpessentials";

    $this->remove_rules();

    $this->contents .= "\n$htaccess_block"; // WHY DOUBLED ?? !!
  }
}


/**
 * Class for reading debug log.
 *
 * @since 2020.06.17
 * @since 2020.12.10 Now extends WPessentials_File_Reader class.
 */
class WPessentials_Debug_Log_Read extends WPessentials_File_Reader
{
  /**
   * Constructor.
   *
   * Creates an instance of "WPessentials_File_Reader" for debug.log.
   *
   * @since 2020.06.17
   */
  public function __construct()
  {
    parent::__construct( self::get_debug_log_path()['path'] );
  }

  /**
   * Retrieve the path or url to debug.log.
   *
   * @since 2020.06.17
   * @since 2020.12.10 Now returns both path and url in one array.
   *
   * @return array {
   *  @type string $path The filepath for debug.log.
   *  @type string $url  The url for debug.log.
   * }
   */
  public static function get_debug_log_path()
  {
    if (
      defined( 'WP_DEBUG_LOG' ) &&
      is_string( WP_DEBUG_LOG )
    ) {
      $path = rtrim( get_home_path(), '/' ) . '/' . ltrim( WP_DEBUG_LOG, '/' );
      $url  = home_url( ltrim( WP_DEBUG_LOG, '/' ) );
    } else {
      $path = WP_CONTENT_DIR . '/debug.log';
      $url  = content_url( 'debug.log' );
    }

    /**
     * Filter the debug.log filepath.
     *
     * @since 2020.06.17
     */
    $path = apply_filters( 'wpessentials_debug_log_path', $path );

    /**
     * Filter the debug.log url.
     *
     * @since 2020.06.17
     */
    $url = apply_filters( 'wpessentials_debug_log_url', $url );

    return compact( 'path', 'url' );
  }
}
