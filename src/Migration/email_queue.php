<?php

use R3m\Io\App;

use R3m\Io\Module\Controller;
use R3m\Io\Module\Database;
//use Doctrine\DBAL\Schema\Schema;
//use Doctrine\DBAL\Types\Types;

use R3m\Io\Doctrine\Service\Table;
use R3m\Io\Doctrine\Service\Schema;

/**
 * @throws \R3m\Io\Exception\ObjectException
 * @throws \Doctrine\DBAL\Exception
 */


return function(App $object, $flags, $options) {
    // Your migration code here
    /*
    Database::instance($object, "api");
    $connection = Database::connection($object, "api");
    $sm = Database::schema_manager($object, "api");
    $platform = Database::platform($object, "api");
    */

    Database::instance($object, "system");
    $connection = Database::connection($object, "system");
    $platform = Database::platform($object, "system");
    $table = 'email_queue';
//    $name = 'Email.Queue';
    Database::options($object, $options, "system", null, $table, $count, $is_install);
    if($is_install === true){
        $url = $object->config('project.dir.package') .
            'R3m' .
            $object->config('ds') .
            'Io' .
            $object->config('ds') .
            'Email' .
            $object->config('ds') .
            'Schema' .
            $object->config('ds') .
            Controller::name($table) .
            $object->config('extension.json')
        ;
        Schema::entity($object, (object) [
            'table' => $table,
            'url' => $url
        ]);

        ddd($url);
        $doctrine_options = (object) [
            'platform' => $platform,
            'url' => $url
        ];
        $queries = Table::create($object, null, $doctrine_options);
        foreach($queries as $sql){
            $connection->executeStatement($sql);
            $count++;
        }
        echo 'Executed: ' . $count . ' queries.' . PHP_EOL;
    } else {
        echo 'Table: ' . $table . ' already exists.' . PHP_EOL;
    }
};