<?php

namespace Drupal\cop_moneris\Event;

use Symfony\Component\EventDispatcher\Event;

class NewPaymentProcessedEvent extends Event {
  //A moneris transaction reply
  protected $transaction;

  public function __construct($transaction) {
    $this->transaction = $transaction;
  }

  public function getTransaction() {
    return $this->transaction;
  }
}
