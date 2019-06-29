<?php

declare(strict_types=1);

namespace GallimimusSitesModule\Handler;

use Psr\Container\ContainerInterface;

class SiteHandlerFactory
{
    public function __invoke(ContainerInterface $container) : SiteHandler
    {
        return new SiteHandler();
    }
}
