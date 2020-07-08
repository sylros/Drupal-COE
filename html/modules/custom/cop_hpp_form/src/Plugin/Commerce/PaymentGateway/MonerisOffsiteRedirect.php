<?php

namespace Drupal\cop_hpp_form\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Off-site Redirect payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "moneris_offsite_redirect",
 *   label = "Moneris (Off-site redirect)",
 *   display_label = "Moneris Hosted Pay Page Redirect",
 *   forms = {
 *     "offsite-payment" = "Drupal\cop_hpp_form\PluginForm\MonerisRedirect",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "mastercard", "visa",
 *   },
 *   requires_billing_information = FALSE,
 * )
 */
class MonerisOffsiteRedirect extends OffsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'redirect_method' => 'post',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // A real gateway would always know which redirect method should be used,
    // it's made configurable here for test purposes.
    $form['redirect_method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Redirect method'),
      '#options' => [
        'get' => $this->t('Redirect via GET (302 header)'),
        'post' => $this->t('Redirect via POST (automatic)'),
        'post_manual' => $this->t('Redirect via POST (manual)'),
      ],
      '#default_value' => 'post',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['redirect_method'] = $values['redirect_method'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    // @todo Add examples of request validation.
    // Note: Since requires_billing_information is FALSE, the order is
    // not guaranteed to have a billing profile. Confirm that
    // $order->getBillingProfile() is not NULL before trying to use it.
    if($order->getBillingProfile() !== NULL) {
      $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
      $payment = $payment_storage->create([
        'state' => 'completed',
        'amount' => $order->getBalance(),
        'payment_gateway' => $this->parentEntity->id(),
        'order_id' => $order->id(),
        'remote_id' => $request->query->get('txn_id'),
        'remote_state' => $request->query->get('payment_status'),
      ]);
      $payment->save();
    }
  }

}
