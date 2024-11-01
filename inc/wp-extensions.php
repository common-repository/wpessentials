<?php

/**
 * This file includes essential hooks and filters to alter existing WordPress functionalities.
 *
 * @since 2020.03.14
 */

defined( 'ABSPATH' ) || exit;


/**
 * Always rename "user_nicename" according to "display_name" to avoid brute-force attacks with valid usernames.
 * In order to work properly, the user must set his display name different from his username.
 *
 * @since 2020.03.14
 */
add_filter(
  'wp_pre_insert_user_data',
  function ( $data )
  {
    $data['user_nicename'] = sanitize_title( mb_substr( sanitize_user( $data['display_name'], true ), 0, 49 ) );

    return $data;
  }
);
