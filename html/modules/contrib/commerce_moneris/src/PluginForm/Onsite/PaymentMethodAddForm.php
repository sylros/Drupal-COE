<?php

namespace Drupal\commerce_moneris\PluginForm\Onsite;

use Drupal\commerce_payment\PluginForm\PaymentMethodAddForm as BasePaymentMethodAddForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class PaymentMethodAddForm extends BasePaymentMethodAddForm {

  /**
   * {@inheritdoc}
   */
  public function buildCreditCardForm(array $element, FormStateInterface $form_state) {
    $element = parent::buildCreditCardForm($element, $form_state);

    //Modification - Add Customer ID Field
    $element['customer_id'] = [
      '#type' => 'textfield',
      '#title' => t('Customer ID'),
      '#maxlength' => 19,
      '#size' => 20,
    ];

    return $element;
  }

}
