<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\entity_print\Controller\EntityPrintController;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\NodeCreationTrait;

/**
 * @coversDefaultClass \Drupal\entity_print\PrintBuilder
 * @group entity_print
 */
class TranslationTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'node',
    'filter',
    'language',
    'entity_print',
    'entity_print_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['system', 'filter']);
    $this->container->get('theme_handler')->install(['stark']);
    $node_type = NodeType::create(['name' => 'Page', 'type' => 'page']);
    $node_type->setDisplaySubmitted(FALSE);
    $node_type->save();

    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('en')->save();
    $this->container->get('language_manager')->reset();
  }

  /**
   * Test the translated version of the printed document.
   */
  public function testTranslatedEntity() {
    $node = $this->createNode(['title' => 'english']);
    $node->addTranslation('de', ['title' => 'german'])->save();
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache([$node->id()]);

    $this->assertSame('english', $node->getTitle());
    $this->assertSame('german', $node->getTranslation('de')->getTitle());

    $controller = EntityPrintController::create($this->container);

    // Ensure we get the English version of the node by default.
    $this->assertContains('english', (string) $controller->viewPrintDebug('pdf', 'node', $node->id()));

    // Change the default language and ensure we get the German version.
    $this->config('system.site')->set('default_langcode', 'de')->save();
    $this->assertContains('german', (string) $controller->viewPrintDebug('pdf', 'node', $node->id()));
  }

}
