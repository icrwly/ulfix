<?php

namespace Drupal\ul_marketo\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CacheexcludeSubscriber.
 *
 * @package Drupal\cacheexclude.
 */
class CacheDisableSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['disableCacheForMarketoForm'];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   */
  public function disableCacheForMarketoForm() {
    $config = \Drupal::config('ul_marketo.metadata.settings');
    $path_matches = FALSE;
    if (!$config->get('cache_enable')) {
      $current_path = \Drupal::request()->getRequestUri();
      $definitions = \Drupal::service('plugin.manager.marketo_form')->getDefinitions();

      foreach ($definitions as $definition) {
        if (isset($definition['page'])) {
          if (!empty($definition['page']['default']['path'])) {
            $path = $definition['page']['default']['path'];
          }
          elseif (!empty($definition['page']['entity']['path'])) {
            $path = $definition['page']['entity']['path'];
          }
          else {
            $path = $definition['page']['path'];
          }

          // @todo Add regex.
          if ($path && substr_count($current_path, $path) > 0) {
            $path_matches = TRUE;
            break;
          }
        }
      }

      if ($path_matches) {
        // Sometimes varnish cache is very aggressive. If cache settings are
        // disabled then bypass it.
        \Drupal::service('page_cache_kill_switch')->trigger();
      }
    }
  }

}
