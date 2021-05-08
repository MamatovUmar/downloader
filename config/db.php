<?php

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=downloader',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',

        // Schema cache options (for production environment)
        //'enableSchemaCache' => true,
        //'schemaCacheDuration' => 60,
        //'schemaCache' => 'cache',
    ];
}else{
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=downloader',
        'username' => 'downloader',
        'password' => 'K6r3X0i5',
        'charset' => 'utf8',
    ];
}