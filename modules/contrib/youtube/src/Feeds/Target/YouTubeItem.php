<?php

namespace Drupal\youtube\Feeds\Target;

use Drupal\feeds\Feeds\Target\StringTarget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;

/**
 * Defines a YouTube field mapper.
 *
 * @FeedsTarget(
 *   id = "youtube",
 *   field_types = {
 *     "youtube",
 *   }
 * )
 */
class YouTubeItem extends StringTarget {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('input')
      ->addProperty('video_id');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    // Both the Video URL (input) and Video ID are required for the field value
    // to work. If the feed only provided one, generate the other.
    if (!empty($values['input']) && empty($values['video_id'])) {
      $values['video_id'] = youtube_get_video_id($values['input']);
    }
    elseif (empty($values['input']) && !empty($values['video_id'])) {
      $values['input'] = 'https://www.youtube.com/watch?v=' . $values['video_id'];
    }
  }

}
