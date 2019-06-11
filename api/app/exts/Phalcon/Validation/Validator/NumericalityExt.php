<?php
namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class NumericalityExt extends Validator
{
    /**
     * 数值验证器
     *  1、验证是否为数字
     *  2、验证小数点位数
     *  3、验证数值范围
     *
     * @param Phalcon\Validation $validator
     * @param int|float|string $attribute
     * @return boolean
     * 
     * 例子：
     * $validator->add("price",
     *     new NumericalityExtValidator(
     *         [
     *             "decimal" => 2,//小数位数
     *             "min" => 0,//最小值
     *             "max" => 100,//最大值
     *             "message" => "price is not numeric"
     *         ]
     *     )
     * );
     * 
     */
    public function validate(Validation $validation, $field)
    {
        $value      = $validation->getValue($field);
        $message    = $this->getOption("message");
        $decimal    = $this->getOption("decimal", 0);//小数位数
        $max        = $this->getOption("max", null);//最大值
        $min        = $this->getOption("min", null);//最小值
        $maxeq      = $this->getOption("maxeq", true) === false ? false : true;
        $mineq      = $this->getOption("mineq", true) === false ? false : true;

        // 参数验证
        if (empty($value)) {
            return false;
        }
        if (!is_int($decimal)) {
            throw new Exception("decimal must be an intger");
        }
        if ($max !== null && !is_numeric($max)) {
            throw new Exception("max value max be numeric");
        }
        if ($min !== null && !is_numeric($min)) {
            throw new Exception("min value max be numeric");
        }

        try {
            if ($decimal) {
                preg_match("/^-?([1-9]\d*|0)(.[0-9]+)?$", $value, $matches);
                if (!empty($matches[2]) && strlen($matches[2]) - 1 > $decimal) {
                    throw new Exception(":field should be accurate to $decimal decimal places");
                }
            }
            if (!$decimal && !preg_match("/^-?\d+$/", $value)) {
                throw new Exception(":field should be an intger");
            }
            if ($min !== null) {
                if ($mineq && $value < $min) {
                    throw new Exception("Field :field must greater than :min");
                }
                if (!$mineq && $value <= $min) {
                    throw new Exception('Field :field must not less than :min');
                }
            }
            if ($max !== null) {
                if ($maxeq && $value > $max) {
                    throw new Exception("Field :field must not greater than :max");
                }
                if (!$maxeq && $value >= $max) {
                    throw new Exception("Field :field must less than :max");
                }
            }
        } catch (\Exception $e) {
            $label        = $this->prepareLabel($validation, $field);
            $message      = $this->prepareMessage($validation, $field, "Numericality");
            $code         = $this->prepareCode($field);
            $replacePairs = [":field" => $label];
            if ($min !== null) {
                $replacePairs[':min'] = $min;
            }
            if ($max !== null) {
                $replacePairs[':max'] = $max;
            }
            if (empty($message)) {
                $message = $e->getMessage();
            }
            $validation->appendMessage(new Message(strtr($message, $replacePairs), $field, "Numericality", $code));
            return false;
        }
        return true;
    }

}
