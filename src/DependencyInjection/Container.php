<?php

namespace Seven\OneskyDownloader\DependencyInjection;

use Onesky\Api\Client;
use Pimple\Container as BaseContainer;

class Container extends BaseContainer
{
    /**
     * @param array $config
     *
     * @return Container
     */
    public static function create(array $config)
    {
        $container = new self($config);
        $container['onesky_client'] = $container->factory(function($container) {
            $client = new Client();
            $client->setApiKey($container['onesky_api_key']);
            $client->setSecret($container['onesky_api_secret']);

            return $client;
        });

        return $container;
    }

    /**
     * @return integer
     */
    public function getOneskyProjectId()
    {
        return intval($this['onesky_project']);
    }

    /**
     * @return Client
     */
    public function getOneskyClient()
    {
        return $this['onesky_client'];
    }
}
