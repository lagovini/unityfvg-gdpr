<?php

namespace Drupal\entity_print\Tests\Update;

use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Update from 1.x to 2.x test.
 *
 * @group entity_print
 */
class EntityPrintUpdateTest extends UpdatePathTestBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../tests/fixtures/entity-print-upgrade-1x-2x.php.gz',
    ];
  }

  /**
   * Test a major upgrade from 8.x-1.x to 8.x-2.x.
   */
  public function testMajorUpgrade() {
    $this->runUpdates();

    // Selected PDF engine upgraded.
    $config = $this->config('entity_print.settings');
    $this->assertEqual('dompdf', $config->get('print_engines.pdf_engine'));

    // Dompdf specific settings are upgraded.
    $config = $this->config('entity_print.print_engine.dompdf');
    $this->assertEqual('dompdf', $config->get('id'));
    $this->assertEqual('tabloid', $config->get('settings.default_paper_size'));
    $this->assertEqual(TRUE, $config->get('settings.enable_html5_parser'));
    $this->assertEqual(TRUE, $config->get('settings.enable_remote'));

    // Ensure the VBO plugin name is upgraded.
    $config = \Drupal::configFactory()->getEditable('system.action.entity_print_pdf_download_action');
    $this->assertEqual('entity_print_pdf_download_action', $config->get('id'));
    $this->assertEqual('entity_print_download_action', $config->get('plugin'));
  }

}
