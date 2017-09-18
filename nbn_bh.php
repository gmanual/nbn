<?php
	require_once("db.php");

	try {
	
		$dsn = "mysql:host=$db_servername;dbname=acma_data;charset=$db_charset";
		$pdo = new PDO($dsn, $db_username, $db_password, $opt);

		$sql = "select 
			device_details.LICENCE_NO,
			device_details.SITE_ID,
			site_data.LATITUDE,
			site_data.LONGITUDE
			from 
			acma_device_details_data as device_details 
			left join 
			acma_licence_data as licence_data on device_details.LICENCE_NO = licence_data.LICENCE_NO
			left join acma_site_data as site_data on device_details.SITE_ID = site_data.SITE_ID
			where
			licence_data.LICENCE_TYPE_NAME = 'Fixed' and licence_data.CLIENT_NO = '8129031'
			group by LICENCE_NO,SITE_ID";

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$nbn_fw_bh_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$sql = "select distinct
			site_data.SITE_ID,site_data.LATITUDE,site_data.LONGITUDE,site_data.NAME,site_data.SITE_PRECISION,site_data.ELEVATION
			FROM
			acma_device_details_data as device_details
			left join
			acma_licence_data as licence_data on device_details.LICENCE_NO = licence_data.LICENCE_NO
			left join acma_site_data as site_data on device_details.SITE_ID = site_data.SITE_ID
			where
			licence_data.CLIENT_NO = '8129031' or licence_data.CLIENT_NO = '1104329';
			";

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$nbn_fw_tower_data = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	
	} catch (PDOException $e){
			echo $e->getMessage();
	}

	foreach($nbn_fw_bh_data as $nbn_device){
		$LICENCE_NO = $nbn_device['LICENCE_NO'];
		$SITE_ID = $nbn_device['SITE_ID'];
		$LATITUDE = $nbn_device['LATITUDE'];
		$LONGITUDE = $nbn_device['LONGITUDE'];
		$site_lat_long = array('SITE_ID'=>$SITE_ID,'LAT'=>$LATITUDE,'LONG'=>$LONGITUDE);
		$nbn_lines[$LICENCE_NO][]['LOC'] = $site_lat_long;
	}
	
	$kml_backhaul_header = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom"><Document><name>NBN FW Backhauls</name>';

	$kml_footer = '</Document></kml>';

	$line_data = null;

	foreach($nbn_lines as $licence_key=>$line){
		$start_lat = $line[0]['LOC']['LAT'];
		$start_lon = $line[0]['LOC']['LONG'];
		$end_lat = $line[1]['LOC']['LAT'];
		$end_lon = $line[1]['LOC']['LONG'];

		$line_data = $line_data . "<Placemark><name>NBN $licence_key</name><LineString><coordinates>";
		$line_data = $line_data . "$start_lon,$start_lat $end_lon,$end_lat";
		$line_data = $line_data . "</coordinates></LineString></Placemark>\n";			
		
	}

	$kml_tower_header = '<?xml version="1.0" encoding="UTF-8"?>
		<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom"><Document><name>NBN Towers</name><Style id="sh_triangle"><IconStyle><scale>1.4</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/triangle.png</href></Icon></IconStyle><LabelStyle><color>00ffffff</color></LabelStyle><ListStyle></ListStyle></Style><StyleMap id="msn_triangle"><Pair><key>normal</key><styleUrl>#sn_triangle</styleUrl></Pair><Pair><key>highlight</key><styleUrl>#sh_triangle</styleUrl></Pair></StyleMap><Style id="sn_triangle"><IconStyle><scale>1.2</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/triangle.png</href></Icon></IconStyle><LabelStyle><color>00ffffff</color></LabelStyle><ListStyle></ListStyle></Style>';

	$tower_data = null;
	foreach($nbn_fw_tower_data as $nbn_tower){
		$SITE_ID = $nbn_tower['SITE_ID'];
		$LATITUDE = $nbn_tower['LATITUDE'];
		$LONGITUDE = $nbn_tower['LONGITUDE'];
		$NAME = $nbn_tower['NAME'];
		$NAME = str_replace('&','&#38;amp;',$NAME);
		$SITE_PRECISION = $nbn_tower['SITE_PRECISION'];
		$ELEVATION = $nbn_tower['ELEVATION'];
		$tower_data = $tower_data . "<Placemark><name>$SITE_ID - $NAME";
		$tower_data = $tower_data . '</name><styleUrl>#msn_triangle</styleUrl><Point><coordinates>';
		$tower_data = $tower_data . "$LONGITUDE,$LATITUDE";
		$tower_data = $tower_data . "</coordinates></Point></Placemark>\n";
	}

	$file = "nbn_fw_towers.kml";
	$output = $kml_tower_header . $tower_data . $kml_footer;
	file_put_contents($file, $output);

	$file = "nbn_fw_backhaul.kml";
	$output = $kml_backhaul_header . $line_data . $kml_footer;
	file_put_contents($file, $output);

?>
