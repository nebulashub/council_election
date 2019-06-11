<?php
namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;


class IdList extends Validator implements  ValidatorInterface
{

    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);

        $ids = explode(',', $value);
        if (empty($ids)) {
            return false;
        }
        foreach ($ids as $id) {
            if (!preg_match("/^[0-9]+$/", $id) || (int)$id == 0) {
                $label        = $this->prepareLabel($validation, $field);
                $message      = !empty($this->getOption('message')) ? $this->getOption('message') : ':field has an wrong format:'.$id;
                $code         = $this->prepareCode($field);
                $replacePairs = [":field" => $label];

                $validation->appendMessage(new Message(strtr($message, $replacePairs), $field, "Date", $code));
                return false;
            }
        }
        return true;
    }
    

}
