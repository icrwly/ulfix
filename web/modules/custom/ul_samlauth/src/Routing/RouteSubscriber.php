<?php

namespace Drupal\ul_samlauth\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // @todo This is not capturing routes with parameters.
    // Change path '/user/login' to '/saml/login.php?return=/'.
    // e.g /user/login?destination=admin/modules.
    if ($route = $collection->get('user.login')) {
      $route->setDefault('_controller', '\Drupal\ul_samlauth\Controller\ULSamlauth::redirectUserLogin');
    }

    // Change path '/user/logout' to '/saml/logout.php?return=/'.
    if ($route = $collection->get('user.logout')) {
      $route->setDefault('_controller', '\Drupal\ul_samlauth\Controller\ULSamlauth::redirectUserLogout');
    }

    // Change path '/user/register' to UL registration page.
    if ($route = $collection->get('user.register')) {
      $route->setDefault('_controller', '\Drupal\ul_samlauth\Controller\ULSamlauth::redirectUserRegistration');
    }

    // Change path '/user' to UL user/registration page.
    if ($route = $collection->get('user.page')) {
      $route->setDefault('_controller', '\Drupal\ul_samlauth\Controller\ULSamlauth::redirectUserPage');
    }
  }

}
