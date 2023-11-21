<?php

namespace Drupal\ul_marketo\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Build Marketo form routes.
 */
class MarketoRouteSubscriber extends RouteSubscriberBase {

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Constructs a new RouteSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The mapper plugin discovery service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {

    // @todo Important!  Need to clear route cache on Marketo Form Type save.
    $entities = $this->entityManager->getStorage('marketo_form_type')->loadMultiple();

    // Creating page.
    if (!empty($entities)) {
      foreach ($entities as $entity) {
        if (!empty($entity->getSetting('path'))) {
          // Marketo form default form.
          $route = new Route(
            // If there's no UUID or entity in the path, go to default.
            $entity->getSetting('path'),
            [
              '_controller' => '\Drupal\ul_marketo\Controller\MarketoFormPageController::defaultForm',
              '_title' => $entity->label(),
            ],
            [
              '_permission' => 'access content',
            ]
          );
          $collection->add('ul_marketo.' . $entity->id() . '.default', $route);

          // Makreto form specific form.
          $options = [
            'parameters' => [
              'identifier' => ['type' => 'string'],
              'entity' => ['type' => 'entity:node'],
            ],
          ];
          // If there is a UUID or entity, try content form.
          $route = new Route(
            $entity->getSetting('path') . '/{identifier}/{entity}',
            [
              '_controller' => '\Drupal\ul_marketo\Controller\MarketoFormPageController::contentForm',
              '_title' => $entity->label(),
            ],
            [
              '_permission' => 'access content',
            ],
            $options
          );
          $collection->add('ul_marketo.' . $entity->id() . '.entity', $route);

          // Success page.
          $route = new Route(
            $entity->getSetting('path') . '/thank-you',
            [
              '_controller' => '\Drupal\ul_marketo\Controller\MarketoFormPageController::defaultSuccess',
              '_title' => 'Thank you!',
            ],
            [
              '_permission' => 'access content',
            ]
          );
          $collection->add('ul_marketo.' . $entity->id() . '.success', $route);
        }
      }
    }
  }

}
