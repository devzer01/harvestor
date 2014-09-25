<?php

locLoop();


function locLoop()
{

$loc_ids = uniqLocations();

foreach ($loc_ids as $locid) {
	
	echo "Location Id: " . $locid . "\n";
	
	try {

	$offers = locOffers($locid);
	echo "Results : " . $offers['paging']['total_results'] . "\n";
	if ($offers['paging']['total_results'] == 0) continue;
	storeDocument('offers2', $offers);

	$next_page = $offers['paging']['next'];

	if ($next_page !== null) {
	do {
		$next_page = $offers['paging']['next'];
		echo "Paging Next Page " . $next_page . "\n";
		$offers = json_decode(getHTTPContent($next_page), true);
		storeDocument('offers2', $offers);
		sleep(rand(1,3));
	} while ($offers['paging']['next'] !== null);
	}
	} catch (Exception $e) {
		echo "No Offers in Location " . $locid . "\n";
	}

	sleep(rand(1,3));
}

}

function locOffers($locid)
{
	$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/" . $locid . "?lang=en_US&checkin=2014-09-30&adults=2&nights=4&currency=USD&ip=infer&mcid=14525&devicetype=mobile&newrequest=false&commerceonly=false&rooms=1&lod=list&subcategory=hotel&subcategory_hotels=hotel&impression_key=f89d6568-3cdf-4302-b10f-665c337e6248&dieroll=55&limit=50&roomtype=lowest_price&sort=popularity&mobile=true";
        return json_decode(getHTTPContent($url), true);
}

function uniqLocations()
{
	$m = new MongoClient(); // connect
        $db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('locations2');

	$cursor = $mcol->find();
	$ids = [];
	foreach ($cursor as $doc) {
			if (!isset($doc['location_id'])) continue;
			$ids[] = $doc["location_id"];
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
