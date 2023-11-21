<?php

namespace Drupal\ul_salesforce\Controller;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Render web-to-case forms.
 */
class WebToCaseController extends ControllerBase {

  /**
   * Returns a web to case form.
   *
   * @return array
   *   The themed form.
   */
  public function getForm($form) {

    $f = \Drupal::formBuilder()->getForm("\\Drupal\\ul_salesforce\\Form\\" . $form);
    $renderer = \Drupal::service('renderer');
    $output = $renderer->render($f);

    $response = new Response();
    $response->setContent($output);

    return $response;
  }

  /**
   * Renders a thank you page using the Customer Servive Thank You block.
   *
   * @return array
   *   The themed form.
   */
  public function thankYou() {

    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // Get the Customer Service thank you block id from the db.
    $database = \Drupal::database();
    $q = "SELECT * FROM block_content_field_data WHERE type = 'basic' AND info = 'Customer Service - Thank You (en)'";

    $result = $database->query($q)->fetchAll();

    if (!empty($result)) {
      $bid = $result[0]->id;
      $block = BlockContent::load($bid);
      if ($block->hasTranslation($lang)) {
        $block = $block->getTranslation($lang);
      }

      $build['thank_you'] = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);
      $build['#attached'] = [
        'library' => [
          'ul_salesforce/ul_salesforce.web_to_case',
        ],
      ];

      // Referrer query string param - to return the user.
      $referrer = \Drupal::request()->query->get('referrer');

      // Add the referrer as a Javascript variable.
      $build['#attached']['drupalSettings']['thank_you_referrer'] = $referrer;

      return $build;
    }
  }

  /**
   * Return a title for form or thank you page.
   *
   * @return string
   *   Translated title.
   */
  public function getTitle() {

    $title = $this->t('Contact Us');

    $current_path = \Drupal::service('path.current')->getPath();

    if ($current_path == '/contact-us/thank-you') {
      $title = $this->t("Thank you. We've received your request.");
    }

    return $title;

  }

}
