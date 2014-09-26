<?php

require_once 'simple_html_dom.php';

$links = getHotelPages();
echo "Got Hotel(s) " . count($links) . "\n";

foreach ($links as $id => $url) {
	
	$html = getHTTPContent($url);
	$html = str_get_html($html);
	
	$location = $html->find("div.line", 1)->find("span.detail", 0)->plaintext;
	$address = $html->find("div.line", 2)->find("span.detail", 0)->plaintext;
	$phone = $html->find("div.line", 3)->find("p.phone", 0)->plaintext;
	$web = $html->find("div.line", 3)->find("div.contact_line", 3)->find("p", 0)->find("a", 0)->href;
	$sub = $html->find("div.div_subdata", 0)->find("p", 4)->plaintext;

	$data = array('location' => $location, 'address' => $address, 'phone' => $phone, 'web' => $web, 'sub' => $sub);
	
	var_dump($data);
	exit;
}

function getHotelPages()
{
	
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('tahotels');

	$cursor = $mcol->find();
	$links = [];
	foreach ($cursor as $doc) {
		$links[$doc['id']] = $doc['url'];
	}
	return $links;	
}

function parseSearch()
{
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
        		
        		preg_match("/\-([0-9]+)$/", $url, $match);
        		$id = $match[1];
        		
        		$hotel = array('url' => $url, 'name' => $name, 'id' => $id);
        		
        		storeDocument('tahotels', $hotel);
        	}
        	
        }
        closedir($dh);
    }
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
	//$useragent = "";
	$headers = [];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
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