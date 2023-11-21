<?php

namespace Drupal\ul_crc_asset\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\ul_crc_asset\Entity\CRCAssetInterface;
use Drupal\Component\Utility\Crypt;

/**
 * Plugin implementation of the 'crc_asset_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "crc_asset_url",
 *   label = @Translation("CRC Asset URL"),
 *   field_types = {
 *     "crc_asset_item"
 *   }
 * )
 */
class CRCAssetURLFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id,
  $plugin_definition,
    FieldDefinitionInterface $field_definition,
  array $settings,
    $label,
  $view_mode,
  array $third_party_settings,
    EntityTypeManager $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Create render array.
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $value = $item->getValue();
    $entity = $this->entityTypeManager->getStorage('crc_asset')->load($value['target_id']);
    if (!empty($entity) && ($entity instanceof CRCAssetInterface)) {
      $url = $entity->getCrcData('original_url');
      // The values will be used directly in templates,
      // so just return them as pure markup.
      if (!empty($url)) {
        // Get and set the name from crc.ul.com if it is changed.
        $name = $entity->getCrcData('name');

        $file_type = $entity->getCrcData('file_type');
        $updated_at = $entity->getCrcData('updated_at');

        // Get default image if the AWS source is not an image.
        $sm_url = $this->getDataCacheCrc($entity->getCrcData('sm_thumbnail_url'), 'img');

        $size = $this->getDataCacheCrc($url, 'size');

        return [
          'name' => ['#markup' => $name],
          'size' => [
            '#markup' => $size,
            '#cache' => [
              'contexts' => [
                'url.site',
              ],
            ],
          ],
          'file_type' => ['#markup' => $file_type],
          'updated_at' => ['#markup' => $updated_at],
          'url' => ['#markup' => $url],
          'sm_thumbnail_url' => [
            '#markup' => $sm_url,
            '#cache' => [
              'contexts' => [
                'url.site',
              ],
            ],
          ],
        ];

      }
    }
  }

  /**
   * Get the file size of any remote resource.
   *
   * @param string $url
   *   Takes the remote object's URL.
   * @param bool $formatSize
   *   Whether to return size in bytes or formatted.
   * @param bool $useHead
   *   Whether to use HEAD requests. If false, uses GET.
   *
   * @return string
   *   Returns human-readable formatted size.
   */
  protected function getFileSize($url, $formatSize = TRUE, $useHead = TRUE) {
    $size = -1;
    if (!isset($url) || filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      return $size;
    }

    if (FALSE !== $useHead) {
      stream_context_set_default(['http' => ['method' => 'HEAD']]);
    }

    if ($head = get_headers($url, 1)) {
      $head = array_change_key_case($head);

      // content-length of download (in bytes), read from Content-Length: field.
      $clen = isset($head['content-length']) ? $head['content-length'] : 0;

      // Cannot retrieve file size, return "-1".
      if (!$clen || !$formatSize) {
        return $size;
      }

      $size = $clen;
      switch ($clen) {
        case $clen < 1024:
          $size = $clen . ' B';
          break;

        case $clen < 1048576:
          $size = round($clen / 1024, 2) . ' KB';
          break;

        case $clen < 1073741824:
          $size = round($clen / 1048576, 2) . ' MB';
          break;

        case $clen < 1099511627776:
          $size = round($clen / 1073741824, 2) . ' GB';
          break;
      }
    }

    return $size;
  }

  /**
   * Get the default thumbnail if the AWS source is not an img.
   *
   * @param string $url
   *   Takes the remote object's URL.
   * @param bool $useHead
   *   Whether to use HEAD requests. If false, uses GET.
   *
   * @return string
   *   Returns a string of URL.
   */
  protected function getImgType($url, $useHead = TRUE) {
    $default = "/profiles/custom/ul_enterprise_profile/themes/custom/ul_enterprise_theme/images/default_file.png";
    if (!isset($url) || filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      return $default;
    }

    if (FALSE !== $useHead) {
      stream_context_set_default(['http' => ['method' => 'HEAD']]);
    }

    // Get content-ype from the file head.
    $head = array_change_key_case(get_headers($url, 1));
    $type = $head["content-type"];
    $imgTypes = ['image/gif', 'image/jpeg', 'image/png'];

    if (array_search($type, $imgTypes)) {
      return $url;
    }

    return $default;
  }

  /**
   * Get or set the Cache data for image type & file szie on AWS source.
   *
   * @param string $url
   *   Takes the remote object's URL.
   * @param string $type
   *   The type of 'img' or 'size'.
   *
   * @return string
   *   Returns a string of URL or Size.
   */
  protected function getDataCacheCrc($url, $type = 'img') {

    $tag = 'crc_file_' . $type;
    $cid = $this->generateCacheId($tag, $url);
    // $data = \Drupal::cache()->get($cid);
    $data = NULL;
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    // Return cached data if it exists.
    if (!empty($data)) {
      return $data;
    }
    else {
      if ($type == 'img') {
        $new = $this->getImgType($url);
      }
      if ($type == 'size') {
        $new = $this->getFileSize($url);
      }
      // Set the cache for the object.
      \Drupal::cache()->set($cid, $new, time() + 24 * 60 * 60, [$tag]);

      return $new;
    }

  }

  /**
   * Generate cache ID.
   *
   * @param string $prefix
   *   The unique cache prefix.
   * @param string $args
   *   An array of request parameters.
   *
   * @return string
   *   The full cache id string.
   */
  private function generateCacheId($prefix, $args) {
    if (!is_string($args)) {
      $args = (string) $args;
    }
    $cache_key = Crypt::hashBase64(serialize($args));
    return $prefix . ':' . $cache_key;
  }

}
