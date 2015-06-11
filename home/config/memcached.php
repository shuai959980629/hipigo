<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/**
 *
 * @copyright (c) 2013-12-3
 * @author msi
 * @version Id:memcached.php
 */

$config= array(
    'default' => array(
        'host' => '192.168.0.6',
        'port'        => 11211,
        'weight'    => 80
    ),
    /*  'server_1' => array(
        'host' => '121.52.217.99',
        'port'        => 11211,
        'weight'    => 50
    ), */
    /*'server_2' => array(
        'host' => '127.0.0.3',
        'port'        => 11211,
        'weight'    => 10
    ) */
);



/* End of file memcached.php */