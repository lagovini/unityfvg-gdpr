<?php

namespace Drupal\gdpr_menu_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\views\Views;

/**
 * Controller routines for menu example routes.
 *
 * The response of Drupal's HTTP Kernel system's request is generated by
 * a piece of code called the controller.
 *
 * In Drupal 8, we use a controller class
 * for placing those piece of codes in methods which responds to a route.
 *
 * This file will be placed at {module_name}/src/Controller directory. Route
 * entries uses a key '_controller' to define the method called from controller
 * class.
 *
 * @see https://www.drupal.org/docs/8/api/routing-system/introductory-drupal-8-routes-and-controllers-example
 */
class GdprMenuManagementController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  // protected function getModuleName() {
  //   return 'menu_example';
  // }

/**
   * Such callbacks can be user for creating web services in Drupal 8.
   */
  public function informative() {

    $view = Views::getView('le_mie_schede_gdpr');
    $render_array = $view->buildRenderable('page_1');
    
    return array(
        '#type' => 'markup',
        '#markup' => \Drupal::service('renderer')->renderRoot($render_array),
    );
  }
}