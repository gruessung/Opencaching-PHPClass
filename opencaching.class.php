<?php

/*********************
** Class: Opencaching
** Author: Alexander Grüßung
** Url: www.gvisions.de
** Version: 0.4
** License: http://creativecommons.org/licenses/by-sa/3.0/de/
** Danke an OC für den Service.
** ! Check if the cache-folder-path is always the same and right !
*********************/

class Opencaching {
	
	private $cacheID = "";  //die ID des Caches im OC System
	private $xmlFile = "";  // Inhalt der XML Datei zum Cache
	private $xmlCache = "cache/opencaching/"; // Link zur XML File im Programmcache (../cache/opencaching/WAYPOINT.xml)
	private $gpxLink = "";  // Link zur GPX File auf OC.de
	private $wp = "";       // Waypoint auf OC, REQUIRED!
	public $Cache = array();//Beinhaltet alle relevanten Cacheinformationen

	/**
	 * function construct
	 * Schreibt den übergebenen Waypoint in die Variable und holt gleich nützliche Infos und Cached
	 **/
	function __construct($wp) {
		$this->wp = $wp;
		$this->xmlCache = $this->xmlCache.$this->wp.".xml";
		if (!@file_exists("cache/opencaching/".$this->wp.".xml")) {
			$this->getGpxLink();			
			$this->loadXmlToCache("http://opencaching.de/".$this->gpxLink);
		}
		$this->loadXML($this->xmlCache);
		
	}
	
	/**
	 * function getGpxLink
	 * Ermittelt den Downloadlink der GPX Datei mithilfe des WP und des Quelltextes der OC Seite
	 **/
	function getGpxLink() {
		$cacheHTML = file_get_contents('http://www.opencaching.de/viewcache.php?wp='.$this->wp.'');
		$regexp_link ='/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>GPX<\/a>/si';
		preg_match_all($regexp_link, $cacheHTML, $alle_links); 
		$gpxLink = $alle_links[1][0];
		$this->gpxLink = $gpxLink;
		return true;
	}

	/**
	 * function loadXmlToCache
	 * Damit die OC Seite nicht immer belastet wird, wird die XML Datei lokal gecached
	 **/
	function loadXmlToCache($xml) {
		$xmlSource = file_get_contents($xml);
		$xmlCache = fopen("cache/opencaching/".$this->wp.".xml","w+");
		$xmlSource = preg_replace("%<gpx%","<xml",$xmlSource);
		$xmlSource = preg_replace("%</gpx>%","</xml>",$xmlSource);
		fwrite($xmlCache,$xmlSource);
		fclose($xmlCache);
		$this->xmlCache = "cache/opencaching/".$this->wp.".xml";
	}

	/**
	 * function loadXML
	 * Lade die XML Datei mit allen Cacheinfos
	 **/
	function loadXML($xml) {
		$xmlFile = @simplexml_load_file($xml);
		$this->xmlFile = $xmlFile;
		return true;
	}

	
	/**
	 * function getCacheInformation
	 * Schreibt alle wichtigen Infos eines Caches in ein Array
	 **/
	function getCacheInformation() {
		$Cache = array();
		$Cache['id'] = $this->xmlFile->wpt->extensions->cache['id'];
		$Cache['wp'] = $this->wp;
		$Cache['coord']['lat'] = $this->xmlFile->wpt['lat'];
		$Cache['coord']['lon'] = $this->xmlFile->wpt['lon'];
		$Cache['time']=$this->xmlFile->wpt->time;
		$Cache['url']=$this->xmlFile->wpt->url;
		$Cache['name'] = $this->xmlFile->wpt->urlname;
		$Cache['type']=$this->xmlFile->wpt->extensions->cache->type;
		$Cache['status']=$this->xmlFile->wpt->extensions->cache['status'];
		$Cache['owner'] = $this->xmlFile->wpt->extensions->cache->owner;  //ownerID = ['owner']['userid']
		$Cache['state'] = htmlentities($this->xmlFile->wpt->extensions->cache->state);
		$Cache['country'] = htmlentities($this->xmlFile->wpt->extensions->cache->country);
		$Cache['container'] = $this->xmlFile->wpt->extensions->cache->container;
		$Cache['difficulty'] = $this->xmlFile->wpt->extensions->cache->difficulty;
		$Cache['terrain'] = $this->xmlFile->wpt->extensions->cache->terrain;
		$Cache['desc'] = $this->xmlFile->wpt->extensions->cache->long_description;
		$Cache['hint'] = $this->xmlFile->wpt->extensions->cache->encoded_hints;
		return $Cache;
	}


}


