<?php

namespace Drupal\csims_users\Form;

//Core libraries
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Routing\RedirectResponse;

class AttendForm extends FormBase {
  protected $event;

  public function __construct(CurrentRouteMatch $current_route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->event= $current_route_match->getParameter('public_engagement_activity');
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'attend_event_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    if($this->event === NULL) {
      $query = \Drupal::request();
      $this->event = Node::load($query->get('plublic_engagement_activity'));
    }

    $form['event'] = array(
      '#type' => 'textarea',
      '#title' => 'Event Id',
      '#default_value' => $this->event->id(),
    );

    $form['action']['#type'] = 'actions';
    $form['action']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Attend',
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = Node::load($form_state->getValue('event'));
    $user = \Drupal::currentUser()->id();
    $currentAttendList = $node->get('field_participant_list')->getValue();
    $isAttending = FALSE;

    foreach ($currentAttendList as $key => $value) {
      if($value['target_id'] === $user) {
        $isAttending = TRUE;
      }
    }

    if(!$isAttending) {
      $currentAttendList[] = array('target_id' => $user);
    } else {
      echo 'already in list';
    }

    $node->set('field_participant_list',$currentAttendList);
    $node->save();

    return;
  }
}
