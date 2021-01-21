<?php

namespace Drupal\youtube\Plugin\migrate\field\d7;

use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

/**
 * MigrateField Plugin for Drupal 7 YouTube Field fields.
 *
 * @MigrateField(
 *   id = "youtube",
 *   destination_module = "youtube",
 *   source_module = "youtube",
 *   type_map = {
 *     "youtube" = "youtube",
 *   },
 *   core = {7}
 * )
 */
class YouTubeField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      $this->pluginId => $this->pluginId,
    ];
  }

}
