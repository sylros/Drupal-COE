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

class MonerisEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  public static function getSubscribedEvents() {
    $events[PaymentEvent::NEW_TRANSACTION][] = ['processMessage'];

    return $events;
  }

  public function processMessage(NewPaymentProcessedEvent $event) {
    if($this->processTransaction($event->getTransaction())) {
      //Create or Update a node with infomration from the message
      // $this->messenger()->addStatus($this->t('This is the message: @message',['@message' => $event->getMessage()]));
      // Record success
    } else {
      //Report Issue with message
    }
  }

  public function processTransaction($transaction) {
    drupal_set_message("Processing transaction");
    //Validate Transaction information
    if($this->validateTransaction($transaction)) {
      //Update order entity
      $oid = $transaction['oid'];
      $payment['number'] = $transaction['payment'];
      $payment['currency_code'] = 'CAD';
      $order = Order::load($oid);
      kint($order);
      kint($payment);
      kint($oid);

      $currentBalance = $order->get('total_price');
      kint($currentBalance);
      $currentPayedaBalanced = $order->get('total_paid');
      if($payment > 0 && ($currentBalance->getValue()[0]['number'] - $currentPayedaBalanced->getValue()[0]['number'] - $payment['number'] > 0)) {
        $totalPaid['number'] = $payment['number'] + $currentPayedaBalanced->getValue['number'];
        $totalPaid['currency_code'] = 'CAD';
        // $currentPayedaBalanced->setValue($totalPaid);
        $paid = new Price($totalPaid['number'],$totalPaid['currency_code']);
        $order->setTotalPaid($paid);
        $order->save();
      }

      kint($order);
      die();
    } else {
      return FALSE;
    }


    return TRUE;
  }

  public function validateTransaction($transaction) {
    return TRUE;
  }
}
