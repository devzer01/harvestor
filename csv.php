<?php
uniqOffers();

function uniqOffers()
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('hotels');

	$cursor = $mcol->find(array("data.0.phone" => array('$ne' => null)));
	
	$fp = fopen("hotels.csv", "w+");
	
	$hdr = array('Name', 'Phone', 'Address', 'Price Level', 'Class', 'Price', 'Best Price', 'Average Price', 'Amenities');
	fputcsv($fp, $hdr);
	
	foreach ($cursor as $doc) {
		$flds = array(
			$doc['data'][0]['name'],
			$doc['data'][0]['phone'],
			$doc['data'][0]['address'],
			$doc['data'][0]['price_level'],
			$doc['data'][0]['hotel_class'],
			$doc['data'][0]['price']);
		if ($doc['data'][0]['hac_offers']['availability'] != 'unsupported') {
			$flds[] = $doc['data'][0]['hac_offers']['best_price'];
			$flds[] = $doc['data'][0]['hac_offers']['average_price'];
		} else {
			$flds[] = 'N/A';
			$flds[] = 'N/A';
		}
		$flds[] = getAmnt($doc['data'][0]['amenities']);
		
		fputcsv($fp, $flds);
	}
	
	fclose($fp);
}

function getAmnt($list)
{
	$amnt = [];
	foreach ($list as $l) {
		$amnt[] = $l['name'];
	}
	return implode($amnt, ',');
}

