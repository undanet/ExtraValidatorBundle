<?php

namespace Undanet\ExtraValidatorBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class DniValidator extends ConstraintValidator
{
    protected $dniFormatExpr = '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/';
    protected $standardDniExpr = '/(^[0-9]{8}[A-Z]{1}$)/';
    protected $tNieExpr = '^/[T]{1}[A-Z0-9]{8}$/';
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
        $ret = $this->checkDni($value);

        if (!$ret) {
            $this->context->buildViolation($constraint->message)
                ->atPath('dni')
                ->addViolation();
        }

        return $ret;
    }

    private function splitDni($dni)
    {
        return str_split($dni, 1);
    }

    protected function checkDniFormat($dni)
    {
        return preg_match($this->dniFormatExpr, $dni);
    }

    protected function isValidDniLastChar($dni)
    {
        $dniCharacters = $this->splitDni($dni);
        return ($dniCharacters[8] == substr($this->avaliableLastChar, substr($dni, 0, 8) % 23, 1));
    }

    protected function checkStandardDni($dni)
    {
        // Check if standard DNI
        if (preg_match($this->standardDniExpr, $dni)) {
            return $this->isValidDniLastChar($dni);
        }
    }

    protected function checkSpecialDni($dni)
    {
        $dniCharacters = $this->splitDni($dni);

        $plus = $dniCharacters[2] + $dniCharacters[4] + $dniCharacters[6];
        for ($i = 1; $i < 8; $i += 2) {
            $plus += (int) substr((2 * $dniCharacters[$i]), 0, 1) + (int) substr((2 * $dniCharacters[$i]), 1, 1);
        }
        $n = 10 - substr($plus, strlen($plus) - 1, 1);
        if (preg_match('/^[KLM]{1}/', $dni)) {
            return ($dniCharacters[8] == chr(64 + $n) || $this->isValidDniLastChar($dni));
        }
    }

    protected function checkStandardNie($nie)
    {
        $nieCharacters = $this->splitDni($nie);

        //T
        if (preg_match('/^[T]{1}/', $nie))
            return ($nieCharacters[8] == preg_match($this->tNieExpr, $nie));

        //XYZ
        if (preg_match('/^[XYZ]{1}/', $nie))
            return ($nieCharacters[8] == substr($this->avaliableLastChar, substr(str_replace(array('X','Y','Z'), array('0','1','2'), $nie), 0, 8) % 23, 1));

        return false;
    }



    /**
     * @param $dni
     * @return boolean
     */
    protected function checkDni($dni)
    {
        $dni = strtoupper($dni);

        // Invalid format
        if (!$this->checkDniFormat($dni)) {
            return false;
        }

        // Standard Dnis and Nie
        if ($this->checkStandardDni($dni) || $this->checkSpecialDni($dni) || $this->checkStandardNie($dni)) {
            return true;
        }

        return false;
    }

}