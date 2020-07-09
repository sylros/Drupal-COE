<?php

namespace Drupal\cop_moneris\EventSubscriber;

use Drupal\cop_moneris\Event\PaymentEvent;
use Drupal\cop_moneris\Event\NewPaymentProcessedEvent;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\node\Entity\Node;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentMethod;
use Drupal\commerce_payment\Entity\PaymentInterface;


class MonerisEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  public static function getSubscribedEvents() {
    $events[PaymentEvent::NEW_TRANSACTION][] = ['processMessage'];

    return $events;
  }

  public function processMessage(NewPaymentProcessedEvent $event) {
    if($this->processTransaction($event->getTransaction())) {
      \Drupal::logger('cop_moneris')->notice('Transacion processed succesfully');
    } else {
      \Drupal::logger('cop_moneris')->notice('Error processing transaction');
      //Report Issue with message
    }
  }

  public function processTransaction($transaction) {
    drupal_set_message("Processing transaction");

    //Validate Transaction information
    if($this->validateTransaction($transaction)) {
      $payment = Payment::create([
        'type' => 'payment_default',
        'state' => 'Moneris Transaction Response',
        'amount' => $transaction['price'],
        'payment_gateway' => $transaction['payment_gateway']->id(),
        'order_id' => $transaction['order']->id(),
        'remote_id' => '1234567890',
        '$payment_gateway_mode' => $transaction['payment_gateway']->getPlugin()->getMode(),
        'expires' => '0',
        'uid' => $transaction['customer'],
      ]);

      $payment->save();

    } else {
      return FALSE;
    }


    return TRUE;
  }

  public function validateTransaction($transaction) {
    return TRUE;
  }
}
