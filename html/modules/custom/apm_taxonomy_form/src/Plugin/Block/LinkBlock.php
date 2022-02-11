<?php

namespace Drupal\apm_taxonomy_form\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "link_block",
 *   admin_label = @Translation("Taxonomy Links"),
 * )
 */
class LinkBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('application_features');
    $links = array();

    foreach ($terms as $key => $value) {
      $links[] = '<a href=\taxonomy\\' . $value->tid . '>' . $value->name . '</a><br>';
    }

    $markup = '';

    foreach ($links as $key => $value) {
      $markup .= $value;
    }

    return [
      '#markup' => $this->t($markup),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
