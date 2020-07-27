<?php

namespace Drupal\cop_hpp_form\PluginForm;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class MonerisRedirect extends BasePaymentOffsiteForm {
  public function buildConfigurationForm(array $form,FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form,$form_state);

    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $redirect_method = 'post';
    $remove_js = 'TRUE';

    $host = 'https://esqa.moneris.com/HPPDP/index.php';
    $redirect_url = $host;//Create URL from URI
    // $redirect_url = Url::fromRoute('commerce_payment_example.dummy_redirect_post')->toString();
    $redirect_method = 'post';

    $order = $payment->getOrder();
    $customer = $order->getCustomer();

    // kint($order);
    // kint($customer);
    // die();

    //Set data from the Order and Customer object which should be in the Payment object
    $data = [
      'ps_store_id' => '6FP88tore3',
      'hpp_key' => 'hpR32IFNLST2',
      'charge_total' => $order->get('total_price')->getValue()[0]['number'],
      'order_id' => $order->get('order_id')->getValue()[0]['value'] + 100000000,
      'cust_id' => $customer->get('uid')->getValue()[0]['value'],
      'lang' => $customer->language()->getId(),
      'ECI' => 1,
    ];

    // kint($redirect_url);
    // kint($data);
    // kint($redirect_method);
    // die();

    $form = $this->buildRedirectForm($form, $form_state, $redirect_url, $data, self::REDIRECT_POST);
    // kint($form);
    return $form;
  }
}
