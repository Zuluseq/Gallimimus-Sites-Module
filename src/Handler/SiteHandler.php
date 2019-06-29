<?php

declare(strict_types=1);

namespace GallimimusSitesModule\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Dom\Query;
use Zend\Json\Json;

use function time;

class SiteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
		$url = $request->getServerParams()['HTTP_HOST'];
		$sch = $request->getServerParams()['REQUEST_SCHEME'];
		$uri = $sch.'://'.$url.'/repository/get/getSiteBySlugAndNamespace?namespace=vizmedia-test&slug=home';

 		$client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
         
        $client->setUri($uri);
		$client->setOptions(array(
			'maxredirects' => 0,
			'timeout'      => 30
		));
        $result = $client->send();
        $JsonBody = $result->getBody();

		$phpNative = Json::decode($JsonBody);

        return new HtmlResponse($phpNative->results);
    }
}
