<?php

namespace Drupal\cop_moneris\PluginForm;

use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

class HostedPayPageRedirectForm extends BasePaymentOffsiteForm {
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form,$form_state);

    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $redirect_method = 'post';
    $remove_js = TRUE;
    $redirect_url = 'https://esqa.moneris.com/HPPDP/index.php';
    $order = $payment->getOrder();

    //Put in data from the order in $data

    $data = array();
    $data['store_id'] = 'A2RGRtore3';
    $data['store_key'] = 'hpUJR27FMKBH';
    $data['total_charge'] = $this->order->get('total_price')->getValue()[0]['number'];
    $data['customer_id'] = $customer->get('uid')->getValue()[0]['value'];

    $data['order_id'] = array(
      '#type' => 'textarea',
      '#title' => 'Order ID',
      '#default_value' => $this->order->get('order_id')->getValue()[0]['value'],
      '#required' => TRUE
    );

    $data['language'] = array(
      '#type' => 'textarea',
      '#title' => 'Language',
      '#default_value' => $customer->language()->getId(),
      '#required' => TRUE
    );

    $data['gst'] = array(
      '#type' => 'textarea',
      '#title' => 'GST',
      '#default_value' => 'Calculate GST from order object',
      '#required' => TRUE
    );

    $data['pst'] = array(
      '#type' => 'textarea',
      '#title' => 'PST',
      '#default_value' => 'Calculate PST from order object',
      '#required' => TRUE
    );

    $data['hst'] = array(
      '#type' => 'textarea',
      '#title' => 'HST',
      '#default_value' => 'Calculate HST from order object',
      '#required' => TRUE
    );

    //Services only? or is shipping cost calculated by us?
    $data['shipping_cost'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipping Cost',
      '#default_value' => '0.0',
      '#required' => TRUE
    );

    //Hosted Pay Page should override this value - Doesn't matter
    $data['eci'] = array(
      '#type' => 'textarea',
      '#title' => 'ECI',
      '#default_value' => '1',
      '#required' => TRUE
    );

    $data['first_name'] = array(
      '#type' => 'textarea',
      '#title' => 'First Name',
      '#default_value' => $customer->get('field_first_name')->getValue()[0]['value'],
      '#required' => TRUE
    );

    $data['last_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Last Name',
      '#default_value' => $customer->get('field_last_name')->getValue()[0]['value'],
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['company_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Company Name',
      '#default_value' => 'Get company name from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['billing address'] = array(
      '#type' => 'textarea',
      '#title' => 'Billing Address',
      '#default_value' => 'Get billing address from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['city'] = array(
      '#type' => 'textarea',
      '#title' => 'City',
      '#default_value' => 'Get city from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['state_province'] = array(
      '#type' => 'textarea',
      '#title' => 'State/Province',
      '#default_value' => 'Get state or province info from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['postal_code'] = array(
      '#type' => 'textarea',
      '#title' => 'Postal code',
      '#default_value' => 'Get postal code from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['phone_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Phone Number',
      '#default_value' => 'Get phone number from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $data['fax_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Fax Number',
      '#default_value' => 'Get fax number from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_first_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment First Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_last_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Last Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_company_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Company Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_address'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment address',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_city'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment City',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_state_province'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment State/Province',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_postal_code'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Postal Code',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_country'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Country',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_telephone'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment telephone',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $data['shipment_fax_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment fax number',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    unset($form['#attached']['library']);
    $form = $this->buildRedirectForm($form,$form_state,$redirect_url,$data,$redirect_method);

    $return $this->buildRedirectForm($form, $form_state);
  }
}
