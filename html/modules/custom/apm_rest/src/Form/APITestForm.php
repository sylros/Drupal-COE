<?php
/**
 * @file
 * Contains \Drupal\demo_api\Form\APITestForm.
 */

namespace Drupal\apm_rest\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class APITestForm extends FormBase {
  public function getFormId() {
    return 'api_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','server')
            ->condition('nid',33);

    $nid = $DBQuery->execute();

    $node = Node::load(99);
    // kint($node);
    $devs = $node->get('field_list_of_developers')->getValue();
    // kint($devs);
    // // kint($devs);
    // kint(intval('85'));
    // kint('85');
    // foreach ($devs as $key => $value) {
    //   $p = Paragraph::load($value['target_id']);
    //   // kint($p);
    //   if(empty($p->get('field_person')->getValue())) {
    //     kint($p->get('field_person'));
    //     echo 'null';
    //   } else {
    //     kint($p->get('field_person')->getValue());
    //     echo 'not null';
    //   }
    //   $person = Node::load($p->get('field_person')->getValue()[0]['target_id']);
    //   // kint($person);
    // }
    // kint($this->isArrayEmpty($devs));
    // kint($this->isArrayEmpty(array(array())));

    // foreach (array_keys($devs) as $key => $value) {
    //   // if(empty($value))
    // }
    // $appList = $node->get('field_application_list')->getValue();
    // kint($node->get('field_roles')->getValue());

    // $roles = $node->get('field_roles')->getValue();
    // foreach ($roles as $key => $value) {
    //   kint($value['value']);
    // }

    // $apps = $node->get('field_application_list')->getValue();
    // kint($apps);
    //
    // foreach ($apps as $key => $value) {
    //   kint($value['target_id']);
    //   $app = Node::load($value['target_id']);
    //   $data = array(
    //     'nid' => $app->id(),
    //     'title' => $app->get('title')->getValue()[0]['value'],
    //   );
    //
    //   kint($data);
    // }


    // kint($ppl);
    // kint($node->get('field_server')->getValue());
    // kint($node->get('title')->getValue()[0]['value']);//->getValue()[0]['target_id']);
    // kint($this->getMultiValueReferenceField($node,'field_environments'));
    // $form['nid'] =array(
    //   '#type' => 'textfield',
    //   '#title' => 'Number of Messages',
    //   '#default_value' => $node->id(),
    // );
    //
    // $form['api_data'] = array(
    //   '#type' => 'textfield',
    //   '#tile' => 'API Data',
    //   '#default_value' => $node->get('field_servers')->getValue()[0]['target_id'],
    // );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message("Form submitted");
  }

  public function getData() {
    return 'data';
  }

  public function getMultiValueReferenceField($entity, $field) {
    $data = array();

    if($field === 'field_servers') {
      $servers = $entity->get('field_servers')->getValue();

      foreach ($servers as $key => $value) {
        $node = Node::load($value['target_id']);
        $data[] = array(
          'nid' => $node->id(),
          'title' => $node->get('title')->getValue()[0]['value'],
        );
      }

    } else if ($field === 'field_environments') {
      $environments = $entity->get('field_environments')->getValue();

      foreach ($environments as $key => $value) {
        kint($value);
        $data[] = array(
          'nid' => $value->id(),
          'title' => $value->get('title')->getValue(),
        );
      }

    } else if ($field === 'field_applications') {
      $applications = $entity->get('field_applications')->getValue();

      foreach ($applications as $key => $value) {
        $data[] = array(
          'nid' => $value->id(),
          'title' => $value->get('title')->getValue(),
        );
      }

    } else if ($field === 'persons') {
      $developers = $entity->get('field_list_of_developers')->getValue();
      $ceg = $entity->get('field_list_of_ceg')->getValue();
      $clients = $entity->get('field_list_of_clients')->getValue();
      $data = array(
        'developers' => array(),
        'ceg' => array(),
        'clients' => array(),
      );

      drupal_set_message('getting here');
      kint($data);
      kint($developers);

      // kint($ceg);
      // kint($clients);
      foreach ($developers as $key => $value) {
        $dev = Paragraph::load($value['target_id']);
        kint($dev->get('field_person')->getValue()[0]['target_id']);
        $person = Node::load($dev->get('field_person')->getValue()[0]['target_id']);
        $data['developers'][] = array(
          'nid' => $person->id(),
          'title' => $person->get('title')->getValue()[0]['value'],
        );
        kint($data);
      }
      // kint($data);
      foreach ($ceg as $key => $value) {
        $data['ceg'][] = array(
          'nid' => $value->id(),
          'title' => $value->get('title')->getValue()[0]['value'],
        );
      }
      // kint($data);
      foreach ($clients as $key => $value) {
        $data['clients'][] = array(
          'nid' => $value->id(),
          'title' => $value->get('title')->getValue(),
        );
      }
      // kint($data);
    }

    return $data;
  }

  //Used to determine if a 2d array is empty
  public function isArrayEmpty($array) {
    if(array_keys($array)) {
      foreach (array_keys($array) as $key => $value) {
        if(!empty($array[$value])) {
          return FALSE;
        }
      }

      return TRUE;
    }

    return FALSE;
  }
}
 ?>
