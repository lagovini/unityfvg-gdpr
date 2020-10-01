<?php

namespace Drupal\Tests\webform_cards\FunctionalJavaScript;

use Drupal\Tests\webform\FunctionalJavascript\WebformWebDriverTestBase;

/**
 * Tests for webform cards auto-forward.
 *
 * @group webform_cards
 */
class WebformCardsAutoForwardJavaScriptTest extends WebformWebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform', 'webform_cards', 'webform_cards_test', 'webform_image_select'];

  /**
   * Test webform cards auto-forward.
   */
  public function testAutoForward() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    /**************************************************************************/

    $this->drupalGet('/webform/test_cards_auto_forward');
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="textfield"]');

    // Check that enter in textfield auto-forwards.
    $session->executeScript('var event = jQuery.Event("keypress"); event.which = 13; jQuery("#edit-textfield").trigger(event);');
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="radios_example"]');

    // Check that radios auto-forwards.
    $session->executeScript('jQuery("#edit-radios-one").mouseup();');
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="radios_other_example"]');

    // Check that clicking radios other 'Other…' does NOT auto-forward.
    $session->executeScript('jQuery("#edit-radios-other-radios-other-").mouseup();');
    $assert_session->waitForElement('css', '#edit-radios-other-other');
    $this->assertCssSelect('.webform-card--active[data-webform-key="radios_other_example"]');

    // Check that clicking radios other option does auto-forward.
    $session->executeScript('jQuery("#edit-radios-other-radios-one").mouseup();');
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="scale"]');

    // Check that clicking scale does auto-forward.
    $session->executeScript('jQuery("#edit-scale-1").change();');
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="rating"]');

    // Check that clicking rating does auto-forward.
    $session->executeScript("jQuery('#edit-rating').val('1').change()");
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="image_select"]');

    // Check that image select does auto-forward.
    $session->executeScript("jQuery('#edit-image-select').val('kitten_1').change()");
    $assert_session->waitForElement('css', '.webform-card--active[data-webform-key="radios_multiple"]');

    // Check that clicking multiple radios does NOT auto-forward.
    $session->executeScript('jQuery("#edit-radios-multiple-1-one, #edit-radios-multiple-1-two").mouseup();');

    // Check that the form can be submitted.
    $page->pressButton('edit-submit');
    $assert_session->pageTextContains('New submission added to Test: Webform: Cards auto-forward.');
  }

}
