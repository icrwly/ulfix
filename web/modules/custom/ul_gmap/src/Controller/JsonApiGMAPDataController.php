<?php

namespace Drupal\ul_gmap\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;

/**
 * Class JsonApiGMAPDataController.
 *
 *  Returns a JSON object for use with the UL Global Market Accesss.
 */
class JsonApiGMAPDataController {

  /**
   * Output JSON object.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   JSON object of certifcation content type for parsing in module.
   */
  public function index() {
    return new JsonResponse($this->processData());
  }

  /**
   * Retrieve all published nodes of certification.
   *
   * @return array
   *   Get array of all published nodes of certification content type.
   */
  public function processData() {

    $result = [];

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'market_access_profile')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->sort('title', 'DESC');

    $nodes_ids = $query->execute();

    if ($nodes_ids) {
      foreach ($nodes_ids as $node_id) {
        $node = Node::load($node_id);

        // [ Market Access Profile Mark ].
        // Use media library image (field_mark_media_library) field.
        if (!empty($node->get('field_mark_media_library')->entity)) {
          $fid = $node->get('field_mark_media_library')->target_id;
          // dd($fid);
          $file = Media::load($fid);
          // dd($file);
          $uri = $file->field_media_image->entity->getFileUri();
          // dd($uri);
          // $uri = $file->url();
          $cert_image_url = Url::fromUri(\Drupal::service('file_url_generator')->generateAbsoluteString($uri))->toString();
        }
        // Fallback to file image (field_certification_mark)
        elseif (!empty($node->get('field_certification_mark')->entity)) {
          $fid = $node->get('field_certification_mark')->target_id;
          $file = File::load($fid);
          $uri = $file->getFileUri();
          $cert_image_url = Url::fromUri(\Drupal::service('file_url_generator')->generateAbsoluteString($uri))->toString();
        }
        else {
          $cert_image_url = "";
        }

        // field_shared_country [ Country Taxonomy ].
        if ($node->get('field_shared_country')->target_id != NULL) {
          $countries = [];
          foreach ($node->get('field_shared_country') as $country) {
            $country_id = $country->target_id;
            $country_term = Term::load($country_id)->getName();
            $countries[] = $country_term;
          }
        }
        else {
          $countries = "";
        }

        // field_market_access_requirement.
        // [ Market Access Requirement Taxonomy ].
        if ($node->get('field_market_access_requirement')->target_id != NULL) {
          $reg_types = [];

          foreach ($node->get('field_market_access_requirement') as $reg_type) {
            $reg_type_id = $reg_type->target_id;
            $reg_type_term = Term::load($reg_type_id)->getName();
            $reg_types[] = $reg_type_term;
          }
        }
        else {
          $reg_types = "";
        }

        if ($node->get('field_market_access_label_req')->value == 1) {
          $mark_required = "Yes";
        }
        else {
          $mark_required = "No";
        }

        $result[] = [
          "id" => $node->id(),
          "profile_mark" => $cert_image_url,
          "name_of_mark" => $node->getTitle(),
          "region_country" => $countries,
          "requirements" => $reg_types,
          "mark_required" => $mark_required,
          "validity_period" => $node->get('field_validity_period_map')->value,
        ];

      }
    }

    return $result;

  }

}
