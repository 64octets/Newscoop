<?php

global $g_ado_db;

/**
 * Display error message and die.
 */
$displayError = function () {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    echo <<<EOT
<div style="color:red;font-size:2em">
    <p>ERROR connecting to the MySQL server!</p>
    <p>Please start the MySQL database server and verify if the connection
configuration is valid.</p>
</div>
EOT;
    exit(1);
};

if (empty($g_ado_db)) {
    if (Zend_Registry::isRegistered('container')) {
        $container = Zend_Registry::get('container');
    } else {
        global $application;
        $application->bootstrap('container');
        $container = $application->getBootstrap()->getResource('container');
    }

    $g_ado_db = $container->getService('doctrine.adodb');
}

if (!$g_ado_db->isConnected(true)) {
    $displayError();
}

