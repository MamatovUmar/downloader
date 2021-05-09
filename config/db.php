<?php


return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname='. env('DB_NAME'),
    'username' => env('DB_USER_NAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
