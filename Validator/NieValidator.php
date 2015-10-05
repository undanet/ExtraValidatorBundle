<?php

namespace Undanet\ExtraValidatorBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class NieValidator extends ConstraintValidator
{
    protected $nieFormatExpr = '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/';
    protected $standardNieExpr = '^/[T]{1}[A-Z0-9]{8}$/';
    protected $avaliableLastChar = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /**
     * @param $value
     * @param Constraint $constraint
     * @return bool
     * @deprecated In Symfony 2.6 does not work but remains backward compatibility 2.3
     */
    public function isValid($value, Constraint $constraint)
    {
        $this->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        $ret = $this->checkNie($value);

        if (!$ret) {
            $this->setMessage($constraint->message);
        }

        return $ret;
    }

    private function splitNie($nie)
    {
        return str_split($nie, 1);
    }

    protected function checkNieFormat($nie)
    {
        return preg_match($this->nieFormatExpr, $nie);
    }

    /**
     * @param $dni
     * @return boolean
     */
    protected function checkNie($dni)
    {
        $dni = strtoupper($dni);

        // Invalid format
        if (!$this->checkNieFormat($dni)) {
            return false;
        }

        // Standard Nie
        if ( $this->checkStandardNie($dni)) {
            return true;
        }

        return false;
    }

    protected function checkStandardNie($nie)
    {
        $nieCharacters = $this->splitNie($nie);

        //T
        if (preg_match('/^[T]{1}/', $nie))
            return ($nieCharacters[8] == preg_match($this->checkStandardNie(), $nie));

        //XYZ
        if (preg_match('/^[XYZ]{1}/', $nie))
            return ($nieCharacters[8] == substr($this->avaliableLastChar, substr(str_replace(array('X','Y','Z'), array('0','1','2'), $nie), 0, 8) % 23, 1));

        return false;
    }

}