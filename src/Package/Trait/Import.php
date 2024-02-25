<?php
namespace Package\R3m\Io\Email\Trait;

use R3m\Io\App;

use R3m\Io\Exception\DirectoryCreateException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use R3m\Io\Node\Model\Node;

use Exception;
trait Import {

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function role_system(): void
    {
        $object = $this->object();
        $package = $object->request('package');
        if($package){
            $node = new Node($object);
            $node->role_system_create($package);
        }
    }

    /**
     * @throws Exception
     */

    public function config_email(): void
    {
        $object = $this->object();
        $package = $object->request('package');
        if($package){
            $options = App::options($object);
            $class = 'System.Config.Email';
            $options->url = $object->config('project.dir.vendor') .
                $package . '/Data/' .
                $class .
                $object->config('extension.json')
            ;
            $node = new Node($object);
            $response = $node->import($class, $node->role_system(), $options);
            $node->stats($class, $response);

            $class = 'System.Config';
            $response = $node->record($class, $node->role_system());
            if(
                $response &&
                array_key_exists('node', $response) &&
                property_exists($response['node'], 'uuid')
            ){
                $record = (object) [];
                $record->uuid = $response['node']->uuid;
                $record->email = '*';
                $response = $node->patch($class, $node->role_system(), $record);
            }
            ddd($response);
        }
    }
}