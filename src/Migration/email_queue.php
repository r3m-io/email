<?php

use R3m\Io\App;

use R3m\Io\Module\Database;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMapping;

return function(App $object, $flags, $options) {
    // Your migration code here

    $em = Database::entityManager($object);
    $connection = $em->getConnection();
    $platform = $connection->getDatabasePlatform();
    $sm = $connection->createSchemaManager();

    $table = 'email_queue';

    $tables = [
        $table
    ];

    if ($sm->tablesExist($tables) == true){
        // table exists! ...
        //$columns = $sm->listTableColumns('user');
        ddd('exist email_queue.php');
    } else {
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
        $queries = $schema->toSql($platform);
        // Create a ResultSetMapping
        $rsm = new ResultSetMapping();
        foreach($queries as $sql){
            // Create a native query
            $query = $em->createNativeQuery($sql, $rsm);
            // Execute the query
            $query->getResult();
        }
        echo 'Executed: ' . count($queries) . ' queries' . PHP_EOL;
    }
};