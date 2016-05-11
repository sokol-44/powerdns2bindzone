# PowerDNS to Bind Zone Generator

## Overview
The PowerDNS to Bind Zone Generator is a simple PHP script to generate Bind-style zone files from a PowerDNS MySQL backend. It is intended as a utility to make migrating zones from a PowerDNS server to a Bind server, not as a straight backup script. The generated zones use configurable name servers and create new SOA and serials, etc.

### Supports the following record types:
* A
* CNAME
* MX
* TXT

## Environment
* Linux
* PHP 5.4 +
* PowerDNS (with MySQL backend)

## Notes
* This script is designed to run on the command line.

## Howto
Download powerdns2bindzone.php and edit the configuration section at the beginning of the file.
```php
$pdns_db = [ 'host' => 'localhost', 'user' => 'pdns_user', 'pass' => 'pdns_pass', 'name' => 'pdns_db_name' ];
$zone_ns = [ 'ns1.example.com', 'ns2.example.com' ];
$zone_adm = 'support.example.com';
```
You can specify as many name servers in $zone_ns as you need, as long as the first value is the primary name server.
Run the script. It will create a tmp directory in the location you run it from and save the zone files there.

## Example Generated Zone
```shell
$TTL 43200

@ IN SOA ns1.example.com. support.example.com. (
             1374091666
             7200
             3600
             604800
             43200 )

  IN NS      ns1.example.com.
  IN NS      ns2.example.com.

  IN MX 10   mx1.mymailhost.tld.

example.com.         IN A xx.xx.xx.xx
ns1.example.com      IN A xx.xx.xx.xx
ns2.example.com      IN A xx.xx.xx.xx

www                  IN CNAME example.com.
```
## License
This project is BSD (2 clause) licensed.
