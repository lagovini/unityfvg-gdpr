<?php

/**
 * @file
 * Contains
 *   \Drupal\faqfield\Plugin\field\formatter\FaqFieldDefinitionListFormatter.
 */

namespace Drupal\faqfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'faqfield_definition_list' formatter.
 *
 * @FieldFormatter(
 *   id = "faqfield_definition_list",
 *   label = @Translation("HTML definition list"),
 *   field_types = {
 *     "faqfield"
 *   }
 * )
 */
class FaqFieldDefinitionListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $default_format = $this->getFieldSetting('default_format');

    $element_items = [];
    foreach ($items as $item) {
      // Decide whether to use the default format or the custom one.
      $format = (!empty($item->answer_format) ? $item->answer_format : $default_format);

      $element_items[] = [
        'question' => $item->question,
        'answer' => $item->answer,
        'answer_format' => $format,
      ];
    }

    $elements = [];
    $elements[0] = [
      '#theme' => 'faqfield_definition_list_formatter',
      '#items' => $element_items,
    ];

    return $elements;
  }

}
