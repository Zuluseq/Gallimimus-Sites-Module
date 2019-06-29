<?php

declare(strict_types=1);

namespace Sites\Handler;

use Psr\Container\ContainerInterface;

class SiteHandlerFactory
{
    public function __invoke(ContainerInterface $container) : SiteHandler
    {
        return new SiteHandler();
    }
}
