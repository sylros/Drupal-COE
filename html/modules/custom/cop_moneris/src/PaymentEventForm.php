<?php

namespace Drupal\cop_moneris\Form;

//Core libraries
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\cop_moneris\Event\PaymentEvent;
use Drupal\cop_moneris\Event\NewPaymentProcessedEvent;

//Commerce libraries
use Drupal\commerce_order\Form\CustomerFormTrait;
use Drupal\commerce_order\Order;

class PaymentEventForm extends FormBase {
  protected $eventDispatcher;

  public function __construct(EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher')
    );
  }

  public function getFormId() {
    return 'moneris_payment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['order_id'] = array(
      '#type' => 'textarea',
      '#title' => 'Order ID',
      '#default_value' => '',
      '#required' => TRUE
    );

    $form['payment'] = array(
      '#type' => 'textarea',
      '#title' => 'Total Price',
      '#default_value' => '',
      '#required' => TRUE
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Make Payment'),
      '#button_type' => 'primary',
    );


    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message("calling this function");
    $oid = $form_state->getValue('order_id');
    $payment = $form_state->getValue('payment');
    $transaction = array(
      'oid' => $oid,
      'payment' => $payment
    );

    $event = new NewPaymentProcessedEvent($transaction);
    $this->eventDispatcher->dispatch(PaymentEvent::NEW_TRANSACTION,$event);

    $order = Order::load($oid);
    // kint($order);
    // die();
  }
}
 ?>
