<?php

namespace Drupal\entity_print\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the Entity Print action tests.
 *
 * @group entity_print
 */
class EntityPrintActionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'entity_print_test', 'views'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create a content type and a dummy node.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Page',
    ]);
    $this->node = $this->drupalCreateNode();

    $account = $this->drupalCreateUser([
      'bypass entity print access',
      'access content overview',
      'administer nodes',
    ]);
    $this->drupalLogin($account);

    // Change to the test PDF implementation.
    $config = \Drupal::configFactory()->getEditable('entity_print.settings');
    $config
      ->set('print_engines.pdf_engine', 'testprintengine')
      ->save();
  }

  /**
   * Test that the download PDF action works as expected.
   */
  public function testDownloadPdfAction() {
    $this->drupalGet('/admin/content');
    $this->drupalPostForm('/admin/content', [
      'action' => 'entity_print_pdf_download_action',
      'node_bulk_form[0]' => 1,
    ], 'Apply to selected items');
    $this->assertText('Using testprintengine');
  }

}
