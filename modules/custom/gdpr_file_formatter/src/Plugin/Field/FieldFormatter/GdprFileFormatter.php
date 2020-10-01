<?php
namespace Drupal\gdpr_file_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'customfiledisplay_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "customfiledisplay_field_formatter",
 *   label = @Translation("Custom File Display field formatter"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class GdprFileFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings['link_to_file'] = TRUE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link_to_file'] = [
      '#title' => $this->t('Link this field to the file download URL'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('link_to_file'),
    ];

    return $form;
  }

 /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;
      // kint($file);
      $custom_title = $item->description ? $item->description : $file->getFilename();
      $custom_value = $custom_title . ' - <span class="file-time">' . date('d/m/Y', $file->getCreatedTime()) . '</span>';
      $elements[$delta] = [
        '#theme' => 'file_link',
        '#file' => $file,
        '#description' => $custom_value,
        '#cache' => [
          'tags' => $file->getCacheTags(),
        ],
      ];
      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    //return $item->value;
  }
  

}