<?php

switch ($_POST['access']) {
	case 'list':
		$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/2043912?lang=en_US&checkin=2014-09-28&adults=2&nights=4&currency=USD&ip=infer&mcid=14525&devicetype=mobile&newrequest=false&commerceonly=false&rooms=1&lod=list&subcategory=hotel&subcategory_hotels=hotel&impression_key=f89d6568-3cdf-4302-b10f-665c337e6248&dieroll=55&limit=50&roomtype=lowest_price&sort=popularity&mobile=true";
		break;
	case 'detail':
		$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/612516?checkin=2014-09-26&countrycode=TH&adults=2&lod=extended&nights=4&devicetype=mobile&newrequest=true&currency=USD";
		break;
	case 'type':
		$url = 'https://api.tripadvisor.com/api/internal/1.2/typeahead/' . urlencode($_POST['loc']) . '?lang=en_US&category=geos&limit=50';
		break;
	default:
		$url = 'https://api.tripadvisor.com/api/internal/1.2/typeahead/' . urlencode($_POST['loc']) . '?lang=en_US&category=geos&limit=50';
		break;
}

//
////$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/293920?lang=en_US&checkin=2014-09-23&adults=2&nights=4&currency=USD&ip=infer&mcid=14525&devicetype=mobile&newrequest=false&commerceonly=false&rooms=1&lod=list&subcategory=hotel&subcategory_hotels=hotel&impression_key=f89d6568-3cdf-4302-b10f-665c337e6248&dieroll=55&limit=50&roomtype=lowest_price&sort=popularity&mobile=true";
//$url = "https://api.tripadvisor.com/api/internal/1.2/meta_hac/612516?checkin=2014-09-23&countrycode=TH&adults=2&lod=extended&nights=4&devicetype=mobile&newrequest=true&currency=USD";
$useragent = "Mobile Android TAaApp TARX13 taAppDeviceFeatures=131076 taAppVersion=101 appLang=en_US osName='Android' deviceName=unknown_sdk_sdk osVer=4.4.2 xhdpi normal mcc=310 mnc=260 connection=cellular";
$headers = array(
		'X-TripAdvisor-Unique: %1%enc%3ARc1iqDE%2BnOVY0uy%2BdCIFnUAQ1aFqh8hoXieo6c14%2BXQ%3D', 
		'X-TripAdvisor-UUID: a2aa6213-3afe-487a-90a3-96b27c121425', 
		'Cookie: TASession=%1%V2ID.563B12CEECAC884C78A35745A955C4BE*SQ.1*LS.MobileNativeSettings*GR.47*TCPAR.64*TBR.89*EXEX.95*ABTR.52*PPRP.82*PHTB.12*FS.45*CPU.82*HS.popularity*ES.popularity*AS.popularity*DS.5*SAS.dateRecent*FPS.oldFirst*DF.0*LP.%2FMobileNativeSettings-a_currency%5C.USD*TRA.true;',
		'X-TripAdvisor-API-Key: 943c3294-53af-8bf2-4b3c-543215a418ab',
);


$ret = getHTTPContent($url, $useragent, null, $headers, true);

echo "<pre>";
var_dump($ret);
echo "</pre>";

function getHTTPContent($url, $useragent, $postContent=null, $headers = array(), $return_header = false)
{
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

	if($postContent !== null)
	{
		curl_setopt($ch, CURLOPT_POST, 1);

		if (is_array($postContent)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postContent));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
		}
	}

	if ($return_header) {
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
	}

	$content = curl_exec($ch);
	curl_close($ch);

	if(empty($content)) {
		//$this->savelog('No Response from url : '.$url.' / Proxy : '.$this->proxy_ip.':'.$this->proxy_port); botutil::setNoResponse($this->commandID, TRUE, $this);
	} else {
		//botutil::setNoResponse($this->commandID, FALSE, $this);
	}

	return $content;
}
