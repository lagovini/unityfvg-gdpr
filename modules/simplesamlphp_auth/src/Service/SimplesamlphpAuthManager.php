<?php

namespace Drupal\simplesamlphp_auth\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use SimpleSAML\Auth\Simple;
use SimpleSAML\Configuration;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simplesamlphp_auth\Exception\SimplesamlphpAttributeException;
use Drupal\Core\Site\Settings;
use SimpleSAML\Error\CriticalConfigurationError;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Service to interact with the SimpleSAMLPHP authentication library.
 */
class SimplesamlphpAuthManager {
  use StringTranslationTrait;

  /**
   * A configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * A SimpleSAML configuration instance.
   *
   * @var \SimpleSAML\Configuration
   */
  protected $simplesamlConfig;

  /**
   * A SimpleSAML instance.
   *
   * @var \SimpleSAML\Auth\Simple
   */
  protected $instance;

  /**
   * Attributes for federated user.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructor for SimplesamlphpAuthManager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context to determine whether the route is an admin one.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \SimpleSAML\Auth\Simple $instance
   *   Simple instance.
   * @param \SimpleSAML\Configuration $config
   *   \SimpleSAML\Configuration instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user, AdminContext $admin_context, ModuleHandlerInterface $module_handler, RequestStack $request_stack, MessengerInterface $messenger, Simple $instance = NULL, Configuration $config = NULL) {
    $this->config = $config_factory
      ->get('simplesamlphp_auth.settings');
    $this->currentUser = $current_user;
    $this->adminContext = $admin_context;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
    $this->instance = $instance;
    $this->simplesamlConfig = $config;
  }

  /**
   * Forwards the user to the IdP for authentication.
   */
  public function externalAuthenticate() {
    $uri = $this->requestStack->getCurrentRequest()->getUri();

    $instance = $this->getSimpleSamlInstance();

    if (empty($instance)) {
      return FALSE;
    }

    $instance->requireAuth(['ReturnTo' => $uri]);
  }

  /**
   * Returns a SimpleSAML Simple class instance.
   *
   * @return \SimpleSAML\Auth\Simple|null
   *   The SimpleSAML Simple instance.
   */
  protected function getSimpleSamlInstance() {
    if (!empty($this->instance)) {
      return $this->instance;
    }
    else {

      $this->checkLibrary();

      $auth_source = $this->config->get('auth_source');
      try {
        $this->instance = new Simple($auth_source);
        return $this->instance;
      }
      catch (CriticalConfigurationError $e) {
        if ($this->currentUser->hasPermission('administer simplesamlphp authentication')
          && $this->adminContext->isAdminRoute()) {
          $this->messenger->addError($this->t('There is a Simplesamlphp configuration problem. @message', ['@message' => $e->getMessage()]), 'error');
        }
        return NULL;
      }
    }
  }

  /**
   * Returns a SimpleSAML configuration instance.
   *
   * @return \SimpleSAML\Configuration|null
   *   The SimpleSAML Configuration instance.
   */
  protected function getSimpleSamlConfiguration() {
    if (!empty($this->simplesamlConfig)) {
      return $this->simplesamlConfig;
    }
    else {

      $this->checkLibrary();

      try {
        $this->simplesamlConfig = Configuration::getInstance();
        return $this->simplesamlConfig;
      }
      catch (CriticalConfigurationError $e) {
        if ($this->currentUser->hasPermission('administer simplesamlphp authentication')
          && $this->currentUser->isAdminRoute()) {
          $this->messenger->addError($this->t('There is a Simplesamlphp configuration problem. @message', ['@message' => $e->getMessage()]), 'error');
        }
        return NULL;
      }
    }
  }

  /**
   * Get SimpleSAMLphp storage type.
   *
   * @return string
   *   The storage type.
   */
  public function getStorage() {
    $config = $this->getSimpleSamlConfiguration();
    if (!empty($config) && !empty($config->getValue('store.type'))) {
      return $config->getValue('store.type');
    }
    return NULL;
  }

  /**
   * Check whether user is authenticated by the IdP.
   *
   * @return bool
   *   If the user is authenticated by the IdP.
   */
  public function isAuthenticated() {

    if ($instance = $this->getSimpleSamlInstance()) {
      return $instance->isAuthenticated();
    }

    return FALSE;
  }

  /**
   * Gets the unique id of the user from the IdP.
   *
   * @return string
   *   The authname.
   */
  public function getAuthname() {
    return $this->getAttribute($this->config->get('unique_id'));
  }

  /**
   * Gets the name attribute.
   *
   * @return string
   *   The name attribute.
   */
  public function getDefaultName() {
    return $this->getAttribute($this->config->get('user_name'));
  }

  /**
   * Gets the mail attribute.
   *
   * @return string
   *   The mail attribute.
   */
  public function getDefaultEmail() {
    return $this->getAttribute($this->config->get('mail_attr'));
  }

  /**
   * Gets all SimpleSAML attributes.
   *
   * @return array
   *   Array of SimpleSAML attributes.
   */
  public function getAttributes() {
    if (!$this->attributes) {
      $this->attributes = $this->getSimpleSamlInstance()->getAttributes();
    }
    return $this->attributes;
  }

  /**
   * Get a specific SimpleSAML attribute.
   *
   * @param string $attribute
   *   The name of the attribute.
   *
   * @return mixed|bool
   *   The attribute value or FALSE.
   *
   * @throws \Drupal\simplesamlphp_auth\Exception\SimplesamlphpAttributeException
   *   Exception when attribute is not set.
   */
  public function getAttribute($attribute) {
    $attributes = $this->getAttributes();

    if (isset($attributes)) {
      if (!empty($attributes[$attribute][0])) {
        return $attributes[$attribute][0];
      }
    }

    throw new SimplesamlphpAttributeException(sprintf('Error in simplesamlphp_auth.module: no valid "%s" attribute set.', $attribute));
  }

  /**
   * Asks all modules if current federated user is allowed to login.
   *
   * @return bool
   *   Returns FALSE if at least one module returns FALSE.
   */
  public function allowUserByAttribute() {
    $attributes = $this->getAttributes();
    foreach ($this->moduleHandler->getImplementations('simplesamlphp_auth_allow_login') as $module) {
      if ($this->moduleHandler->invoke($module, 'simplesamlphp_auth_allow_login', [$attributes]) === FALSE) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Checks if SimpleSAMLphp_auth is enabled.
   *
   * @return bool
   *   Whether SimpleSAMLphp authentication is enabled or not.
   */
  public function isActivated() {
    if ($this->config->get('activate') == 1) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Log a user out through the SimpleSAMLphp instance.
   *
   * @param string $redirect_path
   *   The path to redirect to after logout.
   */
  public function logout($redirect_path = NULL) {
    if (!$redirect_path) {
      $redirect_path = base_path();
    }

    if ($instance = $this->getSimpleSamlInstance()) {
      $instance->logout($redirect_path);
    }
  }

  /**
   * Check if the SimpleSAMLphp library can be found.
   *
   * Fallback for when the library was not found via Composer.
   */
  protected function checkLibrary() {
    if ($dir = Settings::get('simplesamlphp_dir')) {
      include_once $dir . '/lib/_autoload.php';
    }

    if (!class_exists('SimpleSAML\Configuration')) {
      $this->messenger->addError($this->t('The SimpleSAMLphp library cannot be found.'));
    }
  }

}
