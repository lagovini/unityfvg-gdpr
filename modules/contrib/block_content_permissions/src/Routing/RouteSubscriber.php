<?php

namespace Drupal\block_content_permissions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The AccessControlHandler class name.
   *
   * @var string
   */
  private $accessControlHandlerClassName = 'Drupal\block_content_permissions\AccessControlHandler';

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change access callback for the block content type pages.
    $routeNames = [
      'entity.block_content_type.collection',
      'block_content.type_add',
    ];
    foreach ($routeNames as $name) {
      if ($route = $collection->get($name)) {
        $route->addRequirements([
          '_custom_access' => $this->accessControlHandlerClassName . '::blockContentTypeAdministerAccess',
        ]);
        // Remove required "administer blocks" permission.
        $this->removePermissionRequirement($route);
      }
    }

    /* Change access callback for the block content collection page. */
    /* "entity.block_content.collection" route name does not work. */

    // Change access and controller callback for the block content add page.
    if ($route = $collection->get('block_content.add_page')) {
      $route->addRequirements([
        '_custom_access' => $this->accessControlHandlerClassName . '::blockContentAddPageAccess',
      ]);
      $route->setDefault(
        '_controller',
        'Drupal\block_content_permissions\Controller\BlockContentPermissionsAddPageController::add'
      );
      // Remove required "administer blocks" permission.
      $this->removePermissionRequirement($route);
    }

    // Change access callback for the block content add forms.
    if ($route = $collection->get('block_content.add_form')) {
      $route->addRequirements([
        '_custom_access' => $this->accessControlHandlerClassName . '::blockContentAddFormAccess',
      ]);
      // Remove required "administer blocks" permission.
      $this->removePermissionRequirement($route);
    }
  }

  /**
   * Remove required "administer blocks" permission from route.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The Route object.
   */
  private function removePermissionRequirement(Route $route) {
    if ($route->hasRequirement('_permission')) {
      $requirements = $route->getRequirements();
      unset($requirements['_permission']);
      $route->setRequirements($requirements);
    }
  }

}
