<?php
uniqOffers();

function uniqOffers()
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('hotels');

	$cursor = $mcol->find(array("data.0.phone" => array('$ne' => null)));
	
	$fp = fopen("hotels.csv", "w+");
	
	$hdr = array('Name', 'Phone', 'Address', 'Phone2', 'URL', 'Email', 'Price Level', 'Class', 'Price', 'Best Price', 'Average Price', 'Amenities');
	fputcsv($fp, $hdr);
	
	foreach ($cursor as $doc) {
		$flds = array(
			$doc['data'][0]['name'],
			$doc['data'][0]['phone'],
			$doc['data'][0]['address']);
			
				$ret = getbslist($doc['data'][0]['business_listings']);
				if (isset($ret['phone'])) {
					$flds[] = $ret['phone'];
				} else {
					$flds[] = "N/A";
				}
				if (isset($ret['url'])) {
					$flds[] = $ret['url'];
				} else {
					$flds[] = "N/A";
				}
				if (isset($ret['email'])) {
					$flds[] = $ret['email'];
				} else {
					$flds[] = "N/A";
				}
				
				
			$flds[] = $doc['data'][0]['price_level'];
			$flds[] = $doc['data'][0]['hotel_class'];
			$flds[] = $doc['data'][0]['price'];
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

function getbslist($bslist)
{
	$ret = [];
	if (isset($bslist['mobile_contacts'])) {
		foreach ($bslist['mobile_contacts'] as $mc) {
			$ret[$mc['type']] = $mc['value'];
		}
	} else if (isset($bslist['desktop_contacts'])) {
		foreach ($bslist['desktop_contacts'] as $mc) {
			$ret[$mc['type']] = $mc['value'];
		}
	}
	return $ret;
}
