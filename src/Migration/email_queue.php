<?php

use R3m\Io\App;

use R3m\Io\Module\Database;
use Doctrine\DBAL\Schema\Schema;

return function(App $object, $flags, $options) {
    // Your migration code here

    $em = Database::entityManager($object);

    $sm = $em->getConnection()->createSchemaManager();

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
        $schema_table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $schema_table->addColumn('uuid', 'string', ['length' => 36]);
        $schema_table->addColumn('to', 'text');
        $schema_table->addColumn('reply_to', 'text', ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('cc', 'text', ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('bcc', 'text', ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('subject', 'text');
        $schema_table->addColumn('text', 'text');
        $schema_table->addColumn('body', 'text');
        $schema_table->addColumn('attachment', 'text', ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('priority', 'smallint', ['default' => 1]);
        $schema_table->addColumn('isSend', 'datetime', ['default' => null, 'notnull' => false]);
        $schema_table->addColumn('isCreated', 'datetime', ['default' => 'current_timestamp']);
        $schema_table->addColumn('isUpdated', 'datetime', ['default' => 'current_timestamp']);

        $queries = $schema->toSql();
        ddd($queries);

        /*
        $introspect = $sm->introspectTable($table);
        $introspect->addColumn('id', 'integer', ['autoincrement' => true]);
        $introspect->addColumn('uuid', 'varchar', ['length' => 36]);
        $introspect->addColumn('to', 'longtext');
        $introspect->addColumn('reply_to', 'longtext', ['default' => null, 'null' => true]);
        $introspect->addColumn('cc', 'longtext', ['default' => null, 'null' => true]);
        $introspect->addColumn('bcc', 'longtext', ['default' => null, 'null' => true]);
        $introspect->addColumn('subject', 'longtext');
        $introspect->addColumn('text', 'longtext');
        $introspect->addColumn('body', 'longtext');
        $introspect->addColumn('attachment', 'longtext');
        $introspect->addColumn('priority', 'smallint', ['default' => 1]);
        $introspect->addColumn('isSend', 'datetime', ['default' => null, 'null' => true]);
        $introspect->addColumn('isCreated', 'datetime', ['current_timestamp' => true]);
        $introspect->addColumn('isUpdated', 'datetime', ['current_timestamp' => true]);
        */
        ddd('not exist email_queue.php');
    }


};