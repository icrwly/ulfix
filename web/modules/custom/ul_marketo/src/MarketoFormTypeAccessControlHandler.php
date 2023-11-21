<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the Marketo Form type entity type.
 *
 * @see \Drupal\ul_marketo\Entity\MarketoFormType
 */
class MarketoFormTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ul_marketo\Entity\MarketoFormInterface $entity */
    switch ($operation) {
      case 'view label':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      default:
        return parent::checkAccess($entity, $operation, $account);
    }
  }

}
