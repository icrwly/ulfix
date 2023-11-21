<?php

namespace Drupal\ul_marketo\Controller;

use Drupal\Core\Url;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\EntityBase;
use Drupal\ul_marketo\Entity\MarketoForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ul_marketo\Entity\MarketoFormInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide Marketo-connected form pages.
 */
class MarketoFormPageController extends ControllerBase {

  /**
   * The Marketo service.
   *
   * @var Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketo;

  /**
   * The current path.
   *
   * @var string
   */
  protected $currentPath;

  /**
   * Entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Current Route Match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param string $current_path
   *   The current path.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager interface.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\ul_marketo\UlMarketoServiceInterface $marketo
   *   The marketo service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The Current Route Match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(string $current_path, EntityTypeManagerInterface $entity_type_manager, PathMatcherInterface $path_matcher, EntityRepositoryInterface $entity_repository, UlMarketoServiceInterface $marketo, CurrentRouteMatch $current_route_match, Connection $connection) {
    $this->currentPath = $current_path;
    $this->entityTypeManager = $entity_type_manager;
    $this->pathMatcher = $path_matcher;
    $this->entityRepository = $entity_repository;
    $this->marketo = $marketo;
    $this->currentRouteMatch = $current_route_match;
    $this->connection = $connection;
  }

  /**
   * Create a new instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.current')->getPath(),
      $container->get('entity_type.manager'),
      $container->get('path.matcher'),
      $container->get('entity.repository'),
      $container->get('ul_marketo'),
      $container->get('current_route_match'),
      $container->get('database')
    );
  }

  /**
   * Renders the Marketo Form.
   *
   * @return array
   *   The themed marketo form.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function defaultForm(Request $request) {

    $current_path = $this->currentPath;

    $entity_types = $this->entityTypeManager->getStorage('marketo_form_type')->loadMultiple();
    foreach ($entity_types as $entity_type) {
      // Match the path. If path matches then create temporary marketo form
      // entity, if applicable pass the node id as a parent so that it
      // can use that data when it renders the form.
      // @todo Rewrite with regex?
      // Or perhaps make this into a helper function.
      /** @var \Drupal\ul_marketo\Entity\MarketoFormType $entity_type */
      $patterns = [];
      if (!empty($entity_type->getSetting('path'))) {
        $patterns = [
          $entity_type->getSetting('path'),
          $entity_type->getSetting('path') . '/*',
        ];
      }
      $pattern = implode("\n", $patterns);
      $match = $this->pathMatcher->matchPath($current_path, $pattern);

      if ($match) {
        // If an argument is sent then use that form. Otherwise construct a
        // new form from that marketo form type.
        $marketo_form = $request->get('entity');
        if (empty($marketo_form) || $marketo_form->bundle() != $entity_type->id()) {
          // Call function of UlMarketoServiceInterface to get default
          // MarketoForm.
          $marketo_form = $this->marketo->getOrCreateDefaultMarketoForm($entity_type->id());
        }

        $form = $this->buildView($marketo_form, $entity_type->label());

        return $form;
      }
    }
    return [];
  }

  /**
   * Renders the Marketo Contact Form in a node context.
   *
   * @todo When node IDs are completely removed from URLs, adjust this function
   *   to only take one paramater.
   *
   * @param string $identifier
   *   The UUID of referring page.
   * @param \Drupal\Core\Entity\EntityBase $entity
   *   The referenced entity from the route.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return mixed
   *   The themed marketo form or false if not a valid UUID.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function contentForm($identifier, EntityBase $entity, Request $request) {
    $current_route = $this->currentRouteMatch->getRouteName();
    if (Uuid::isValid($identifier)) {
      // @todo Include this in mentioned parent entity method when built.
      $parent_entity = $this->entityRepository->loadEntityByUuid('node', $identifier);
      // Check if we're using a node.
      if ($parent_entity instanceof NodeInterface && $this->hasMarketoFormFields($parent_entity)) {
        // Get all forms referenced by the node.
        $parent_forms = $parent_entity->get('field_shared_marketo_custom');
        $form_types = $parent_forms->referencedEntities();

        // Get parent settings.
        $settings = $this->marketo->getEntitySettings($parent_entity);
        if (count($form_types) > 0) {
          foreach ($form_types as $type) {

            // Check if the form ID is part of the current route.  Assuming
            // we're on a page.
            if ($type) {
              if (in_array($type->bundle(), explode('.', $current_route)) && !$settings) {
                $settings['form_entity'] = $type;
              }
            }
          }
        }

        if (!$parent_entity->get('field_shared_marketo_forms')->isEmpty()) {
          $settings_field = $parent_entity->get('field_shared_marketo_forms')[0];
          $entity_settings = $settings_field->getValue();
        }
        else {
          return $this->defaultForm($request);
        }
        // Get instance settings.
        if (is_array($settings)) {
          $settings = array_merge($settings, $entity_settings);
        }
        else {
          $settings = $entity_settings;
        }

        $marketo_form = isset($settings['form_entity']) ? $settings['form_entity'] : NULL;

        if ($marketo_form instanceof MarketoForm) {
          $form = $this->buildView($marketo_form, $settings);
        }
        else {
          $form = $this->defaultForm($request);
        }
        // Build a view of the current form to render on page.
        // @todo Add user facing title for form pages.
        return $form;
      }
    }
    // If this doesn't match to any content or the content doesn't have this
    // form type attached, we'll use a default form for it.
    return $this->defaultForm($request);

  }

  /**
   * Renders the Marketo Contact Form Thank you page.
   *
   * @return array
   *   The themed thank you page.
   */
  public function defaultSuccess(Request $request) {
    $current_path = $this->currentPath;

    $entity_types = $this->entityTypeManager->getStorage('marketo_form_type')->loadMultiple();
    foreach ($entity_types as $entity_type) {
      // Match the path. If path matches then create temporary marketo form
      // entity, if applicable pass the node id as a parent so that it
      // can use that data when it renders the form.
      // @todo Rewrite with regex?
      $patterns = [];
      if (!empty($entity_type->getSetting('path'))) {
        $patterns = [
          $entity_type->getSetting('path'),
          $entity_type->getSetting('path') . '/*',
        ];
      }
      $pattern = implode("\n", $patterns);
      $match = $this->pathMatcher->matchPath($current_path, $pattern);

      if ($match) {
        // If an argument is sent then use that form. Otherwise construct a
        // new form from that marketo form type.
        $marketo_form = $request->get('entity');
        if (empty($marketo_form) || $marketo_form->bundle() != $entity_type->id()) {
          // Load the default field values.
          $marketo_form = MarketoForm::create([
            'type' => $entity_type->id(),
          ]);
        }

        $viewBuilder = $this->entityTypeManager->getViewBuilder('marketo_form');
        $form = $viewBuilder->view($marketo_form, 'default');

        return [
          '#theme' => 'marketo_form_success',
          '#title' => 'Thank you',
          '#message' => 'Thank you for your submission.',
          '#form' => $form,
        ];
      }
    }
    return [];
  }

  /**
   * Get success page url.
   *
   * @return string
   *   URL to marketo success page.
   */
  public function getSuccessUrl($marketo_settings) {
    return Url::fromRoute('ul_marketo.' . $marketo_settings['id'] . '.success');
  }

  /**
   * This is where the view building magic starts.
   *
   * @param \Drupal\ul_marketo\Entity\MarketoFormInterface $marketo_form
   *   The current marketo form entity.
   * @param mixed $settings
   *   Settings data.
   *
   * @return array
   *   Renderable array.
   */
  public function buildView(MarketoFormInterface $marketo_form, $settings) {
    $label = isset($settings['label']) ? ($settings['label']) : 'Marketo';
    $viewBuilder = $this->entityTypeManager->getViewBuilder('marketo_form');
    $form = $viewBuilder->view($marketo_form, 'default');
    // @TODO: Add user facing title for form pages.
    return [
      '#theme' => 'marketo',
      '#title' => $label,
      '#form' => $form,
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
    ];
  }

  /**
   * Check if a node has marketo_form fields.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The current node entity.
   *
   * @return bool
   *   Renderable TRUE or FALSE.
   */
  public function hasMarketoFormFields(NodeInterface &$entity) {
    if ($entity->hasField('field_shared_marketo_custom') && $entity->hasField('field_shared_marketo_forms')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
