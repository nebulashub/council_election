<?php
namespace App\Handlers;

use Phalcon\Mvc\Controller;
use App\Components\Utility;
use App\Models\TargetPeriod;
use App\Components\HttpStatusCode;

class ControllerBase extends Controller
{
    public $isRawReturned = false;

    private $_voteTotal = [];

    protected function _getNatRewardTotal($periodNum, $voteNum)
    {
        if (!isset($this->_voteTotal[$periodNum])) {
            $this->_voteTotal[$periodNum] = TargetPeriod::sum(
                [
                    'column' => "support + [against]",
                    'condition' => 'num <= '.$periodNum
                ]
            );
        }
        return bcmul(bcdiv(3000000, $this->_voteTotal[$periodNum], 4), $voteNum, 4);
    }

    public function __call($action, $arguments)
    {
        if (method_exists($this, $action)) {
            $judge = new \ReflectionMethod($this, $action);
            if ($judge->isPublic()) {
                return true;
            }
        }
        $method = $this->request->getMethod();
        $actionClass = str_replace("Controller", '', get_class($this))."\\".Utility::toCamelCase($action)."Action";
        $methodClass = str_replace("Controller", '', get_class($this))."\\".ucfirst(Utility::toCamelCase($action))."\\".ucfirst(strtolower($method))."Method";
        if (!class_exists($methodClass)) {
            if (!class_exists($actionClass)) {
                throw new \Phalcon\Http\Response\Exception('not found: no method', HttpStatusCode::NOT_FOUND);
            } else {
                $class = $actionClass;
            }
        } else {
            $class = $methodClass;
        }
        $o = new $class;
        $o->setDI($this->getDI());
        return call_user_func_array([$o, 'call'], $arguments);
    }

}