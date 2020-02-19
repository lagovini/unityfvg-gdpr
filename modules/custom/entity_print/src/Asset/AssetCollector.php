<?php

namespace Drupal\entity_print\Asset;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\entity_print\Event\PrintCssAlterEvent;
use Drupal\entity_print\Event\PrintEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Collect the assets for the entities being printed.
 */
class AssetCollector implements AssetCollectorInterface {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The info parser for yml files.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * AssetCollector constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, InfoParserInterface $info_parser, EventDispatcherInterface $event_dispatcher) {
    $this->themeHandler = $theme_handler;
    $this->infoParser = $info_parser;
    $this->dispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function getCssLibraries(array $entities) {
    $libraries = [];
    $theme = $this->themeHandler->getTheme($this->themeHandler->getDefault());
    $theme_info = $this->infoParser->parse($theme->getPathname());

    if (isset($theme_info['entity_print'])) {
      // See if we have the special "all" key which is added to every PDF.
      if (isset($theme_info['entity_print']['all'])) {
        $libraries = array_merge($libraries, (array) $theme_info['entity_print']['all']);
        unset($theme_info['entity_print']['all']);
      }

      foreach ($entities as $entity) {
        $this->buildCssForEntity($entity, $theme_info['entity_print'], $libraries);
      }
    }

    $this->dispatcher->dispatch(PrintEvents::CSS_ALTER, new PrintCssAlterEvent($libraries, $entities));

    return $libraries;
  }

  /**
   * Build the CSS for a single entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to build the CSS for.
   * @param array $theme_info
   *   A list of css libraries to add.
   * @param array $libraries
   *   A list of CSS libraries.
   */
  protected function buildCssForEntity(EntityInterface $entity, array $theme_info, array &$libraries) {
    foreach ($theme_info as $key => $value) {
      // If the entity type doesn't match just skip.
      if ($key !== $entity->getEntityTypeId()) {
        continue;
      }

      // Parse our css files per entity type and bundle.
      foreach ($value as $css_bundle => $css) {
        // If it's magic key "all" add it otherwise check the bundle.
        if ($css_bundle === 'all' || $entity->bundle() === $css_bundle) {
          $libraries = array_merge($libraries, (array) $css);
        }
      }
    }
  }

}
