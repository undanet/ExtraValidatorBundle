<?php

namespace Undanet\ExtraValidatorBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Nie extends Constraint
{
    public $message = "It is not a valid NIE";

    public function requiredOptions()
    {
        return array();
    }

    public function defaultOption()
    {
        return '';
    }

    public function validatedBy()
    {
        return __CLASS__.'Validator';
    }
}
