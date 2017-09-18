<?php
	require_once("db.php");

	$files = array(
		"client.csv",
		"licence.csv",
		"device_details.csv",
		"site.csv"
		);

	try {
		$dsn = "mysql:host=$db_servername;charset=$db_charset";
		$pdo = new PDO($dsn, $db_username, $db_password, $opt);

		$pdo->query("CREATE DATABASE IF NOT EXISTS acma_data");

		$dsn = "mysql:host=$db_servername;dbname=acma_data;charset=$db_charset";		
		$pdo = new PDO($dsn, $db_username, $db_password, $opt);

		$sql = "CREATE TABLE IF NOT EXISTS acma_client_data (CLIENT_NO int(15) PRIMARY KEY,LICENCEE VARCHAR(250))";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		$sql = "CREATE TABLE IF NOT EXISTS acma_licence_data (LICENCE_NO varchar(15) PRIMARY KEY,CLIENT_NO int(15),LICENCE_TYPE_NAME varchar(20), LICENCE_CATEGORY_NAME varchar(40))";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		$sql = "CREATE TABLE IF NOT EXISTS acma_device_details_data (SDD_ID int(15) PRIMARY KEY,LICENCE_NO varchar(15),FREQUENCY varchar(15),BANDWIDTH int (15),EMISSION varchar(20),DEVICE_TYPE varchar (1), SITE_ID int(15))";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		$sql = "CREATE TABLE IF NOT EXISTS acma_site_data (SITE_ID int(15) PRIMARY KEY, LATITUDE varchar(10),LONGITUDE varchar(10),NAME varchar(255),SITE_PRECISION varchar(25),ELEVATION int(5))";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();		

	} catch (PDOException $e){
		echo $e->getMessage();
		exit;
	}

	foreach($files as $file){
		echo "Loading $file...\n";
		if(($handle = fopen($file, 'r')) !== false){
			$header = fgetcsv($handle);
			foreach ($header as $key=>$name){
				$value_mapping[$name] = $key;
			}
			while(($data = fgetcsv($handle)) !== false){
				try {
					$dsn = "mysql:host=$db_servername;dbname=acma_data;charset=$db_charset";
					$pdo = new PDO($dsn, $db_username, $db_password, $opt);
					
					if ($file == "client.csv"){
						$CLIENT_NO = $data[$value_mapping['CLIENT_NO']];
						$LICENCEE = $data[$value_mapping['LICENCEE']];

						$sql = "replace into acma_client_data (CLIENT_NO,LICENCEE) values (:client_number,:licencee)";

						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':client_number', $CLIENT_NO);
						$stmt->bindParam(':licencee', $LICENCEE);
						$stmt->execute();
						echo "Updated client number: $CLIENT_NO\n";
					}
					
					if ($file == "licence.csv"){
						$LICENCE_NO = $data[$value_mapping['LICENCE_NO']];
						$CLIENT_NO = $data[$value_mapping['CLIENT_NO']];
						$LICENCE_TYPE_NAME = $data[$value_mapping['LICENCE_TYPE_NAME']];
						$LICENCE_CATEGORY_NAME = $data[$value_mapping['LICENCE_CATEGORY_NAME']];
						
						$sql = "replace into acma_licence_data (LICENCE_NO,CLIENT_NO,LICENCE_TYPE_NAME,LICENCE_CATEGORY_NAME) values (:licence_number,:client_number,:licence_type_name,:licence_category_name)";
						
						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':licence_number', $LICENCE_NO);
						$stmt->bindParam(':client_number', $CLIENT_NO);
						$stmt->bindParam(':licence_type_name', $LICENCE_TYPE_NAME);
						$stmt->bindParam(':licence_category_name', $LICENCE_CATEGORY_NAME);
						$stmt->execute();
						echo "Updated licence number: $LICENCE_NO\n";
					}

					if ($file == "device_details.csv"){
						$LICENCE_NO = $data[$value_mapping['LICENCE_NO']];
						$FREQUENCY = $data[$value_mapping['FREQUENCY']];
						$BANDWIDTH = $data[$value_mapping['BANDWIDTH']];
						$EMISSION = $data[$value_mapping['EMISSION']];
						$DEVICE_TYPE = $data[$value_mapping['DEVICE_TYPE']];
						$SITE_ID = $data[$value_mapping['SITE_ID']];
						$SDD_ID = $data[$value_mapping['SDD_ID']];
						
						$sql = "replace into acma_device_details_data (SDD_ID,LICENCE_NO,FREQUENCY,BANDWIDTH,EMISSION,DEVICE_TYPE,SITE_ID) values (:sdd_id,:licence_no,:frequency,:bandwidth,:emission,:device_type,:site_id)";
						
						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':sdd_id', $SDD_ID);
						$stmt->bindParam(':licence_no', $LICENCE_NO);
						$stmt->bindParam(':frequency', $FREQUENCY);
						$stmt->bindParam(':bandwidth', $BANDWIDTH);
						$stmt->bindParam(':emission', $EMISSION);
						$stmt->bindParam(':device_type', $DEVICE_TYPE);
						$stmt->bindParam(':site_id', $SITE_ID);
						$stmt->execute();
						echo "Updated device detail: $SDD_ID\n";
					}
				
					if ($file == "site.csv"){
						$SITE_ID = $data[$value_mapping['SITE_ID']];
						$LATITUDE = $data[$value_mapping['LATITUDE']];
						$LONGITUDE = $data[$value_mapping['LONGITUDE']];
						$NAME = $data[$value_mapping['NAME']];
						$SITE_PRECISION = $data[$value_mapping['SITE_PRECISION']];
						$ELEVATION = $data[$value_mapping['ELEVATION']];

						$sql = "replace into acma_site_data (SITE_ID,LATITUDE,LONGITUDE,NAME,SITE_PRECISION,ELEVATION) values (:site_id,:latitude,:longitude,:name,:site_precision,:elevation)";

						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':site_id', $SITE_ID);
						$stmt->bindParam(':latitude', $LATITUDE);
						$stmt->bindParam(':longitude', $LONGITUDE);
						$stmt->bindParam(':name', $NAME);
						$stmt->bindParam(':site_precision', $SITE_PRECISION);
						$stmt->bindParam(':elevation', $ELEVATION);
						$stmt->execute();
						echo "Updated site id: $SITE_ID\n";

					}

				} catch (PDOException $e){
					echo $e->getMessage();
					exit;
				}
			}
			unset($data);
		}
		fclose($handle);
		
	}

?>
