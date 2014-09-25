<?php 

$locs = uniqLocations();

$fp = fopen("data/updated_locations.csv", "w+");
$hdr = array('Category', 'Parent', 'Name',  'Geo', 'Sub Category', 'Country', 'Location Id', 'Results');
fputcsv($fp, $hdr);

foreach ($locs as $loc) {
	$loc[] = getResult($loc['location_id']);
	fputcsv($fp, array_values($loc));
}

fclose($fp);


function getResult($id)
{
	echo "Location : " . $id . "\n";
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('offers');
	
	$filter = array("status.primary_geo" => "$id");
	
	$cursor = $mcol->find($filter);

	
	foreach ($cursor as $doc) {
		return $doc['paging']['total_results'];
	}

	return -1;
}

function uniqLocations()
{
	$m = new MongoClient(); // connect
	$db = $m->selectDB("tripad");
	$mcol = $db->selectCollection('locations');

	$data = [];
	
	$cursor = $mcol->find();
	foreach ($cursor as $doc) {
		foreach ($doc as $k => $loc) {
			if ($k == "_id") continue;
			$row = [];
			$row['category_key'] = $loc['category_key'];
			$row['parent_display_name'] = $loc['parent_display_name'];
			$row['name'] = $loc['name'];
			$row['geo_type'] = $loc['geo_type'];
			$row['subcategory_key'] = $loc['subcategory_key'][0];
			$row['country'] = $loc['country'];
			$row['location_id'] = $loc['location_id'];
			$data[] = $row;
		}
	}
	
	return $data;
}
