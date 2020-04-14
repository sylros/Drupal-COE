<?php

namespace Drupal\demo_form\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the TelephoneNumber constraint.
 */
class TelephoneNumberConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // First check if the value is an integer.
      if (!$this->isInteger($item->value)) {
        $this->context->addViolation($constraint->notInteger, ['%value' => $item->value]);
      }


      if($this->isInternational($item->value)) {
        $this->context->addViolation($constraint->internationalPhoneNumber, ['%value' => $item->value]);
      }
      // Next check if the value a phone number.
      if (!$this->notInternational($item->value) && !$this->isInternational($item->value)) {
        $this->context->addViolation($constraint->notAPhoneNumber, ['%value' => $item->value]);
      }
    }
  }

  /**
   * Is international
   *
   * @param string $value
   */
  private function isInternational($value) {
    return (preg_match('/^\+[0-9]{1,2}-[0-9]{3}-[0-9]{3}-[0-9]{4}$/',$value));
  }

  /**
   * Is local
   *
   * @param string $value
   */
  private function notInternational($value) {
    return (preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $value));
  }

  private function isInteger($value) {
    $value = str_replace('(','',$value);
    $value = str_replace(')','',$value);
    $value = str_replace('+','',$value);
    $value = str_replace('-','',$value);

    return is_numeric($value);
  }

}
