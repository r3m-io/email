<?php
namespace Package\R3m\Io\Email\Trait;

use DateTime;

use Entity\EmailQueue;

//use Domain\Api_Workandtravel_World\Service\Email;
//use Domain\Api_Workandtravel_World\Service\Entity;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use R3m\Io\Doctrine\Service\Entity;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;

use Exception;

use R3m\Io\Exception\AuthorizationException;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;

trait Main {

    const DURATION = 60;

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
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public static function send(App $object, Data $config, $record=[]): bool
    {
        try {
            $dsn =
                'smtp://' .
                $config->get('username') .
                ':' .
                $config->get('password') .
                '@' .
                $config->get('hostname') .
                ':' .
                $config->get('port');
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);
            $email = (new MimeEmail())->from(new Address($config->get('from.email'), $config->get('from.name')));
            if(!array_key_exists('to', $record)){
                return false;
            }
            foreach($record['to'] as $to){
                $explode = explode(' <', $to, 2);
                if(array_key_exists(1, $explode)){
                    $email->to(Address::create($to));
                } else {
                    $email->to(new Address($to));
                }
            }
            if(!empty($record['replyTo'])){
                foreach($record['replyTo'] as $replyTo){
                    $explode = explode(' <', $replyTo, 2);
                    if(array_key_exists(1, $explode)){
                        $email->replyTo(Address::create($replyTo));
                    } else {
                        $email->replyTo(new Address($replyTo));
                    }
                }
            }
            if(!empty($record['cc'])){
                foreach($record['cc'] as $cc){
                    $explode = explode(' <', $cc, 2);
                    if(array_key_exists(1, $explode)){
                        $email->cc(Address::create($cc));
                    } else {
                        $email->cc(new Address($cc));
                    }
                }
            }
            if(!empty($record['bcc'])){
                foreach($record['bcc'] as $bcc){
                    $explode = explode(' <', $bcc, 2);
                    if(array_key_exists(1, $explode)){
                        $email->bcc(Address::create($bcc));
                    } else {
                        $email->bcc(new Address($bcc));
                    }
                }
            }
            $email
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject($record['subject'])
                ->text($record['text'])
                ->html($record['body']);

            if(!empty($record['attachment'])){
                foreach($record['attachment'] as $attachment){
                    if(File::exist($attachment)){
                        $email->attachFromPath($attachment);
                    }
                }
            }
            $mailer->send($email);
            $logger = $object->config('project.log.email');
            if(
                $logger &&
                array_key_exists('uuid', $record)
            ){
                $object->logger($logger)->info('Mail send...', [$record['uuid']]);
            }
            return true;
        }
        catch(Exception | RfcComplianceException $exception){
            if(get_class($exception) === RfcComplianceException::class){
                if(empty($logger)){
                    $logger = $object->config('project.log.email');
                }
                if($logger){
                    $object->logger($logger)->error($exception->getMessage(), [$record['uuid']]);
                }
            } else {
                throw $exception;
            }
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function queue($options): void
    {
        $object = $this->object();
        $id = posix_geteuid();
        if(!empty($id)){
            throw new Exception('Only root can use Mail::queue');
        }
        $function = __FUNCTION__;
        $start = microtime(true);
        if($object->config('email.queue.amount_per_minute')){
            $sleep = self::DURATION / $object->config('email.queue.amount_per_minute');
        } else {
            $sleep = 2;
        }
        $entityManager = Database::entityManager($object);
        $logger = $object->config('project.log.email');
        $role = false;
        if(posix_geteuid() === 0){
            $entity = 'Role';
            $repository = $entityManager->getRepository($object->config('doctrine.entity.prefix') . $entity);
            $criteria = [];
            $criteria['name'] = 'ROLE_SYSTEM';
            $role = $repository->findOneBy($criteria);
        } else {
            throw new AuthorizationException('This command can only be executed by root.');
        }
        $uuid = Core::uuid();
        if(
            $object->config('ramdisk.url') &&
            empty($object->config('ramdisk.is.disabled'))
        ){
            $lock_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Lock' .
                $object->config('ds')
            ;
        } else {
            $lock_dir = $object->config('framework.dir.temp') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Lock' .
                $object->config('ds')
            ;
        }
        $lock_url = $lock_dir .
            'Mail.lock'
        ;
        while(true){
            $time = microtime(true);
            if(($time - $start) >= self::DURATION){
                break;
            }
            if(
                !File::exist($lock_url) ||
                (
                    File::exist($lock_url) &&
                    File::mtime($lock_url) - $start <= self::DURATION
                )
            ){
                if(File::exist($lock_url)){
                    $read = File::read($lock_url);
                    if($read === $uuid){
                        $entity = 'EmailQueue';
                        $repository = $entityManager->getRepository($object->config('doctrine.entity.prefix') . $entity);
                        $criteria = [];
                        $criteria['isSend'] = null;
                        $order = [];
                        $order['priority'] = 'DESC';
                        $order['isCreated'] = 'ASC';
                        $node = $repository->findOneBy($criteria, $order);
                        if($node){
                            if($object->config('email.queue.amount_per_minute')){
                                $sleep = self::DURATION / $object->config('email.queue.amount_per_minute');
                            } else {
                                $sleep = 2;
                            }
                            $config = $object->config('email.account');
                            if($config){
                                $config = new Data(Core::object($config, Core::OBJECT_OBJECT));
                                $expose = Entity::expose_get(
                                    $object,
                                    $entity,
                                    $entity . '.queue.expose'
                                );
                                $record = Entity::expose(
                                    $object,
                                    clone $node,
                                    $expose,
                                    $entity,
                                    $function,
                                    $role
                                );
                                ddd($record);
                                $this->send($object, $config, $record);
                                $node->setIsSend(new DateTime());
                                $entityManager->persist($node);
                                $entityManager->flush();
                            }
                        } else {
                            $sleep = $object->config('email.queue.wait_inactive');
                            if(empty($sleep)){
                                $sleep = self::DURATION / 3;
                            }
                        }
                    }
                } else {
                    Dir::create($lock_dir, Dir::CHMOD);
                    File::write($lock_url, $uuid);
                }
            } else {
                $object->logger($logger)->notice('EmailQueue already running...');
            }
            $this->sleep($sleep);
        }
        File::remove($lock_url);
    }

    private function sleep($sleep=1): void
    {
        $sleep_old = $sleep;
        $sleep = floor($sleep);
        if(
            $sleep_old - $sleep > 0 &&
            $sleep_old - $sleep < 1
        ){
            $usleep = ($sleep_old - $sleep) * 1000000;
            usleep($usleep);
        }
        sleep($sleep);
    }
}