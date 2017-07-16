<?php

namespace JetFire\Framework\System;

use JetFire\Routing\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse as HttpJsonResponse;

/**
 * Class JsonResponse
 * @package JetFire\Framework\System
 */
class JsonResponse extends HttpJsonResponse implements ResponseInterface
{
    /**
     * @param array $headers
     */
    public function setHeaders($headers = [])
    {
        foreach ($headers as $key => $content) {
            $this->headers->set($key, $content);
        }
    }
} 