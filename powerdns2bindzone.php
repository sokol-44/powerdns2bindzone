#!/bin/env php
<?php

/**
 * @copyright 2013 James Linden <kodekrash@gmail.com>
 * @author James Linden <kodekrash@gmail.com>
 * @url http://jameslinden.com/code/powerdns-to-bind-zone
 * @license BSD (2 clause) <http://www.opensource.org/licenses/BSD-2-Clause>
 */

$pdns_db = [ 'host' => 'localhost', 'user' => 'pdns_user', 'pass' => 'pdns_pass', 'name' => 'pdns_db_name' ];
$zone_ns = [ 'ns1.example.com', 'ns2.example.com' ];
$zone_adm = 'support.example.com';

$zones = [];
$db = new mysqli( $pdns_db['host'], $pdns_db['user'], $pdns_db['pass'], $pdns_db['name'] );
if( $q = $db->query( 'SELECT * FROM domains' ) ) {
    while( $r = $q->fetch_assoc() ) {
        $d = strtolower( $r['name'] );
        if( $q1 = $db->query( 'SELECT * FROM records WHERE domain_id=' . $r['id'] ) ) {
            $x = [ 'A' => [], 'CNAME' => [], 'NS' => [], 'MX' => [], 'TXT' => [] ];
            while( $r1 = $q1->fetch_assoc() ) {
                $r1['name'] = strtolower( $r1['name'] );
                switch( $r1['type'] ) {
                    case 'A':
                        $x[ $r1['type'] ][] = [ $r1['name'], $r1['content'] ];
                    break;
                    case 'CNAME':
                        $x[ $r1['type'] ][] = [ trim( str_replace( $d, null, $r1['name'] ), '.' ), strtolower( $r1['content'] ) ];
                    break;
                    case 'NS':
                        $x[ $r1['type'] ][] = strtolower( $r1['content'] );
                    break;
                    case 'MX':
                        $x[ $r1['type'] ][] = [ (int)$r1['prio'], strtolower( $r1['content'] ) ];
                    break;
                    case 'TXT':
                        $x[ $r1['type'] ][] = [ trim( str_replace( $d, null, $r1['name'] ), '.' ), $r1['content'] ];
                    break;
                }
            }
            $q1->free();
        }
        $zones[ $d ] = $x;
    }
    $q->free();
}
$db->close();

if( is_array( $zones ) && count( $zones ) > 0 ) {
    $p = './tmp/';
    if( !is_dir( $p ) ) {
        if( !mkdir( $p ) ) {
            die( 'Unable to make tmp directory' . PHP_EOL );
        }
    }
    if( count( $zones ) > 0 ) {
        foreach( $zones as $d => $r ) {
            $x = '             ';
            $t = [ '$TTL 43200', null, '@ IN SOA ' . $zone_ns[0] . '. ' . $zone_adm . '. (', $x . time(), $x . '7200', $x . '3600', $x . '604800', $x . '43200 )', null ];
            foreach( $zone_ns as $x ) {
                $t[] = '  IN NS      ' . $x . '.';
            }
            $t[] = null;
            foreach( $r['MX'] as $x ) {
                $t[] = '  IN MX ' . str_pad( $x[0], 5, ' ', STR_PAD_RIGHT ) . $x[1] . ( is_numeric( substr( $x[1], -1, 1 ) ) ? null : '.' );
            }
            $t[] = null;
            foreach( $r['A'] as $x ) {
                $t[] = str_pad( $x[0] . '.', 32, ' ', STR_PAD_RIGHT ) . '  IN A ' . $x[1];
            }
            $t[] = null;
            foreach( $r['CNAME'] as $x ) {
                $t[] = str_pad( $x[0], 32, ' ', STR_PAD_RIGHT ) . '  IN CNAME ' . $x[1] . '.';
            }
            $t[] = null;
            foreach( $r['TXT'] as $x ) {
                $t[] = str_pad( $x[0], 32, ' ', STR_PAD_RIGHT ) . '  IN TXT ' . $x[1];
            }
            file_put_contents( $p . $d . '.zone', implode( PHP_EOL, $t ) . PHP_EOL );
        }
    }
}

?>