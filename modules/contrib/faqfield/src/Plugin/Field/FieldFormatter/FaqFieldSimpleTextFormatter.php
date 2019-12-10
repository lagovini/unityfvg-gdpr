<?php

/**
 * @file
 * Contains \Drupal\faqfield\Plugin\field\formatter\FaqFieldSimpleTextFormatter.
 */

namespace Drupal\faqfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'faqfield_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "faqfield_simple_text",
 *   label = @Translation("Simple text"),
 *   field_types = {
 *     "faqfield"
 *   }
 * )
 */
class FaqFieldSimpleTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $default_format = $this->getFieldSetting('default_format');

    $elements = [];
    foreach ($items as $delta => $item) {
      // Decide whether to use the default format or the custom one.
      $format = (!empty($item->answer_format) ? $item->answer_format : $default_format);

      // Add each Q&A as page element, to be rendered by the faqfield_simple_text_formatter template.
      $elements[$delta] = [
        '#theme' => 'faqfield_simple_text_formatter',
        '#question' => $item->question,
        '#answer' => $item->answer,
        '#answer_format' => $format,
        '#delta' => $delta,
      ];
    }

    return $elements;
  }

}
