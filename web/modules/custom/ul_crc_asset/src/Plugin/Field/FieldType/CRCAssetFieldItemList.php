<?php

namespace Drupal\ul_crc_asset\Plugin\Field\FieldType;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Form\FormStateInterface;

/**
 * Represents a configurable entity file field.
 */
class CRCAssetFieldItemList extends EntityReferenceFieldItemList {

  /**
   * {@inheritdoc}
   */
  public function defaultValuesForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {

    $entity = $this->getEntity();

    // Set selected assets as permament so that thye're never deleted during
    // garbage collection.
    $assets = $this->referencedEntities();
    $ids = [];
    if (!empty($assets)) {
      foreach ($assets as $asset) {
        $ids[] = $asset->id();
        $asset->setPermanent();
        $asset->save();
      }
    }

    // Setting removed assets as temporary so that they can be deleted during
    // garbage collection.
    $field_name = $this->getFieldDefinition()->getName();
    $original_ids = [];
    $langcode = $this->getLangcode();
    $original = $entity->original;
    if (!empty($original)) {
      if ($original->hasTranslation($langcode)) {
        $original_items = $original->getTranslation($langcode)->{$field_name};
        foreach ($original_items as $item) {
          $original_ids[] = $item->target_id;
        }
      }
    }
    $removed_ids = array_filter(array_diff($original_ids, $ids));

    foreach ($removed_ids as $id) {
      // Load the removed asset.
      $asset = \Drupal::entityTypeManager()->getStorage('crc_asset')->load($id);
      // If this asset isn't used anywhere then set it as temporary to be
      // deleted later.
      $is_used = $asset->getReferencingEntities();
      if (empty($is_used)) {
        $asset->setTemporary();
        $asset->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {

    $assets = $this->referencedEntities();
    if (!empty($assets)) {
      foreach ($assets as $asset) {
        // If this asset isn't used anywhere then set it as temporary to be
        // deleted later.
        $is_used = $asset->getReferencingEntities();
        if (empty($is_used)) {
          $asset->setTemporary();
          $asset->save();
        }
      }
    }

    parent::delete();
  }

}
