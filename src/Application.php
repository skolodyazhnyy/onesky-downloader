<?php

namespace Seven\OneskyDownloader;

use Seven\OneskyDownloader\DependencyInjection\Container;
use Seven\OneskyDownloader\Command\ContainerAwareInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    const VERSION = "1.0.0";

    /**
     * @var Container
     */
    private $container;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        parent::__construct("Onesky Downloader", self::VERSION);

        $this->container = Container::create($config);
    }

    /**
     * @param Command $command
     *
     * @return Command
     */
    public function add(Command $command)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->container);
        }

        return parent::add($command);
    }
}
