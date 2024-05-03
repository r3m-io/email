<?php

use R3m\Io\App;

return function(App $object, $flags, $options) {
    // Your migration code here
    ddd('email_queue.php');
};