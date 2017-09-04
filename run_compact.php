<?php

if(empty($argv))
    die('[ERROR] db_host was not passed to script as first parameter');

$db_host = $argv[1];

$excluded_dbs = [
	'_replicator',
	'_users',
];

//https://github.com/MaksymSemenykhin/php-HTTPRequester
include 'HTTPRequester/HTTPRequester.php';

define(HTTPRVERBOSE,true);

$all_dbs = file_get_contents($db_host.'/_all_dbs');
if(!$all_dbs){
    die('[ERROR] _all_dbs did not return anything');
}

$all_dbs = json_decode($all_dbs);
echo "\n\n_all_dbs count :".count($all_dbs)."\n\n";

foreach($all_dbs as $count => $db){
	if(in_array($db,$excluded_dbs ))
		continue;

	echo " ==> $db #$count\n";
	
	print_r(HTTPRequester::HTTPPost($db_host."/$db/_compact",[],['Content-Type: application/json']) );	
	
}



?>
