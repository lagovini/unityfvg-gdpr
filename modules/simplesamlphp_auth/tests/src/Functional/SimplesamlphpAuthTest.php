<?php

namespace Drupal\Tests\simplesamlphp_auth\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests authentication via SimpleSAMLphp.
 *
 * @group simplesamlphp_auth
 */
class SimplesamlphpAuthTest extends BrowserTestBase {

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'block',
    'simplesamlphp_auth',
    'simplesamlphp_auth_test',
  ];

  /**
   * An administrator user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer users',
      'administer blocks',
    ]);

    // Configure SimpleSAMLphp for testing purposes.
    $this->config('simplesamlphp_auth.settings')
      ->set('activate', 1)
      ->set('mail_attr', 'mail')
      ->set('unique_id', 'uid')
      ->set('user_name', 'displayName')
      ->set('login_link_display_name', "Federated test login")
      ->set('allow.default_login_users', $this->adminUser->id())
      ->save();
  }

  /**
   * Test the SimplesamlphpAuthBlock Block plugin.
   */
  public function testFederatedLoginLink() {

    // Check if the SimpleSAMLphp auth link is shown on the login form.
    $this->drupalGet('user/login');
    $this->assertSession()->pageTextContains(t('Federated test login'));

    $this->drupalLogin($this->adminUser);
    $default_theme = $this->config('system.theme')->get('default');

    // Add the SimplesamlphpAuthBlock to the sidebar.
    $this->drupalGet('admin/structure/block/add/simplesamlphp_auth_block/' . $default_theme);
    $edit = [];
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/simplesamlphp_auth_block/' . $default_theme, $edit, t('Save block'));

    // Assert Login link in SimplesamlphpAuthBlock.
    $this->assertSession()->elementTextContains('css', '.region-sidebar-first .block-simplesamlphp-auth-block h2', 'SimpleSAMLphp Auth Status');
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains(t('Federated test login'));
    $this->assertSession()->linkByHrefExists('saml_login');

    // Disable and ensure the link is no longer shown.
    $this->config('simplesamlphp_auth.settings')
      ->set('activate', FALSE)
      ->save();

    $this->drupalGet('user/login');
    $this->assertSession()->pageTextNotContains(t('Federated test login'));

  }

}
