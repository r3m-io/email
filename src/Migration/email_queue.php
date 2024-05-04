<?php

use R3m\Io\App;

use R3m\Io\Module\Database;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

use R3m\Io\Doctrine\Service\Table;

/**
 * @throws \R3m\Io\Exception\ObjectException
 * @throws \Doctrine\DBAL\Exception
 */


return function(App $object, $flags, $options) {
    // Your migration code here
    Database::instance($object, "ramdisk", $em, $connection, $platform, $sm);
    $table = 'email_queue';
    Database::options($object, $connection, $sm, $options, $table, $count, $is_install);
    if($is_install === true){
        $url = $object->config('project.dir.source') .
            'Entity' .
            $object->config('ds') .
            'Schema' .
            $object->config('ds') .
            $table .
            $object->config('extension.json')
        ;
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