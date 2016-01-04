<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserAccessControlHandler
 */

namespace Drupal\entity_browser;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the entity browser.
 *
 */
class EntityBrowserAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   *
   * Link the activities to the permissions. checkAccess is called with the
   * $operation as defined in the routing.yml file.
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'administer entity browsers');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'administer entity browsers');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer entity browsers');
    }
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   *
   * Separate from the checkAccess because the entity does not yet exist, it
   * will be created during the 'add' process.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer entity browsers');
  }

}
