## This project is pre-alpha, and should not be used in production. If you're an enterprise customer feel free to reach out to our support with feedback and feature requests. We appreciate feedback from the Aerospike community on issues related to the new PHP client.

# Aerospike PHP@8+ Client

An [Aerospike](https://www.aerospike.com/) client library for PHP@8+.

## Requirements

* PHP 8.1+
* Composer
* cargo
* Aerospike server v6.3 

* NOTE: Does not support Scan/Query API features for Aerospike server 6.4.
* NOTE: Currently does not support Windows platform. [Will be adding support in future release.]

## Setup

### Installation via Composer

* Add the following in the 'require' section of composer.json
    ``` "aerospike/aerospike-php": "v0.3.0-alpha" ```
* Run ```composer upgrade```
* ```cd vendor/aerospike/aerospike-php && sudo composer install```

### Manual Installation:

* Clone the repository ```git clone https://github.com/aerospike/php-client.git```
* ```cd php-client```
* Build and run the test code via: ```make```
* Once this is done, it will install the extension in your php extension directory
* Supported platforms - Linux, Darwin
### NOTE: Use Aerospike Server v6.3 for testing; The Rust client does not support the newer servers entirely.



## Documentation
* Php stubs and API documentation can be found [here](https://github.com/aerospike/php-client/blob/php-rs/php_code_stubs/php_stubs.php)
* GeoFilter examples can be found [here](https://github.com/aerospike/php-client/php-rs/blob/examples/geoQueryFilter.php)


## Usage
The following is a very simple example of CRUD operations in an Aerospike database.

## Issues
If there are any isses please create an issue on our github repository page - [github-issues](https://github.com/aerospike/php-client/issues)

```php

<?
$cp = new ClientPolicy();
$client = Aerospike($cp, "127.0.0.1:3000");
$key = new Key("test", "test", 1);

$wp = new WritePolicy();
$bin1 = new Bin("bin1", 111);
$client->put($wp, $key, [$bin1]);

$client->prepend($wp, $key, [new Bin("bin2", "prefix_")]);

$client->append($wp, $key, [new Bin("bin2", "_suffix")]);

$rp = new ReadPolicy();
$record = $client->get($rp, $key);
var_dump($record->bins);

$deleted = $client->delete($wp, $key);
var_dump($deleted);

$exists = $client->exists($wp, $key);
var_dump($exists);

$client->close();

```





