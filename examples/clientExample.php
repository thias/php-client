<?php

namespace Aerospike;

$cp = new ClientPolicy();

////////////////////////////////////////////////////////////////////////////////
//
//	Creating Client, persisting in permanent storage, retriving from there
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "127.0.0.1:3000");
// $client = Aerospike($cp, "172.17.0.2:3000");
// $client = Aerospike($cp, "172.17.0.2:3000");
$connected = $client->isConnected();

var_dump($connected);
////////////////////////////////////////////////////////////////////////////////
//
// Key object
//
////////////////////////////////////////////////////////////////////////////////

$key = new Key("test", "test", 1);
var_dump($key);
// var_dump($key->namespace);
// var_dump($key->setname);
// var_dump($key->value);
// var_dump($key->digest);

////////////////////////////////////////////////////////////////////////////////
//
// client->truncate
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$client->truncate("test", "test");

////////////////////////////////////////////////////////////////////////////////
//
// client->put
//
////////////////////////////////////////////////////////////////////////////////

$wp = new WritePolicy();
$bin1 = new Bin("bin1", 111);
$bin2 = new Bin("bin2", "string");
$bin3 = new Bin("bin3", 333.333);
$bin4 = new Bin("bin4", [
	"str", 
	1984, 
	333.333, 
	[1, "string", 5.1], 
	[
		"integer" => 1984, 
		"float" => 333.333, 
		"list" => [1, "string", 5.1]
	] 
]);

$bin5 = new Bin("bin5", [
	"integer" => 1984, 
	"float" => 333.333, 
	"list" => [1, "string", 5.1], 
	null => [
		"integer" => 1984, 
		"float" => 333.333, 
		"list" => [1, "string", 5.1]
	],
	"" => [ 1, 2, 3 ],
]);

for ($x = 0; $x < 1000; $x++) {
	$key = new Key("test", "test", $x);
	$bin1 = new Bin("bin1", $x);
	$client = Aerospike($cp, "localhost:3000");
	$client->put($wp, $key, [$bin1, $bin2, $bin3, $bin4, $bin5]);
}


////////////////////////////////////////////////////////////////////////////////
//
// client->prepend
//
////////////////////////////////////////////////////////////////////////////////

$client->prepend($wp, $key, [new Bin("bin2", "prefix_")]);

////////////////////////////////////////////////////////////////////////////////
//
// client->append
//
////////////////////////////////////////////////////////////////////////////////

$client->append($wp, $key, [new Bin("bin2", "_suffix")]);

////////////////////////////////////////////////////////////////////////////////
//
// client->get
//
////////////////////////////////////////////////////////////////////////////////

$rp = new ReadPolicy();

$rp->setMaxRetries(3);
$timeInMillis = 3000;
$rp->timeout = $timeInMillis;

for ($x = 0; $x <= 1000; $x++) {
	$client = Aerospike($cp, "localhost:3000");
	$record = $client->get($rp, $key, ["bin1"]);
}

$client = Aerospike($cp, "localhost:3000");
$record = $client->get($rp, $key);
var_dump($record->bins);
var_dump($record->generation);
var_dump($record->key);

////////////////////////////////////////////////////////////////////////////////
//
// client->touch
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$client->touch($wp, $key);

$client = Aerospike($cp, "localhost:3000");
$record = $client->get($rp, $key, []);
var_dump($record->bin("bin1"));
var_dump($record->bin("bin2"));
var_dump($record->generation);

$client = Aerospike($cp, "localhost:3000");
$record = $client->get($rp, $key, ["bin1"]);
var_dump($record->bin("bin1"));
var_dump($record->bin("bin2"));

////////////////////////////////////////////////////////////////////////////////
//
// client->batchGet
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$bp = new BatchPolicy();


$br = [
	new BatchRead($key, ["bin1"]),
	new BatchRead($key),
	new BatchRead($key, []),
];

$batch_reads = $client->batchGet($bp, $br);
foreach ($batch_reads as &$br) {
	var_dump($br->record()->bins);
}


////////////////////////////////////////////////////////////////////////////////
//
// client->scan
//
////////////////////////////////////////////////////////////////////////////////


$sp = new ScanPolicy();
$client = Aerospike($cp, "localhost:3000");
$recordset = $client->scan($sp, "test", "test");

$count = 0;
$sum = 0;
while ($rec = $recordset->next()) {
	// var_dump($rec->bins["bin1"]);
	$count++;
	$sum+=$rec->bins["bin3"];
}

echo "Scan results count: $count\n";
echo "Scan results sum: $sum\n";


////////////////////////////////////////////////////////////////////////////////
//
// $client->exists
//
////////////////////////////////////////////////////////////////////////////////


$client = Aerospike($cp, "localhost:3000");
$exists = $client->exists($rp, $key);
var_dump($exists);


////////////////////////////////////////////////////////////////////////////////
//
// $client->delete
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$deleted = $client->delete($wp, $key);
var_dump($deleted);

$client = Aerospike($cp, "localhost:3000");
$exists = $client->exists($rp, $key);
var_dump($exists);

////////////////////////////////////////////////////////////////////////////////
//
// client->dropIndex
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$client->dropIndex("test", "test", "test.test.bin1");

////////////////////////////////////////////////////////////////////////////////
//
// $client->createIndex
//
////////////////////////////////////////////////////////////////////////////////

$client = Aerospike($cp, "localhost:3000");
$client->createIndex("test", "test", "bin1", "test.test.bin1", IndexType::Numeric());

sleep(1);

////////////////////////////////////////////////////////////////////////////////
//
// $client->query
//
////////////////////////////////////////////////////////////////////////////////

$qp = new QueryPolicy();
$statement = new Statement("test", "test", ["bin1"]);
$statement->filters = [Filter::range("bin1", 1, 10)];
$client = Aerospike($cp, "localhost:3000");
$recordset = $client->query($qp, $statement);

$count = 0;
$sum = 0;
while ($rec = $recordset->next()) {
	var_dump($rec->bins["bin1"]);
	$count++;
	$sum+=$rec->bins["bin1"];
}

echo "Query results count: $count\n";
echo "Query results sum: $sum\n";


////////////////////////////////////////////////////////////////////////////////
//
// create a value of certain Value type
//
////////////////////////////////////////////////////////////////////////////////

$geoVal = Value::geoJson("{\"type\":\"Point\",\"coordinates\":[-80.590003, 28.60009]}");
$geoBin = new Bin("Geo_Location", $geoVal); 


$client->close();