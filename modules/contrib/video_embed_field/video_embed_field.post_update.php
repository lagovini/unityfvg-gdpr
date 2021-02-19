<?php

/**
 * @file
 * Post update functions for the video_embed_field module.
 */

/**
 * Sets the default value of the privacy mode to false.
 */
function video_embed_field_post_update_install_privacy_default_settings() {
  \Drupal::service('config.factory')
    ->getEditable('video_embed_field.settings')
    ->set('privacy_mode', 'optional')
    ->save();
}
