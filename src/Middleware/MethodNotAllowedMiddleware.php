<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-router for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Router\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

use const Zend\Expressive\Router\METHOD_NOT_ALLOWED_MIDDLEWARE_RESPONSE;

/**
 * Emit a 405 Method Not Allowed response
 *
 * If the request composes a route result, and the route result represents a
 * failure due to request method, this middleware will emit a 405 response,
 * along with an Allow header indicating allowed methods, as reported by the
 * route result.
 *
 * If no route result is composed, and/or it's not the result of a method
 * failure, it passes handling to the provided handler.
 */
class MethodNotAllowedMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(callable $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult || ! $routeResult->isMethodFailure()) {
            return $handler->handle($request);
        }

        return ($this->responseFactory)(METHOD_NOT_ALLOWED_MIDDLEWARE_RESPONSE)
            ->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)
            ->withHeader('Allow', implode(',', $routeResult->getAllowedMethods()));
    }
}
