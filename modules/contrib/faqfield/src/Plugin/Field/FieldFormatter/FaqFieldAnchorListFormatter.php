<?php

/**
 * @file
 * Contains \Drupal\faqfield\Plugin\field\formatter\FaqFieldAnchorListFormatter.
 */

namespace Drupal\faqfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'faqfield_anchor_list' formatter.
 *
 * @FieldFormatter(
 *   id = "faqfield_anchor_list",
 *   label = @Translation("HTML anchor list"),
 *   field_types = {
 *     "faqfield"
 *   }
 * )
 */
class FaqFieldAnchorListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'anchor_list_type' => 'ul',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    // HTML element type.
    $elements['anchor_list_type'] = [
      '#type' => 'select',
      '#title' => t('Anchor link list type'),
      '#default_value' => $this->getSetting('anchor_list_type'),
      '#options' => [
        'ul' => t('<ul> - Bullet list'),
        'ol' => t('<ol> - Numeric list'),
      ],
      '#description' => t('The type of HTML list used for the anchor link list.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if ($this->getSetting('anchor_list_type') == 'ul') {
      $summary[] = t('Bullet list');
    }
    else {
      $summary[] = t('Numeric list');
    }

    return $summary;
  }

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
      '#theme' => 'faqfield_anchor_list_formatter',
      '#items' => $element_items,
      '#list_type' => $this->getSetting('anchor_list_type'),
    ];

    return $elements;
  }

}
