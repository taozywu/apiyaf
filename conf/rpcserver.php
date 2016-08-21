<?php

/**
 * 说明一点
 * return array(
 *     'rpc_secret_key' => ************,
 *     'OpSys' => // Service下目录名。必须绝对 
 * );
 */
return array(
    // secret.
    'rpc_secret_key' => 'd2d2f3f8b77b168b3622390b135d3b2f',
    // service config.
    'OpSys' => array(
        'uri' => 'tcp://127.0.0.1:9502',
        'user' => 'toolOpSys', // this is same to service/Config/Auth.php's $var.
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
        // 'compressor' => 'GZ',
    ),
    'TaoFilm' => array(
        'uri' => 'tcp://127.0.0.1:9503',
        'user' => 'toolTaoFilm',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
        // 'compressor' => 'GZ',
    ),
);
