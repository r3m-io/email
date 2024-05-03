<?php

use R3m\Io\App;

use R3m\Io\Module\Database;

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
        $introspect->addColumn('isCreated', 'datetime' ['current_timestamp' => true]);
        $introspect->addColumn('isUpdated', 'datetime' ['current_timestamp' => true]);
        ddd('not exist email_queue.php');
    }


};