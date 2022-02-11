<?php

namespace Drupal\apm_blocks\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "data_centre_block",
 *   admin_label = @Translation("Data Centre Block"),
 * )
 */
class DataCentreBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');

    // kint($node->getType());

    //If page is a node
    if($node !== NULL) {
      $nid = $node->id();

      $DBQuery = \Drupal::entityQuery('node')
                  ->condition('type','data_centre')
                  ->condition('status',1);

      $nids = $DBQuery->execute();
      $dcs = Node::loadMultiple($nids);
      $dc = NULL;

      //Find related data centre
      foreach ($dcs as $key => $value) {
        $servers = $value->get('field_servers')->getValue();

        foreach ($servers as $k => $v) {
          if($v['target_id'] == $nid) {
            $dc = $value;
          }
        }
      }

      if($dc !== NULL) {
        //Create URL to data centre
        $options = ['absolute' => TRUE];
        $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $dc->id()], $options);
        $url = $url->toString();

        $markup = '<b>Data Centre:</b> ' . '<a href=' . $url . '>' . $dc->getTitle() . '</a';

        return [
          '#markup' => $this->t($markup),
        ];
      }

    }

    return ['#markup' => $this->t('Server is not in a known Data Centre, please check your data')];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
