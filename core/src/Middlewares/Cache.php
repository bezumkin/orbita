<?php

namespace App\Middlewares;

use App\Services\Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class Cache
{
    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $cacheTTL = (int)getenv('CACHE_API_TIME');
        if ($cacheTTL && $request->getMethod() === 'GET' && !$request->getAttribute('token')) {
            $cacheKey = $this::getCacheKey($request->getUri());
            if ($this->redis->exists($cacheKey)) {
                $response = (new ResponseFactory())->createResponse();
                $response->getBody()->write($this->redis->get($cacheKey));
                if (getenv('CORS')) {
                    $response = $response
                        ->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('HTTP_ORIGIN'));
                }

                return $response
                    ->withHeader('X-From-Cache', '1')
                    ->withHeader('Content-Type', 'application/json; charset=utf-8');
            }

            $response = $handler->handle($request);
            $cacheResponse = $response->getStatusCode() === 200 &&
                str_starts_with($response->getHeaderLine('Content-Type'), 'application/json');
            if ($cacheResponse) {
                $this->redis->set($cacheKey, (string)$response->getBody(), 'EX', $cacheTTL);
            }

            return $response;
        }

        return $handler->handle($request);
    }

    protected static function getCacheKey(UriInterface $uri): string
    {
        $key = str_replace('/', ':', trim($uri->getPath(), '/'));
        parse_str($uri->getQuery(), $query);
        ksort($query);
        if ($query) {
            $key .= '-' . sha1(json_encode($query, JSON_THROW_ON_ERROR));
        }

        return $key;
    }
}
