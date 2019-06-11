<?php
namespace App\Middleware;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use App\Components\Utility;
use App\Components\HttpStatusCode;
use App\Components\Config;
use App\Components\CacheMaker as CacheMaker;

/**
 * ResponseMiddleware
 *
 * Manipulates the response
 */
class Response implements MiddlewareInterface
{
    const PAYLOAD = ['code' => HttpStatusCode::OK, 'message' => ''];

    private $_payload = self::PAYLOAD;

    public function resetPayload()
    {
        $this->_payload = self::PAYLOAD;
    }

    public function setCode($code)
    {
        $this->_payload['code'] = $code;
        return $this;
    }

    public function setMessage($message)
    {
        $this->_payload['message'] = $message;
        return $this;
    }

    public function setData($data)
    {
        $this->_payload['data'] = $data;
        return $this;
    }

    public function getPayload()
    {
        return $this->_payload;
    }

    public function handleException(Micro $app, \Throwable $exception)
    {
        $this->resetPayload();
        $response = $app->response->reset();
        if ($exception instanceof \Phalcon\Validation\Exception || $exception instanceof \InvalidArgumentException) {
            $response->setStatusCode(HttpStatusCode::BAD_REQUEST);
        } else if ($exception instanceof \Phalcon\Http\Response\Exception) {
            $response->setStatusCode((int)$exception->getCode());
        } else {
            $response->setStatusCode(HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
        // set code in json
        $this->setCode((int)$response->getStatusCode());
        $this->setMessage($exception->getMessage());
        $response->setContentType("application/json", "UTF-8");
        $response->setJsonContent($this->getPayload(), JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * Before anything happens
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $app)
    {
        if ($app->view->isDisabled() == FALSE) {
            return true;
        }
        $this->resetPayload();
        $response = $app->response;
        $statusCode = (int)$response->getStatusCode();
        
        $this->setCode($statusCode ? $statusCode : HttpStatusCode::OK);
        $handler = $app->getActiveHandler();
        if (is_array($handler) && isset($handler[0]) && $handler[0]->isRawReturned) {
            $response->setContent($app->getReturnedValue());
        } else {
            $payload = '';
            if ($app->getReturnedValue() !== null) {
                if ($app->getReturnedValue() instanceof \stdClass) {
                    $payload = (array)$app->getReturnedValue();
                } else {
                    $this->setData($app->getReturnedValue());
                    $payload = $this->getPayload();
                }
            }
            $response->setContentType("application/json", "UTF-8");
            $response->setJsonContent($payload, JSON_UNESCAPED_UNICODE);
        }
        return true;
    }

}