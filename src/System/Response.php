<?php

namespace JetFire\Framework\System;

use JetFire\Routing\ResponseInterface;
use \Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse implements ResponseInterface
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

    /**
     * @param $content
     * @param int $status
     * @param string $type
     * @return SymfonyResponse
     */
    public function answer($content, $status = 200, $type = 'text/html')
    {
        $this->setContent($content);
        $this->headers->set('Content-type', $type);
        $this->setStatusCode($status);
        return $this;
    }

} 