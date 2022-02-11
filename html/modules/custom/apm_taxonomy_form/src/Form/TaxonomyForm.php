<?php
/**
 * @file
 * Contains \Drupal\demo_api\Form\APITestForm.
 */

namespace Drupal\apm_taxonomy_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;

class TaxonomyForm extends FormBase {
  public function getFormId() {
    return 'update_taxonomies_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $args = NULL) {
    // kint($args);
    $tid = \Drupal::request()->get('_route_params')['taxonomy_term'];
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->load($tid);

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','application')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $applications = Node::loadMultiple($nids);

    $headers = array(
      'application_name' => 'Application Name - ' . $terms->get('name')->getValue()[0]['value']
    );

    $options = array();
    $apps = array();
    $currentValue = array();

    foreach ($applications as $key => $value) {
      $features = $value->get('field_features')->getValue();
      $checkbox = FALSE;

      foreach ($features as $k => $v) {
        if(in_array($tid,$v)) {
          $currentValue[$value->id()] = $value->id();
          break;
        }
      }

      $apps[] = array(
        'nid' => $value->id(),
        'application_name' => $value->get('title')->getValue()[0]['value']
      );

    }

    foreach ($apps as $key => $value) {
      $options[$value['nid']] = array(
        'application_name' => $value['application_name']
      );
    }

    $form['table'] = array(
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $options,
      '#value' => $currentValue
    );

    $form['term_id'] = array(
      '#type' =>'hidden',
      '#value' => $tid,
    );

    $form['app_id'] = array(
      '#type' => 'hidden',
      '#value' => $nids
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Update features'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tableKeys = array_keys($form_state->getCompleteForm()['table']);
    $table = $form_state->getCompleteForm()['table'];
    $withFeature = array();
    $withoutFeature = array();

    $nids = $form_state->getCompleteForm()['app_id']['#value'];
    $fid = $form_state->getCompleteForm()['term_id']['#value'];

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','application')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $applications = Node::loadMultiple($nids);
    $results = array();

    foreach ($applications as $key => $value) {
      $features = $value->get('field_features')->getValue();

      if($this->hasFeature($fid,$features)) {
        $withFeature[] = $value->id();
      } else {
        $withoutFeature[] = $value->id();
      }
    }

    echo 'without';
    kint($withoutFeature);
    echo 'with';
    kint($withFeature);


    foreach ($table as $key => $value) {
      if(is_int($key)) {
        $results[$key] = $value['#checked'];
      }
    }

    // kint($results);

    kint($withFeature);
    kint($applications);

    foreach ($applications as $key => $value) {
      if($results[$value->id()] === TRUE) {
        if(in_array($value->id(),$withoutFeature)) {
          echo 'add feature to appID: ' . $value->id();
          $appFeatures = $applications[$value->id()]->get('field_features')->getValue();
          kint($appFeatures);
          $appFeatures[]['target_id'] = $fid;
          kint($appFeatures);
          $applications[$value->id()]->set('field_features',$appFeatures);
          $applications[$value->id()]->save();
        } else {
          //Do nothing
        }
      } else {
        if(in_array($value->id(),$withoutFeature)) {
          //Do nothing
        } else {
          $appFeatures = $applications[$value->id()]->get('field_features')->getValue();

          foreach ($appFeatures as $k => $v) {
            if($v['target_id'] === $fid) {;
              unset($appFeatures[$k]);
              $applications[$value->id()]->set('field_features',$appFeatures);
              $applications[$value->id()]->save();
            }
          }
        }
      }

    }

    // die();

  }

  public function hasFeature($fid,$features) {
    foreach ($features as $key => $value) {
      if($value['target_id'] === $fid) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
