<?php
/**
 * @file
 * Contains \Drupal\demo_api\Form\APITestForm.
 */

namespace Drupal\apm_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class CheckRelationshipsForm extends FormBase {
  public function getFormId() {
    return 'update_relationships_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Check Relationships'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $dataCentres;
    $servers;
    $environments;
    $applications;
    $persons;

    $DBQuery = \Drupal::entityQuery('node');

    //Get all data centres
    $DBQuery->condition('type','data_centre');
    $DBQuery->condition('status',1);

    $nids = $DBQuery->execute();
    $dataCentres = Node::loadMultiple($nids);

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','server')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $servers = Node::loadMultiple($nids);

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','application')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $applications = Node::loadMultiple($nids);

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','environments')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $environments = Node::loadMultiple($nids);

    $DBQuery = \Drupal::entityQuery('node')
                ->condition('type','application')
                ->condition('status',1);

    $nids = $DBQuery->execute();
    $applications = Node::loadMultiple($nids);
    // die();
    foreach ($applications as $key => $value) {
      $this->checkUserRelation($value);
    }
    // $DBQuery = \Drupal::entityQuery('node')
    //             ->condition('type','person')
    //             ->condition('status',1);
    //
    // $nids = $DBQuery->execute();
    // $persons = Node::loadMultiple($nids);
    // kint($persons);
    // die();
  }

  public function checkUserRelation($application) {
    $developers = array();
    $clients = array();
    $ceg = array();

    $list = $application->get('field_list_of_developers')->getValue();

    foreach ($list as $key => $value) {
      $paragraph = Paragraph::load($value['target_id']);
      $dev = Node::load($paragraph->get('field_person')->getValue()[0]['target_id']);
      $listOfApplications = $dev->get('field_application_list')->getValue();
      $bool = FALSE;

      foreach ($listOfApplications as $key => $value) {
        if($value['target_id'] === $application->id()) {
          $bool = TRUE;
          break;
        }
      }

      if(!$bool) {
        //Add application to user list
        $listOfApplications[] = array(
          'target_id' => $application->id()
        );

        $dev->set('field_application_list',$listOfApplications);
        $dev->save();
      }
    }

    $list = $application->get('field_list_of_ceg')->getValue();

    foreach ($list as $key => $value) {
      $paragraph = Paragraph::load($value['target_id']);
      $ceg = Node::load($paragraph->get('field_ceg')->getValue()[0]['target_id']);
      $listOfApplications = $ceg->get('field_application_list')->getValue();
      $bool = FALSE;

      foreach ($listOfApplications as $key => $value) {
        if($value['target_id'] === $application->id()) {
          $bool = TRUE;
          break;
        }
      }

      if(!$bool) {
        //Add application to user list
        $listOfApplications[] = array(
          'target_id' => $application->id()
        );

        $ceg->set('field_application_list',$listOfApplications);
        $ceg->save();
      }
    }

    $list = $application->get('field_list_of_clients')->getValue();

    foreach ($list as $key => $value) {
      $paragraph = Paragraph::load($value['target_id']);
      $client = Node::load($paragraph->get('field_client')->getValue()[0]['target_id']);
      $listOfApplications = $ceg->get('field_application_list')->getValue();
      $bool = FALSE;

      foreach ($listOfApplications as $key => $value) {
        if($value['target_id'] === $application->id()) {
          $bool = TRUE;
          break;
        }
      }

      if(!$bool) {
        //Add application to user list
        $listOfApplications[] = array(
          'target_id' => $application->id()
        );

        $client->set('field_application_list',$listOfApplications);
        $client->save();
      }
    }

    drupal_set_message("Finished updating relationships for applications");
  }
}
