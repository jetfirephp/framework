<?php

namespace JetFire\Framework\Providers;

/**
 * Class HttpProvider
 * @package JetFire\Framework\Providers
 */
/**
 * Class HttpProvider
 * @package JetFire\Framework\Providers
 */
class HttpProvider extends Provider
{

    /**
     * @param array $config
     */
    public function init($config = [])
    {
        $this->app->addRule('GuzzleHttp\Client', [
            'shared' => true,
            'construct' => [$config]
        ]);
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->app->get('GuzzleHttp\Client');
    }

} 