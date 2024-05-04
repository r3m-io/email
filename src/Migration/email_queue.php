<?php

use R3m\Io\App;

use R3m\Io\Module\Database;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

return function(App $object, $flags, $options) {
    // Your migration code here
    Database::instance($object, $em, $connection, $platform, $sm);
    $table = 'email_queue';
    Database::options($object, $connection, $sm, $options, $table, $count, $is_install);
    if($is_install === true){
        $schema = new Schema();
        $schema_table = $schema->createTable($table);
        $schema_table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $schema_table->addColumn('uuid', Types::STRING, ['length' => 36]);
        $schema_table->addColumn('to', Types::TEXT);
        $schema_table->addColumn('reply_to', Types::TEXT, ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('cc', Types::TEXT, ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('bcc', Types::TEXT, ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('subject', Types::TEXT);
        $schema_table->addColumn('text', Types::TEXT);
        $schema_table->addColumn('body', Types::TEXT);
        $schema_table->addColumn('attachment', Types::TEXT, ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('priority', Types::SMALLINT, ['default' => 1]);
        $schema_table->addColumn('isSend', Types::DATETIME_MUTABLE, ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('isCreated', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $schema_table->addColumn('isUpdated', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $schema_table->setPrimaryKey(["id"]);
        $schema_table->addUniqueIndex(["uuid"]);
        $queries = $schema->toSql($platform);
        foreach($queries as $sql){
            $connection->executeStatement($sql);
            $count++;
        }
        echo 'Executed: ' . $count . ' queries.' . PHP_EOL;
    } else {
        echo 'Table: ' . $table . ' already exists.' . PHP_EOL;
    }
};