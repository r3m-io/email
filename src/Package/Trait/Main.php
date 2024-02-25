<?php
namespace Package\R3m\Io\Email\Trait;

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;

trait Main {

    /**
     * @throws Exception
     */
    public function compile($options=[]): void
    {
        $object = $this->object();
        $posix_id = $object->config(Config::POSIX_ID);
        if(
            !in_array(
                $posix_id,
                [
                    0,
                    33
                ]
            )
        ){
            throw new Exception('Access denied...');
        }
        ddd($options);
    }

    /**
     * @throws Exception
     */
    public function ast($options): void
    {
        $object = $this->object();
        $posix_id = $object->config(Config::POSIX_ID);

        if(
            !in_array(
                $posix_id,
                [
                    0,
                    33
                ]
            )
        ){
            throw new Exception('Access denied...');
        }
        if(property_exists($options, 'document') === false){
            throw new Exception('Document not found...');
        }
        if(property_exists($options, 'data') === false){
            $data = [];
        }
        elseif(File::exist($options->data)){
            $data = Core::object(File::read($options->data), Core::OBJECT_OBJECT);
            ddd($data);
        }

        $read = File::read($options->document);
        ddd($options);
    }
}