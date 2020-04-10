<?php

namespace Drupal\entity_print\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * An event to alter the HTML string.
 */
class PrintHtmlAlterEvent extends Event {

  /**
   * The HTML string.
   *
   * @var string
   */
  protected $html;

  /**
   * An array of entities we're rendering.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities;

  /**
   * PrintHtmlAlterEvent constructor.
   *
   * @param string $html
   *   The generated HTML.
   * @param array $entities
   *   An array of entities we're rendering.
   */
  public function __construct(&$html, array $entities) {
    $this->html = &$html;
    $this->entities = $entities;
  }

  /**
   * Gets the rendered HTML.
   *
   * @return string
   *   The HTML string.
   */
  public function &getHtml() {
    return $this->html;
  }

  /**
   * Gets the entities being rendered.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entities.
   */
  public function getEntities() {
    return $this->entities;
  }

}
