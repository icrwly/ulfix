<?php

namespace Drupal\ul_json\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\ul_json\Export;

/**
 * Returns responses for ul_json routes.
 */
class UlJsonController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Ul Json Export.
   *
   * @var object
   */
  protected $export;

  /**
   * SerializerInterface object.
   *
   * @var object
   */
  protected $serializer;

  /**
   * Constructs a new ReportCommands object.
   *
   * @param \Drupal\ul_json\Export $export
   *   Report Service object.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   Report Serializer object.
   */
  public function __construct(Export $export, SerializerInterface $serializer) {
    $this->export = $export;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ul_json.export'),
      $container->get('serializer')
    );
  }

  /**
   * Builds the response.
   */
  public function build($token) {
    // The arg: 1=>PowerBI.
    $data = $this->export->exportJson($token, 1);
    if (!$data) {
      echo "<b>You do not have permission to acces the JSON data.</b>";
    }
    else {
      // Add header.
      header('Content-Type: application/json; charset=utf-8');
      echo $data;
    }
    exit();
  }

  /**
   * Builds the response for newsletter/emial JSON data.
   */
  public function build2($token) {
    // The arg: 2=>Email.
    $data = $this->export->exportJson($token, 2);
    if (!$data) {
      echo "<b>You do not have permission to acces the JSON data.</b>";
    }
    else {
      // Add header.
      header('Content-Type: application/json; charset=utf-8');
      echo $data;
    }
    exit();
  }

  /**
   * Builds the response for Feed exporting in Chinese intended for China team.
   */
  public function build3($token) {
    // The arg: 3=>Chinese Translation.
    $data = $this->export->exportJson($token, 3);
    if (!$data) {
      echo "<b>You do not have permission to acces the JSON data.</b>";
    }
    else {
      // Add header.
      header('Content-Type: application/json; charset=utf-8');
      echo $data;
    }
    exit();
  }

  /**
   * Builds the response for RSS Feed exporting in Chinese for China team.
   */
  public function build3Rss($token) {
    // $feed = $this->export->exportRss($token, 3);
    // The arg: 3=>Chinese Translation.
    $jsonData = $this->export->exportJson($token, 3);
    // $feed = $this->json2xml($data);
    $feed = json_decode($jsonData, TRUE);

    if (!$feed) {
      echo "<b>You do not have permission to acces the RSS Feed.</b>";
    }
    else {
      // header('Content-Type: application/rss+xml; charset=utf-8');
      // change to text/xml.
      header('Content-Type: text/xml; charset=utf-8');
      header('Content-Disposition: attachment; filename="ul-chinese-nodes.xml"');
      header('Content-Transfer-Encoding: binary');

      // Create root XML element.
      $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><data></data>');

      // Convert array to XML.
      $this->arrayToXml($feed, $xml, 0);
      echo $xml->asXML();
    }
    exit();
  }

  /**
   * Builds the response for RSS Feed exporting in Chinese for China team.
   */
  public function arrayToXml($data, &$xml, $count, $parentKey = NULL) {
    foreach ($data as $key => $value) {

      if ($count == 0 && is_null($key)) {
        $key = 'item_' . $key;
      }
      elseif (is_numeric($key)) {
        // Handle arrays with numeric keys.
        if (!is_null($parentKey) || $parentKey == 0 || $parentKey == "0") {
          $key = 'item_' . $key;
        }
      }

      if (is_array($value)) {
        $subNode = $xml->addChild($key);
        $this->arrayToXml($value, $subNode, $key, $count++);
      }
      else {
        $value = ($value) ? htmlspecialchars($value) : $value;
        $xml->addChild($key, $value);
      }

    }
  }

}
