<?php

namespace Drupal\cop_order\Form;

//Core libraries
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Routing\RedirectResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

//Commerce libraries
use Drupal\commerce_order\Form\CustomerFormTrait;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentMethod;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;

//Events
use Drupal\cop_moneris\Event\PaymentEvent;
use Drupal\cop_moneris\Event\NewPaymentProcessedEvent;
use Drupal\cop_moneris\EventSubscriber\MonerisEventSubscriber;

class OrderForm extends FormBase {
  protected $order;
  protected $customer;

  public function __construct(CurrentRouteMatch $current_route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->order = $current_route_match->getParameter('commerce_order');
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->customer = $this->order->getCustomer();
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'cop_order_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
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
    $dispatcher = \Drupal::service('event_dispatcher');
    $payment_gateway = \Drupal::EntityTypeManager()
      ->getStorage('commerce_payment_gateway')
      ->load('moneris_test');

    $amount = $form_state->getValue('payment');
    $price = Price::fromArray(['number' => $amount,'currency_code' => 'CAD']);

    $transaction = array(
      'price' => $price,
      'payment_gateway' => $payment_gateway,
      'order' => $this->order,
      'customer' => $this->order->getCustomerId(),
    );

    $event = new NewPaymentProcessedEvent($transaction);
    $dispatcher->dispatch(PaymentEvent::NEW_TRANSACTION, $event);
  }
}

?>
