<?php

declare(strict_types=1);

namespace GallimimusSitesModule\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Db\Adapter\Exception;

use function time;

class SiteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
		$url = $request->getServerParams()['HTTP_HOST'];
		$sch = $request->getServerParams()['REQUEST_SCHEME'];
		$uriSzablon = $sch.'://'.$url.'/repository/get/getSiteBySlugAndNamespace?namespace=vizmedia-test&slug=';
		$uriSekcje = $sch.'://'.$url.'/repository/get/getSectionsOfSite?id_site=';
		$uriKomponenty = $sch.'://'.$url.'/repository/get/getComponentsOfSection?id_site={id_site}&section={section}';

		$attributes = $request->getAttributes();
		$slug = $attributes['slug'];
		if($slug != null) $slug = htmlspecialchars($slug, ENT_HTML5, 'UTF-8');
		else $slug = "home";
		$uriSzablon = $uriSzablon.$slug;

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

		$uriSekcje = $uriSekcje.$site->id_site;
        $client->setUri($uriSekcje);
        $result = $client->send();
        $JsonBody = $result->getBody();

		if(!isset($JsonBody) || $JsonBody == null) $this->error("Brakuje zdefiniowanych komponentów dla tej strony");

		//tablica nazw sekcji 
		$sekcje = Json::decode($JsonBody);

		$sekcjeContent = array();
		// tworze content dla sekcji i wypełniam je komponentami

		foreach($sekcje as $sekcja)
		{
			$uriKomp = str_replace('{id_site}',$site->id_site,$uriKomponenty);
			$uriKomp = str_replace('{section}',$sekcja->section,$uriKomp);
			$client->setUri($uriKomp);
			$result = $client->send();
			$JsonBody = $result->getBody();
			$komponenty = Json::decode($JsonBody);
			$sekcjeContent[$sekcja->section] = $this->preparujKomponenty($komponenty);
		}

		// // $qr = new Query($szablon);
		
		// // <gal-template gal-section="nawigacja">nawigacja</gal-template>
		// $dom = new \DOMDocument();
		
		$domSzablon = new \DOMDocument('5.0', 'UTF-8');
		$domSzablon->loadHTML($szablon, LIBXML_NOERROR );
		$znalezioneSekcje = $domSzablon->getElementsByTagName("gal-template");
		
		$ileSekcji = $znalezioneSekcje->length;
		for ($i=0; $i<$ileSekcji; $i++) 
		{
			$znalezioneSekcje = $domSzablon->getElementsByTagName("gal-template");
			$znalezionaSekcja = $znalezioneSekcje[0];
			$nazwaSekcji = "";
			$nazwaSekcji = $znalezionaSekcja->attributes["gal-section"]->value;
			if(strlen($nazwaSekcji) > 0) 
			{
				$trescSekcji = $sekcjeContent[$nazwaSekcji];
				$subDom = $domSzablon->createDocumentFragment();
				$subDom->appendXML($trescSekcji);
				$znalezionaSekcja->parentNode->replaceChild($subDom,$znalezionaSekcja);
			}
		}

		// wstawiam sekcje do szablonu
        // return new HtmlResponse($sekcjeContent[1]);
        // return new TextResponse($wynik);
        // return new TextResponse($domSzablon->saveHtml());
        return new HtmlResponse($domSzablon->saveHtml());
        // return new JsonResponse($JsonBody);
        // return new JsonResponse($sekcje);
        // return new HtmlResponse($szablon);
        // return new JsonResponse($sekcjeContent,200,[],JSON_PRETTY_PRINT);
        // return new JsonResponse($site->template);
    }

	private function preparujKomponenty($komponenty)
	{
		$wynik = "";
		foreach($komponenty as $komponent) 
		{
			$m = new \Mustache_Engine;
			// $wynik = $m->render('Hello {{planet}}', array('planet' => 'World!'));
			// $s = $m->render($komponent->template, $komponent->default_values);
			$parametry = Json::decode($komponent->default_values);
			$s = $m->render($komponent->template, $parametry);

			// $wynik[] = $s;
			$wynik .= $s;
		}
		return $wynik;
	}

	public function error($mess)
	{
		throw new Exception\RuntimeException(
			$mess,
			0
		);		
	}
}
