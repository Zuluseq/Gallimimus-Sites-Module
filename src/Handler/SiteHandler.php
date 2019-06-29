<?php

declare(strict_types=1);

namespace Sites\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;

use function time;

class SiteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // return new JsonResponse(['gallimimus site handler: ack' => time()]);

		$htmlContent = '<h1>dupa2</h1>';

		return new HtmlResponse($htmlContent,200);
    }
}
