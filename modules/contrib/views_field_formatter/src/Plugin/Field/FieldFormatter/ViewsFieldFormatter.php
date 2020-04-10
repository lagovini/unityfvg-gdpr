<?php

declare(strict_types = 1);

namespace Drupal\views_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Entity\View;
use Drupal\views\Views;

/**
 * Class ViewsFieldFormatter.
 *
 * @FieldFormatter(
 *     id="views_field_formatter",
 *     label=@Translation("View"),
 *     weight=100,
 *     field_types={
 *         "boolean",
 *         "changed",
 *         "comment",
 *         "computed",
 *         "created",
 *         "datetime",
 *         "decimal",
 *         "email",
 *         "entity_reference",
 *         "entity_reference_revisions",
 *         "expression_field",
 *         "file",
 *         "float",
 *         "image",
 *         "integer",
 *         "language",
 *         "link",
 *         "list_float",
 *         "list_integer",
 *         "list_string",
 *         "map",
 *         "path",
 *         "string",
 *         "string_long",
 *         "taxonomy_term_reference",
 *         "text",
 *         "text_long",
 *         "text_with_summary",
 *         "timestamp",
 *         "uri",
 *         "uuid"
 *     }
 * )
 */
class ViewsFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    list($view_id) = \explode('::', $this->getSetting('view'), 2);
    // Don't call the current view, as it would result into an
    // infinite recursion.
    // TODO: Check for infinite loop here.
    if ($view_id !== NULL && $view = View::load($view_id)) {
      $dependencies[$view->getConfigDependencyKey()][] = $view->getConfigDependencyName();
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'view' => '',
      'arguments' => [
        'field_value' => ['checked' => TRUE],
        'entity_id' => ['checked' => TRUE],
        'delta' => ['checked' => TRUE],
        'entity_revision_id' => ['checked' => TRUE],
      ],
      'hide_empty' => FALSE,
      'multiple' => FALSE,
      'implode_character' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $options = [];

    foreach (Views::getAllViews() as $view) {
      foreach ($view->get('display') as $display) {
        $options[$view->get('label')][$view->get('id') . '::' . $display['id']] =
                    \sprintf('%s - %s', $view->get('label'), $display['display_title']);
      }
    }

    if ([] === $options) {
      $element['help'] = [
        '#markup' => '<p>' . $this->t('No available Views were found.') . '</p>',
      ];

      return $element;
    }

    $element['view'] = [
      '#title' => $this->t('View'),
      '#description' => $this->t("Select the view that will be displayed instead of the field's value."),
      '#type' => 'select',
      '#default_value' => $this->getSetting('view'),
      '#options' => $options,
    ];

    $element['arguments'] = [
      '#type' => 'table',
      '#header' => [$this->t('View Arguments'), $this->t('Weight')],
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'arguments-order-weight',
      ],
      ],
      '#caption' => $this->t(
        'Select the arguments to send to the views, you can reorder them.
                          These arguments can be used as contextual filters in the selected View.'
      ),
    ];

    $default_arguments = \array_keys(\array_filter($this->getSetting('arguments'), static function ($argument) {
      return $argument['checked'];
    }));

    $arguments = \array_combine($default_arguments, $default_arguments);

    foreach ($this->getDefaultArguments() as $argument_id => $argument_name) {
      $arguments[$argument_id] = $argument_name;
    }

    foreach ($arguments as $argument_id => $argument_name) {
      $element['arguments'][$argument_id] = [
        'checked' => [
          '#type' => 'checkbox',
          '#title' => $argument_name,
          '#default_value' => \in_array($argument_id, $default_arguments, TRUE),
        ],
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $argument_name]),
          '#title_display' => 'invisible',
          '#attributes' => ['class' => ['arguments-order-weight']],
        ],
        '#attributes' => ['class' => ['draggable']],
      ];
    }

    $element['hide_empty'] = [
      '#title' => $this->t('Hide empty views'),
      '#description' => $this->t('Do not display the field if the view is empty.'),
      '#type' => 'checkbox',
      '#default_value' => (bool) ($this->getSetting('hide_empty')),
    ];

    $element['multiple'] = [
      '#title' => $this->t('Multiple'),
      '#description' => $this->t(
        'If the field is configured as multiple (<em>greater than one</em>),
                          should we display a view per item ? If selected, there will be one view per item.'
      ),
      '#type' => 'checkbox',
      '#default_value' => (bool) ($this->getSetting('multiple')),
    ];

    $element['implode_character'] = [
      '#title' => $this->t('Implode with this character'),
      '#description' => $this->t(
        'If it is set, all field values are imploded with this character (<em>ex: a simple comma</em>)
                          and sent as one views argument. Empty to disable.'
      ),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('implode_character'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' .
          $this->fieldDefinition->getName() .
          '][settings_edit_form][settings][multiple]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    // For default settings, don't show a summary.
    if ($settings['view'] === '') {
      return [
        $this->t('Not configured yet.'),
      ];
    }

    list($view, $view_display) = \explode('::', $settings['view'], 2);
    $multiple = ((bool) $settings['multiple'] === TRUE) ? 'Enabled' : 'Disabled';
    $hide_empty = ((bool) $settings['hide_empty'] === TRUE) ? 'Hide' : 'Display';

    $arguments = \array_filter($settings['arguments'], static function ($argument) {
      return $argument['checked'];
    });

    $all_arguments = $this->getDefaultArguments();
    $arguments = \array_map(static function ($argument) use ($all_arguments) {
      return $all_arguments[$argument];
    }, \array_keys($arguments));

    if ([] === $arguments) {
      $arguments[] = $this->t('None');
    }

    if ($view !== NULL) {
      $summary[] = \t('View: @view', ['@view' => $view]);
      $summary[] = \t('Display: @display', ['@display' => $view_display]);
      $summary[] = \t('Argument(s): @arguments', ['@arguments' => \implode(', ', $arguments)]);
      $summary[] = \t('Empty views: @hide_empty empty views', ['@hide_empty' => $hide_empty]);
      $summary[] = \t('Multiple: @multiple', ['@multiple' => $multiple]);
    }

    if (((bool) $settings['multiple'] === TRUE) && ($settings['implode_character'] !== '')) {
      $summary[] = \t('Implode character: @character', ['@character' => $settings['implode_character']]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();
    $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();

    if (isset($settings['view']) && !empty($settings['view']) && \mb_strpos($settings['view'], '::') !== FALSE) {
      list($view_id, $view_display) = \explode('::', $settings['view'], 2);
    }
    else {
      return $elements;
    }

    $arguments = $this->getArguments($items, $items[0], 0);

    // If empty views are hidden, execute view to count result.
    if (!empty($settings['hide_empty'])) {
      $view = Views::getView($view_id);

      if (!$view || !$view->access($view_display)) {
        return $elements;
      }

      // We try to reproduce the arguments which will be used below. We cannot
      // just use $this->getArguments($items, $items[0], 0) as this might return
      // items, which for example no longer exist, still you want to show the
      // view when there are more possible entries.
      if (((bool) $settings['multiple'] === TRUE) && ($cardinality !== 1)) {
        if (!empty($settings['implode_character'])) {
          $arguments = $this->getArguments($items, NULL, 0);
        }
      }
      $view->setArguments($arguments);
      $view->setDisplay($view_display);
      $view->preExecute();
      $view->execute();

      if (empty($view->result)) {
        return $elements;
      }
    }

    $elements = [
      '#cache' => [
        'max-age' => 0,
      ],
      [
        '#type' => 'view',
        '#name' => $view_id,
        '#display_id' => $view_display,
        '#arguments' => $arguments,
      ],
    ];

    if (($cardinality !== 1) && ((bool) $settings['multiple'] === TRUE)) {
      if (empty($settings['implode_character'])) {
        foreach ($items as $delta => $item) {
          $elements[$delta] = [
            '#type' => 'view',
            '#name' => $view_id,
            '#display_id' => $view_display,
            '#arguments' => $this->getArguments($items, $items[$delta], $delta),
          ];
        }
      }
    }

    return $elements;
  }

  /**
   * Helper function. Returns the arguments to send to the views.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The items.
   * @param mixed $item
   *   The current item.
   * @param mixed $delta
   *   The item delta.
   *
   * @return array
   *   The arguments array.
   */
  protected function getArguments(FieldItemListInterface $items, $item, $delta) {
    $settings = $this->getSettings();

    $user_arguments = \array_keys(\array_filter($settings['arguments'], static function ($argument) {
      return $argument['checked'];
    }));

    $arguments = [];

    foreach ($user_arguments as $argument) {
      switch ($argument) {
        case 'field_value':
          $columns = \array_keys(
            $items->getFieldDefinition()->getFieldStorageDefinition()->getSchema()['columns']
            );
          $column = \array_shift($columns);
          $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();

          /** @var FieldItemInterface $item */
          if ($item) {
            $arguments[$argument] = !empty($column) && isset($item->getValue()[$column]) ?
                            $item->getValue()[$column] : NULL;
          }

          if (((bool) $settings['multiple'] === TRUE) && ($cardinality !== 1)) {
            if (!empty($settings['implode_character'])) {
              $values = [];

              /** @var FieldItemInterface $item */
              foreach ($items as $item) {
                $values[] = !empty($column) && isset($item->getValue()[$column]) ?
                                    $item->getValue()[$column] : NULL;
              }

              if (!empty($values)) {
                $arguments[$argument] = \implode($settings['implode_character'], \array_filter($values));
              }
            }
          }

          break;

        case 'entity_id':
          $arguments[$argument] = $items->getParent()->getValue()->id();

          break;

        case 'entity_revision_id':
          $arguments[$argument] = $items->getParent()->getValue()->getRevisionId();

          break;

        case 'delta':
          $arguments[$argument] = isset($delta) ? $delta : NULL;

          break;
      }
    }

    return \array_values($arguments);
  }

  /**
   * Get the default Arguments.
   *
   * @return string[]
   *   The default arguments.
   */
  protected function getDefaultArguments(): array {
    return [
      'field_value' => $this->t('Field value'),
      'entity_id' => $this->t('Entity ID'),
      'delta' => $this->t('Delta'),
      'entity_revision_id' => $this->t('Entity revision ID'),
    ];
  }

}
