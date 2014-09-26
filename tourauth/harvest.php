<?php

require_once 'simple_html_dom.php';


$dir = "../ta/";

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
        	if (!preg_match("/html$/", $file)) continue;
        	
        	if (!is_file($dir . $file)) continue;
        	$html = file_get_contents($dir . $file);
        	$html = str_get_html($html);
        	
        	$links = $html->find("div.topic");
        	
        	foreach ($links as $link) {
        		$url = $link->find("a", 0)->href;
        		$name = $link->find("a",0)->plaintext;
        		
        		preg_match("/\-([0-9]+)$/", $link, $match);
        		
        		print_r($match);
        		$id = $match[0];
        		
        	}
        	
        }
        closedir($dh);
    }
}

function storeDocument($coll, $doc) 
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = null;
	try {
		$mcol = $db->selectCollection($coll);
	} catch (Exception $e) {
		$mcol = $db->createCollection($coll);
	}

	if (is_null($mcol)) throw new Exception("We don't have a collection reference");

	return $mcol->insert($doc);
}


function getHTTPContent($url, $return_header = false)
{
	$useragent = "Mobile Android TAaApp TARX13 taAppDeviceFeatures=131076 taAppVersion=101 appLang=en_US osName='Android' deviceName=unknown_sdk_sdk osVer=4.4.2 xhdpi normal mcc=310 mnc=260 connection=cellular";
	$headers = [];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

	/* $registry = Registry::getInstance();
	 if ($registry->get('username') !== null) {
	$cookie_file = $registry->get('username') . ".txt";
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	} */

	if (!empty($headers)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	if ($return_header) {
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
	}

	$content = curl_exec($ch);

	if (!preg_match("/2[0-9][0-9]/", curl_getinfo($ch, CURLINFO_HTTP_CODE))) {
		throw new Exception("Invalid Status Code " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . $content);
	}

	curl_close($ch);

	return $content;
}