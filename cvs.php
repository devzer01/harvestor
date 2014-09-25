<?php
uniqOffers();

function uniqOffers()
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('hotels');

	$cursor = $mcol->find(array("data.0.phone" => array('$ne' => null)));
	foreach ($cursor as $doc) {
		echo $doc['data'][0]['name'];
		echo $doc['data'][0]['phone'];
		echo $doc['data'][0]['location_string'];
		echo $doc['data'][0]['price_level'];
		echo $doc['data'][0]['hotel_class'];
		echo $doc['data'][0]['address'];
		echo $doc['data'][0]['price'];
		if ($doc['data'][0]['hac_offers']['availability'] != 'unsupported') {
			echo $doc['data'][0]['hac_offers']['best_price'];
			echo $doc['data'][0]['hac_offers']['average_price'];
		}
		getbslist($doc['data'][0]['business_listings']);
		echo getAmnt($doc['data'][0]['amenities']);
		exit;
	}
}

function getAmnt($list)
{
	$amnt = [];
	foreach ($list as $l) {
		$amnt[] = $l['name'];
	}
	return implode($amnt, ',');
}

function getbslist($bslist)
{
	var_dump($bslist);
}
