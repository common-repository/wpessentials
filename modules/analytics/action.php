<?php

defined( 'ABSPATH' ) || exit;


/**
 * Generate the Google Analytics snippet.
 *
 * @since 2020.02.21
 * @since 2020.12.05 Added analytics.js tag.
 * @since 2021.03.12 Removed analytics.js, now gtag.js only.
 */
function wpessentials_analytics_header()
{
  $setting = wpessentials_get_option( array( 'wpessentials_analytics' ) );

  /**
   * Return if no Tracking ID is set OR
   * admin bypass is enabled while request is for an admin-level user OR
   * all logged-in users are bypassed while request is for logged-in user.
   */
  if (
    empty( $setting['trackingid'] ) ||
    (
      (
        ! empty( $setting['bypass_administrators'] ) ||
        ! empty( $setting['bypass_loggedin'] )
      ) &&
      is_user_logged_in()
    ) ||
    (
      ! empty( $setting['bypass_administrators'] ) &&
      current_user_can( 'manage_options' )
    )
  ) {
    return;
  } else {
    $ga_id = $setting['trackingid'];
  }

  /**
   * Set additional options.
   */
  $options = array(
    'allow_ad_personalization_signals' => empty( $setting['disable_ads'] ),
    'allow_google_signals'             => empty( $setting['disable_ads'] ),
    'anonymize_ip'                     => ! empty( $setting['anonymize'] ),
  );

  /**
   * Print snippet.
   */
  ?>
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js',new Date());
  <?php
  foreach ( $options as $option => $value ) :
    ?>
      gtag('set','<?php echo esc_js( esc_html( $option ) ); ?>',<?php echo json_encode( $value ); ?>);
    <?php
  endforeach;
  ?>
    gtag('config','<?php echo esc_js( esc_html( $ga_id ) ); ?>',<?php echo json_encode( $options ); ?>);
  </script>
  <?php
}

/**
 * Print snippet on header or footer.
 *
 * @since 2020.02.21
 * @since 2020.12.05 Merged admin area option.
 * @since 2020.12.10 Added filter for opt-out purposes.
 */
if ( apply_filters( 'wpessentials_do_analytics', true ) ) {
  switch ( wpessentials_get_option( array( 'wpessentials_analytics', 'snippet_location' ), '' ) ) {
    case 'footer':
      add_action( 'wp_footer', 'wpessentials_analytics_header' );
      if ( wpessentials_get_option( array( 'wpessentials_analytics', 'admin_area' ) ) ) {
        add_action( 'admin_footer', 'wpessentials_analytics_header' );
      }
      break;

    default:
      add_action( 'wp_head', 'wpessentials_analytics_header' );
      if ( wpessentials_get_option( array( 'wpessentials_analytics', 'admin_area' ) ) ) {
        add_action( 'admin_head', 'wpessentials_analytics_header' );
      }
  }
}
