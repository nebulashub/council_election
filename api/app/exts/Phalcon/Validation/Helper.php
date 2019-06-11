<?php
namespace Phalcon\Validation;

use Phalcon\Validation;
use Phalcon\Validation\ValidatorInterface;
use Phalcon\Validation\CombinedFieldsValidator;

class Helper {

    private $_validators = [];

    private function _add($field, ValidatorInterface $validator, ?string $alias = null)
    {
        $alias = $alias ? $alias : $field;
        if (!isset($this->_validators[$alias])) {
            $this->_validators[$alias] = [];
        }
        $this->_validators[$alias][] = [$field, $validator];
        return $this;
    }

    public function add($field, ValidatorInterface $validator, ?string $alias = null)
    {
        if (is_array($field)) {
            if ($validator instanceof CombinedFieldsValidator) {
                if (empty($alias)) {
                    throw new Exception("alias be required, when use Uniqueness validator");
                }
                $this->_add($field, $validator, $alias);
            } else {
                foreach ($field as $value) {
                    $this->_add($value, $validator);
                }
            }
        } else if (is_string($field)) {
            $this->_add($field, $validator);
        } else {
            throw new Exception("Field must be passed as array of fields or string");
        }
        return $this;
    }

    public function rules($field, array $validators)
    {
        foreach ($validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $this->add($field, $validator);
            }
        }
        return $this;
    }

    public function getValidation(?array $columns = null)
    {
        $validation = new Validation();
        foreach ($this->_validators as $column => $validators) {
            if (!empty($columns) && !in_array($column, $columns)) {
                continue;
            }
            foreach ($validators as $params) {
                $validation->add($params[0], $params[1]);
            }
        }
        return $validation;
    }
}