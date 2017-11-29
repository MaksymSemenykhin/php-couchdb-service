<?php
#https://blog.couchdb.org/2016/08/17/migrating-to-couchdb-2-0/

#php ./couchdb_migration.php  http://localhost:5984 http://localhost:5986

if(empty($argv) || !isset($argv[1]))
    die('[ERROR] db_from was not passed to script as first parameter');
if(empty($argv) || !isset($argv[2]))
    die('[ERROR] db_to was not passed to script as second parameter');


$db_from = $argv[1];
$db_to   = $argv[2];


$excluded_dbs = [
        '_replicator',
        '_users',
];
$excluded_dbs = [];


include 'HTTPRequester.php';

define('HTTPRVERBOSE',true);

$all_dbs = file_get_contents($db_from.'/_all_dbs');

if(!$all_dbs){
    die('[ERROR] _all_dbs did not return anything');
}

$all_dbs = json_decode($all_dbs);
echo "\n\n_all_dbs count :".count($all_dbs)."\n\n";

foreach($all_dbs as $count => $db){
        if(in_array($db,$excluded_dbs ))
                continue;
				
# create a clustered new mydb on CouchDB 2.0
#curl -X PUT 'http://machine2:5984/mydb' 
       print_r(HTTPRequester::HTTPput($db_to."/$db",[]));
echo "-------\n\n";
	   
# replicate data
#curl -X POST 'http://machine1:5984/_replicate' -H 'Content-type: application/json' -d '{"source": "mydb", "target": "http://machine2:5984/mydb"}' 
       print_r(HTTPRequester::HTTPPost($db_from."/_replicate",["source"=> "$db", "target"=> "$db_to/$db"],['Content-Type: application/json']) );
echo "-------\n\n";

# trigger re-build index(es) of somedoc with someview; do for all 
# to speed up first use of application
#curl -X GET 'http://machine2:5984/mydb/_design/_view/?stale=update_after' 				
       print_r(file_get_contents($db_to."/$db/_design/_view/?stale=update_after") );
echo "-------\n\n";

}
