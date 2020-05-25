<?php

namespace Drupal\commerce_invoice;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\EntityAccessControlHandler;

/**
 * Controls access based on the Invoice entity permissions.
 */
class InvoiceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $account = $this->prepareUser($account);

    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = parent::checkAccess($entity, $operation, $account);

    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $entity */
    if ($result->isNeutral() && $operation === 'view') {
      if ($account->id() == $entity->getCustomerId()) {
        $result = AccessResult::allowedIfHasPermissions($account, ['view own commerce_invoice']);
        $result = $result->cachePerUser()->addCacheableDependency($entity);
      }
    }

    return $result;
  }

}
