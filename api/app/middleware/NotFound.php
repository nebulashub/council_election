<?php
namespace App\Middleware;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use App\Components\HttpStatusCode;

/**
 * RequestMiddleware
 *
 * Check incoming payload
 */
class NotFound extends Response
{
    private static $_indexContent = null;
    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function beforeNotFound(Event $event, Micro $app)
    {
        $app->response->setStatusCode(HttpStatusCode::NOT_FOUND);
        $this->setCode(HttpStatusCode::NOT_FOUND)->setMessage("Nothing to see here.");
        $app->response->setJsonContent($this->getPayload(), JSON_UNESCAPED_UNICODE);
        return false;
    }
}