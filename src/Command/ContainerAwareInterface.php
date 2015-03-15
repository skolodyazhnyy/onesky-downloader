<?php

namespace Seven\OneskyDownloader\Command;

use Seven\OneskyDownloader\DependencyInjection\Container;

interface ContainerAwareInterface
{
    public function setContainer(Container $container);
}
