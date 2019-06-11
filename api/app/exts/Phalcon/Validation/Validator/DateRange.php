<?php
namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator\Date as DateValidator;

class DateRange extends Validator
{
    public function validate(Validation $validation, $field)
    {
        $format  = $this->getOption("format");
        $value   = $validation->getValue($field);
        $minimum = $this->getOption("minimum");
        $maximum = $this->getOption("maximum");
        $mineq   = $this->getOption('mineq', true) === false ? false : true;
        $maxeq   = $this->getOption('maxeq', true) === false ? false : true;
        $message = $this->getOption('message');

        if (is_array($format)) {
            $format = isset($format[$field]) ? $format[$field] : null;
        }
        if (empty($format)) {
            $format = 'Y-m-d';
        }
        if (is_array($minimum)) {
            $minimum = isset($minimum[$field]) ? $minimum[$attribute] : null;
        }
        if (is_array($maximum)) {
            $maximum = isset($maximum[$field]) ? $maximum[$field] : null;
        }
        if (is_array($message)) {
            $message = isset($message[$field]) ? $message[$field] : null;
        }
        if (empty($minimum) && empty($maximum)) {
            throw new Exception("minimum or maximum be required at least one");
        }
        if (!($date = $this->_formatDate($format, $value))) {
            return false;
        }
        if (!empty($minimum) && !($minimum = $this->_formatDate($format, $minimum))) {
            throw new Exception("minimum has a wrong format");
        }
        if (!empty($maximum) && !($maximum = $this->_formatDate($format, $maximum))) {
            throw new Exception("maximum has a wrong format");
        }
        if (!empty($minimum) && !empty($maximum)) {
            if ($maximum < $minimum) {
                throw new Exception("maximum must greater than minimum");
            }
        }
        try {
            if (!empty($minimum)) {
                if ($mineq && $date < $minimum) {
                    throw new \Exception("Field :field must not less than :min");
                }
                if (!$mineq && $date <= $minimum) {
                    throw new \Exception("Field :field must greater than :min");
                }
            }
            if (!empty($maximum)) {
                if ($maxeq && $date > $maximum) {
                    throw new \Exception("Field :field must not greater than :max");
                }
                if (!$maxeq && $date >= $maximum) {
                    throw new \Exception("Field :field must less than :max");
                }
            }
        } catch (\Exception $e) {
            $label        = $this->prepareLabel($validation, $field);
            $message      = $this->prepareMessage($validation, $field, "Date");
            $code         = $this->prepareCode($field);
            $replacePairs = [":field" => $label];
            if (!empty($minimum)) {
                $replacePairs[':min'] = $minimum->format($format);
            }
            if (!empty($maximum)) {
                $replacePairs[':max'] = $maximum->format($format);
            }
            if (empty($message)) {
                $message = $e->getMessage();
            }
            $validation->appendMessage(new Message(strtr($message, $replacePairs), $field, "Date", $code));
            return false;
        }
        return true;
    }


    private function _formatDate($format, $value)
    {
        $date = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();
        if ($errors["warning_count"] > 0 || $errors["error_count"] > 0) {
            return false;
        }
        return $date;
    }

}