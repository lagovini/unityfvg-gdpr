<?php

/**
 * @file
 * Contains \Drupal\faqfield\Plugin\Field\FieldWidget\FaqFieldDefaultWidget.
 */

namespace Drupal\faqfield\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Plugin implementation of the 'faqfield_default' widget.
 *
 * @FieldWidget(
 *   id = "faqfield_default",
 *   label = @Translation("FAQ Field"),
 *   field_types = {
 *     "faqfield"
 *   }
 * )
 */
class FaqFieldDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'answer_widget' => 'text_format',
        'question_title' => t('Question'),
        'answer_title' => t('Answer'),
        'advanced' => [
          'question_length' => 255,
          'question_size' => 100,
          'question_rows' => 0,
          'answer_rows' => 3,
        ],
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Add textfield for question.
    $element['question'] = [
      '#title' => SafeMarkup::checkPlain($this->getSetting('question_title')),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->question) ? $items[$delta]->question : '',
      '#maxlength' => $this->getSetting('advanced')['question_length'],
      '#size' => $this->getSetting('advanced')['question_size'],
      '#delta' => $delta,
      '#weight' => 0,
    ];
    // Question textfield can be configured to be a textarea.
    $question_rows = $this->getSetting('advanced')['question_rows'];
    if ($question_rows > 0) {
      $element['question']['#type'] = 'textarea';
      $element['question']['#rows'] = $question_rows;
    }

    // Add textarea / formatable textarea / textfield for answer.
    $element['answer'] = [
      '#title' => SafeMarkup::checkPlain($this->getSetting('answer_title')),
      '#type' => $this->getSetting('answer_widget'),
      '#default_value' => isset($items[$delta]->answer) ? $items[$delta]->answer : '',
      '#delta' => $delta,
      '#weight' => 1,
      '#rows' => $this->getSetting('advanced')['answer_rows'],
    ];
    // We choose the source output format depending on the input type.
    if ($this->getSetting('answer_widget') == 'text_format') {
      $default_format = $this->getFieldSetting('default_format');
      $element['answer']['#format'] = (isset($items[$delta]->answer_format)) ? $items[$delta]->answer_format : $default_format;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    // Input for the count of rows for the answer field.
    $elements['answer_widget'] = [
      '#type' => 'select',
      '#title' => $this->t('Answer widget'),
      '#default_value' => $this->getSetting('answer_widget'),
      '#options' => [
        'textarea' => $this->t('Textarea'),
        'text_format' => $this->t('Formatable textarea'),
        'textfield' => $this->t('Textfield'),
      ],
      '#required' => TRUE,
      '#description' => $this->t('What form widget to use for answer input. Formatable textarea is needed for WYSIWYG editors.'),
    ];

    // Input for custom title of questions.
    $elements['question_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Question widget title'),
      '#placeholder' => t('Question'),
      '#default_value' => $this->getSetting('question_title'),
      '#description' => $this->t('Custom title of question widget.'),
      '#maxlength' => 50,
      '#size' => 20,
    ];

    // Input for custom title of answers.
    $elements['answer_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Answer widget title'),
      '#placeholder' => t('Answer'),
      '#default_value' => $this->getSetting('answer_title'),
      '#description' => $this->t('Custom title of answer widget.'),
      '#maxlength' => 50,
      '#size' => 20,
    ];

    // We put more advanced settings into a collapsed fieldset.
    $elements['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#collapsed' => TRUE,
    ];

    // Input for the maximum length of questions.
    $elements['advanced']['question_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Question length'),
      '#placeholder' => 255,
      '#default_value' => $this->getSetting('advanced')['question_length'],
      '#description' => $this->t('Maximum length of questions (Between 1 and 255).'),
      '#min' => 1,
      '#max' => 255,
      '#step' => 1,
    ];

    // Input for the size of the question input.
    $elements['advanced']['question_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Question field size'),
      '#placeholder' => 100,
      '#default_value' => $this->getSetting('advanced')['question_size'],
      '#description' => $this->t('Width of the question widget.'),
      '#min' => 10,
      '#max' => 255,
      '#step' => 1,
    ];

    // Input for the count of rows for the answer field.
    $elements['advanced']['question_rows'] = [
      '#type' => 'select',
      '#title' => $this->t('Question widget'),
      '#default_value' => $this->getSetting('advanced')['question_rows'],
      '#options' => [
        t('Fieldset'),
        t('Textarea, 1 row'),
        t('Textarea, 2 row'),
        t('Textarea, 3 row'),
        t('Textarea, 4 row'),
      ],
      '#required' => TRUE,
      '#description' => $this->t('Number of rows used for the question textfield/textarea.'),
    ];

    // Input for the count of rows for the answer field.
    $elements['advanced']['answer_rows'] = [
      '#type' => 'number',
      '#title' => $this->t('Answer rows'),
      '#placeholder' => 3,
      '#default_value' => $this->getSetting('advanced')['answer_rows'],
      '#description' => $this->t('Number of rows used for the answer textarea.'),
      '#required' => TRUE,
      '#states' => [
        'invisible' => [
          ':input[id="edit-fields-field-faq-settings-edit-form-settings-answer-widget"]' => ['value' => 'textfield'],
        ],
      ],
      '#min' => 1,
      '#step' => 1,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    // Answer widget setting.
    $answer_widget_options = [
      'textarea' => $this->t('Textarea'),
      'text_format' => $this->t('Formatable textarea'),
      'textfield' => $this->t('Textfield'),
    ];
    $answer_widget = $this->getSetting('answer_widget');
    if (isset($answer_widget_options[$answer_widget])) {
      $summary[] = $this->t('Answer widget') . ': ' . $answer_widget_options[$answer_widget];
    }

    $summary[] = $this->t('Answer widget title') . ': "' . $this->getSetting('answer_title') . '"';
    $summary[] = $this->t('Question widget title') . ': "' . $this->getSetting('question_title') . '"';

    return $summary;
  }

}
