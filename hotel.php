<?php
offerLoop();

function offerLoop()
{
	$offer_urls = uniqOffers();
	echo "Number of Offers " . count($offer_urls) . "\n";
	$continue = false;
	foreach ($offer_urls as $offer_url) {

		echo "Trying " . $offer_url . "\n";
		try {
		 	$hotel = json_decode(getHTTPContent($offer_url), true);
			if (!isset($hotel['data'][0]['name'])) continue;
			echo "Hotel : " . $hotel['data'][0]['name'] . "\n";
                	storeDocument('hotels2', $hotel);
                	sleep(rand(1,3));
		} catch (Exception $e) {
			echo $e->getMessage();
		}

	}	

}

function uniqOffers()
{
        $m = new MongoClient(); // connect
        $db = $m->selectDB("tripad");
        $mcol = $db->selectCollection('offers2');

        $flds = ["data.api_detail_url" => 1]; //"1.location_id" => 1,"2.location_id" => 1,"3.location_id" => 1,"4.location_id" => 1,"5.location_id" => 1,"6.location_id" => 1, "7.location_id" => 1];
        $cursor = $mcol->find(array(), $flds);
        $ids = [];
        foreach ($cursor as $doc) {
         	foreach ($doc['data'] as $d) {
			if (!isset($d['api_detail_url'])) continue;
        	        $ids[] = $d["api_detail_url"];
		}
        }

        echo "Count : " . count(array_unique($ids)) . "\n";

        return array_unique($ids);

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
	$headers = array(
		'X-TripAdvisor-Unique: %1%enc%3ARc1iqDE%2BnOVY0uy%2BdCIFnUAQ1aFqh8hoXieo6c14%2BXQ%3D', 
		'X-TripAdvisor-UUID: a2aa6213-3afe-487a-90a3-96b27c121425', 
		'Cookie: TASession=%1%V2ID.563B12CEECAC884C78A35745A955C4BE*SQ.1*LS.MobileNativeSettings*GR.47*TCPAR.64*TBR.89*EXEX.95*ABTR.52*PPRP.82*PHTB.12*FS.45*CPU.82*HS.popularity*ES.popularity*AS.popularity*DS.5*SAS.dateRecent*FPS.oldFirst*DF.0*LP.%2FMobileNativeSettings-a_currency%5C.USD*TRA.true;',
		'X-TripAdvisor-API-Key: 943c3294-53af-8bf2-4b3c-543215a418ab',
);
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
