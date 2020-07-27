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

//Commerce libraries
use Drupal\commerce_order\Form\CustomerFormTrait;
use Drupal\commerce_order\Order;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;

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
    kint($this->order);
    // $form['order_id'] = $order->get

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Make Payment'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}

?>
