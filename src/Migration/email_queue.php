<?php

use R3m\Io\App;

use R3m\Io\Module\Database;

return function(App $object, $flags, $options) {
    // Your migration code here

    $em = Database::entityManager($object);

    $schema = $em->getConnection()->createSchemaManager();

    $tables = [
        'email_queue'
    ];

    if ($schema->tablesExist($tables) == true){
        // table exists! ...
        ddd('exist email_queue.php');
    } else {
        ddd('not exist email_queue.php');
    }


};