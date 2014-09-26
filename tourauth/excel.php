<?php


tahotels();

function tahotels()
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('tahotelsfull');

	$cursor = $mcol->find();

	$fp = fopen("thaitour.csv", "w+");

	$hdr = array('Name', 'Location', 'Address', 'Phone', 'Web', 'Remarks');
	fputcsv($fp, $hdr);

	foreach ($cursor as $doc) {
		$flds = array(
				$doc['name'],
				$doc['location'],
				$doc['address'],
				$doc['phone'],
				$doc['web'],
				$doc['sub']);
		fputcsv($fp, $flds);
	}

	fclose($fp);
}