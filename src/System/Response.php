<?php

namespace JetFire\Framework\System;

use JetFire\Routing\ResponseInterface;
use JetFire\Http\Response as HttpResponse;

class Response extends HttpResponse implements ResponseInterface{

    /**
     * @param array $headers
     */
    public function setHeaders($headers = [])
    {
        foreach($headers as $key => $content)
            $this->headers->set($key,$content);
    }

} 