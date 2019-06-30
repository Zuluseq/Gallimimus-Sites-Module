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
		$uriSzablon = $sch.'://'.$url.'/repository/get/getSiteBySlugAndNamespace?namespace=vizmedia-test&slug=home';
		$uriSekcje = $sch.'://'.$url.'/repository/get/getSectionsOfSite?id_site=';
		$uriKomponenty = $sch.'://'.$url.'/repository/get/getComponentsOfSection?id_site={id_site}&section={section}';

 		$client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
         
		// pobieram szablon
        $client->setUri($uriSzablon);
		$client->setOptions(array(
			'maxredirects' => 0,
			'timeout'      => 30
		));
        $result = $client->send();
        $JsonBody = $result->getBody();
		$phpNative = Json::decode($JsonBody);
		$site = $phpNative[0];
		$szablon = $site->template;

		// pobieram komponenty
		$uriSekcje = $uriSekcje.$site->id_site;
        $client->setUri($uriSekcje);
        $result = $client->send();
        $JsonBody = $result->getBody();
		$sekcje = Json::decode($JsonBody);

		$sekcjeContent = array();
		// tworze content dla sekcji i wypełniam je komponentami
		$wynik = "";
		foreach($sekcje as $sekcja)
		{
			$uriKomp = str_replace('{id_site}',$site->id_site,$uriKomponenty);
			$uriKomp = str_replace('{section}',$sekcja->section,$uriKomp);
			$client->setUri($uriKomp);
			$result = $client->send();
			$JsonBody = $result->getBody();
			$komponenty = Json::decode($JsonBody);

			// $sekcjeContent[] = $this->preparujKomponenty($komponenty);
			$wynik = $wynik.$this->preparujKomponenty($komponenty);
		}


		// wstawiam sekcje do szablonu

        return new HtmlResponse($wynik);
        // return new JsonResponse($sekcjeContent,200,[],JSON_PRETTY_PRINT);
        // return new JsonResponse($site->template);
    }

	private function preparujKomponenty($komponenty)
	{
		$wynik = "";
		foreach($komponenty as $komponent) 
		{
			$wynik = $wynik.$komponent->template;
		}
		return $wynik;
	}
}
