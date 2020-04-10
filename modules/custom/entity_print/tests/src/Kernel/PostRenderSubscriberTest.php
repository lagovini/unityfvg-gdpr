<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\entity_print\EventSubscriber\PostRenderSubscriber;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\entity_print\EventSubscriber\PostRenderSubscriber
 * @group entity_print
 */
class PostRenderSubscriberTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_print'];

  /**
   * Test the event subscriber.
   */
  public function testEventSubscriber() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $event = new PrintHtmlAlterTestEvent();
    $subscriber = new PostRenderSubscriber($configFactory, $this->container->get('request_stack'));
    $subscriber->postRender($event);

    // Now change the select PDF engine to phpwkhtmltopdf so we get the
    // exception.
    $config = $configFactory->getEditable('entity_print.settings');
    $data = $config->get('print_engines');
    $data['pdf_engine'] = 'phpwkhtmltopdf';
    $config->set('print_engines', $data);
    $config->save();

    // Try render again and we should get the exception.
    $this->setExpectedException('\Drupal\entity_print\PrintEngineException');
    $subscriber->postRender($event);
  }

}
