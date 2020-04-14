<?php

namespace Drupal\demo_form\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid telephone number.
 *
 * @Constraint(
 *   id = "TelephoneNumber",
 *   label = @Translation("Telephone Number", context = "Validation"),
 *   type = "string"
 * )
 */
class TelephoneNumberConstraint extends Constraint {

  // The message that will be shown if the value is not an international number (ie local).
  public $notAPhoneNumber = '%value is not a phone number';

  // The message that will be shown if the value has invalid characters
  public $notInteger = '%value contains invalid characters';

  // The message that will eb shown if the phone number is international
  public $internationalPhoneNumber = '%value is an international phone number';

}
