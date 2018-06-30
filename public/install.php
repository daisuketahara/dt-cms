<?php

    $mode = '';

    $dbHost = '';
    $dbName = '';
    $dbUser = '';
    $dbPassword = '';

    $spryngUser = '';
    $spryngPassword = '';

    $mailType = '';
    $mailHost = '';
    $mailPort = '';
    $mailUser = '';
    $mailPassword = '';

    $files = array(
        'config/bundles.php.dist' => 'config/bundles.php',
        'config/routes.yaml.dist' => 'config/routes.yaml',
        'config/services.yaml.dist' => 'config/services.yaml',
    );
    foreach($files as $dist => $file) {
        copy($dist, $file);
    }
