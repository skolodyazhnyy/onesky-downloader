<?php

namespace Seven\OneskyDownloader\Command;

use Seven\OneskyDownloader\DependencyInjection\Container;
use Symfony\Component\Console\Command\Command;

class ContainerAwareCommand extends Command implements ContainerAwareInterface
{
    /** @var Container */
    private $container;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
