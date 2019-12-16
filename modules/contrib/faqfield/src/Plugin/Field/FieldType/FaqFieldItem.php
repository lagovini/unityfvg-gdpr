<?php

/**
 * @file
 * Contains \Drupal\faqfield\Plugin\Field\FieldType\FaqFieldItem.
 */

namespace Drupal\faqfield\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of the 'faqfield' field type.
 *
 * @FieldType(
 *   id = "faqfield",
 *   label = @Translation("FAQ Field"),
 *   description = @Translation("Stores a question, an answer and its format to
 *   assemble a FAQ."), default_widget = "faqfield_default", default_formatter
 *   = "faqfield_accordion"
 * )
 */
class FaqFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'default_format' => 'plain_text',
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];

    $properties['question'] = DataDefinition::create('string')
      ->setLabel(t('Question value'));

    $properties['answer'] = DataDefinition::create('string')
      ->setLabel(t('Answer value'));

    $properties['answer_format'] = DataDefinition::create('string')
      ->setLabel(t('Answer text format'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'question' => [
          'description' => 'The FAQ Field question values.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
        'answer' => [
          'description' => 'The FAQ Field answer values.',
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium',
        ],
        'answer_format' => [
          'description' => 'The FAQ Field answer format.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    // Get a list of formats that the current user has access to.
    $formats = filter_formats();
    $filter_options = [];
    foreach ($formats as $format) {
      $filter_options[$format->get('format')] = $format->get('name');
    }

    // Format select input for field settings.
    $element['default_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Default text format'),
      '#default_value' => $this->getSetting('default_format'),
      '#options' => $filter_options,
      '#access' => count($formats) > 1,
      '#required' => TRUE,
      '#description' => $this->t('Default text format to filter FAQ field answer content.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $answer = $this->get('answer')->getValue();
    $question = $this->get('question')->getValue();

    $answer_value = is_array($answer) ? $answer['value'] : $answer;

    // Return TRUE only if both are empty.
    return (empty($question) && empty($answer_value));
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'question';
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values['answer']) && is_array($values['answer'])) {
      // Normal textarea's and textfields put their values simply in by
      // array($name => $value); Unfortunately text_format textareas put
      // them into an array so also the format gets saved: array($name
      // => array('value' => $value, 'format' => $format)).
      // So the API will try to save normal textfields to the 'name' field
      // and text_format fields to 'answer_value' and 'answer_format'.
      // To bypass this, we pull the values out of this array and force
      // them to be saved in 'answer' and 'answer_format'.
      $values['answer_format'] = $values['answer']['format'];
      $values['answer'] = $values['answer']['value'];
    }
    parent::setValue($values, $notify);
  }

}
