<?php

define('DEBUG', 0);

subdistLoop();


function locLoop()
{

$loc_ids = uniqLocations();

foreach ($loc_ids as $locid) {
	
	echo "Location Id: " . $locid . "\n";
	
	try {

	$offers = locOffers($locid);
	echo "Results : " . $offers['paging']['total_results'] . "\n";
	if ($offers['paging']['total_results'] == 0) continue;
	storeDocument('offers', $offers);

	$next_page = $offers['paging']['next'];

	if ($next_page !== null) {
	do {
		$next_page = $offers['paging']['next'];
		echo "Paging Next Page " . $next_page . "\n";
		$offers = json_decode(getHTTPContent($next_page), true);
		storeDocument('offers', $offers);
		sleep(rand(1,3));
	} while ($offers['paging']['next'] !== null);
	}
	} catch (Exception $e) {
		echo "Something Went Wrong";
		echo $e->getMessage() . "\n";
	}

	sleep(rand(1,3));
}

}


function locOffers($locid)
{
	$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/" . $locid . "?lang=en_US&checkin=2014-09-25&adults=2&nights=4&currency=USD&ip=infer&mcid=14525&devicetype=mobile&newrequest=false&commerceonly=false&rooms=1&lod=list&subcategory=hotel&subcategory_hotels=hotel&impression_key=f89d6568-3cdf-4302-b10f-665c337e6248&dieroll=55&limit=50&roomtype=lowest_price&sort=popularity&mobile=true";
        return json_decode(getHTTPContent($url), true);
}

function subdistLoop()
{
	$attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	$dbo = new PDO("mysql:host=localhost;dbname=tripad", "root", "", $attr);
	
	
	$sql = "SELECT province_id, province_name FROM province_detail";
	$sth = $dbo->prepare($sql);
	$sth->execute();
	$rows = $sth->fetchAll();
	
	$start = false;
	foreach ($rows as $row) {
	
		//if (trim($row['province_name']) == $city) $start = true;
		//if (!$start) continue;
	
		echo "Current Province " . $row['province_id'] . " - " . $row['province_name'] . "\n";
		if (strlen($row['province_name']) < 3) continue;
		try {
			$doc = getLocationDocument($row['province_name']);
			if (empty($doc)) continue;
			
			foreach ($doc as $d) {
				if ($d['country'] != 'Thailand') continue;
				
				if (!isLocationHaveOffers($d['location_id'])) {
					echo "Location Id " . $d['location_id'] . " Not in system \n";
					storeDocument('locations2', $d);
				}
			}
			
		} catch (Exception $e) {
			echo $e->getMessage();
			exit;
		}
		sleep(rand(1,4));
	}
}

function isLocationHaveOffers($id)
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('offers');
	
	$filter = array("status.primary_geo" => "$id");
	
	$cursor = $mcol->find($filter);
	
	
	foreach ($cursor as $doc) {
		if ($doc['paging']['total_results'] > 0) return true;
	}
	
	return false;
}

function districtLoop()
{
	$attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	$dbo = new PDO("mysql:host=localhost;dbname=tripad", "root", "", $attr);


	$sql = "SELECT amphur_name FROM amphur_detail";
	$sth = $dbo->prepare($sql);
	$sth->execute();
	$rows = $sth->fetchAll();
	$city = 'Ban Hong';
	$start = false;
	foreach ($rows as $row) {

		if (trim($row['amphur_name']) == $city) $start = true;
		if (!$start) continue;

		echo "Current District " . $row['amphur_name'] . "\n";
		if (strlen($row['amphur_name']) < 3) continue;
		try {
			$doc = getLocationDocument($row['amphur_name']);
			if (empty($doc)) continue;
			storeDocument('locations', $doc);
		} catch (Exception $e) {
			echo $e->getMessage();
			exit;
		}
		if (DEBUG === 1) exit;
		sleep(rand(1,4));
	}
}

function uniqLocations()
{
	$cmd = 'db.locations.find({}, { "0.location_id" : 1,"1.location_id" : 1,"2.location_id" : 1,"3.location_id" : 1,"4.location_id" : 1,"5.location_id" : 1,"6.location_id" : 1, "7.location_id" : 1 });';
	$m = new MongoClient(); // connect
        $db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('locations');

	$flds = ["0.location_id" => 1,"1.location_id" => 1,"2.location_id" => 1,"3.location_id" => 1,"4.location_id" => 1,"5.location_id" => 1,"6.location_id" => 1, "7.location_id" => 1];
	$cursor = $mcol->find(array(), $flds);
	$ids = [];
	foreach ($cursor as $doc) {
    		for ($i=0; $i < 8; $i++) {
			if (!isset($doc[$i]['location_id'])) continue;
			$ids[] = $doc[$i]["location_id"];
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

function getLocationDocument($name)
{

	$url = 'https://api.tripadvisor.com/api/internal/1.2/typeahead/' . urlencode($name) . '?lang=en_US&category=geos&limit=50';
	return json_decode(getHTTPContent($url), true);
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
