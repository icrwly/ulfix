<?php

namespace Drupal\ul_gmap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Language\LanguageInterface;

/**
 * Controller for UL_GMAP Steps Page.
 *
 * @ingroup ul_gmap
 */
class ULGMAPStepsTwigController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {

    // Do something with your variables here.
    $vid = 'certification_regulatory_types';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $langcode_current = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $config = $this->config('ul_gmap.settings');

    // Get page UUID from admin settings.
    $page_uuid = $config->get('ul_gmap.page_uuid');

    if ($page_uuid != NULL) {
      $page_entity = \Drupal::service('entity.repository')->loadEntityByUuid('node', $page_uuid);
      $node_id = $page_entity->id();
      $spacer = '/';

      // URL to I Don't Know node.
      $notSureURL = $page_entity->toUrl()->toString();

      // Check language and create contact url.
      if ($langcode_current == 'en') {
        $contactURL = '/sales-inquiries' . $spacer . $page_uuid . '/' . $node_id;
      }
      else {
        $contactURL = '/' . $langcode_current . '/sales-inquiries' . $spacer . $page_uuid . '/' . $node_id;
      }
    }
    else {
      $notSureURL = "/";
      $contactURL = "/";
    }

    return [
      // Your theme hook name.
      '#theme' => 'ul_gmap_steps_hook',
      // Your variables.
      '#req_types' => $terms,
      '#contact_url' => $contactURL,
      '#not_sure_url' => $notSureURL,
      '#lang_code' => $langcode_current,
    ];

  }

}
