<?php
namespace Entity;

use DateTime;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\FileWriteException;

#[ORM\Entity]
#[ORM\Table(name: "email_attachment")]
#[ORM\HasLifecycleCallbacks]
class EmailQueue {
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected $id;
    #[ORM\Column(type: "string", unique: true)]
    protected $uuid;
    #[ORM\Column(type: "json", name: "`to`")]
    protected $to;
    #[ORM\Column(type: "json", name: "`reply_to`", nullable: true)]
    protected $replyTo;
    #[ORM\Column(type: "json", nullable: true)]
    protected $cc;
    #[ORM\Column(type: "json", nullable: true)]
    protected $bcc;
    #[ORM\Column(type: "text")]
    protected $subject;
    #[ORM\Column(type: "text")]
    protected $text;
    #[ORM\Column(type: "text")]
    protected $body;
    #[ORM\Column(type: "json", nullable: true)]
    protected $attachment;
    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    protected $priority;
    #[ORM\Column(type: "datetime", nullable: true)]
    protected $isSend;
    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    protected $isCreated;
    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    protected $isUpdated;

    protected $is_text_decrypted = false;
    protected $is_body_decrypted = false;
    protected $is_subject_decrypted = false;
    protected $is_attachment_decrypted = false;

    private $object;

    public function __construct()
    {
        $this->setPriority();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid($uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo($to=[]): void
    {
        $this->to = $to;
    }

    public function getReplyTo(): ?array
    {
        return $this->replyTo;
    }

    public function setReplyTo($replyTo=[]): void
    {
        $this->replyTo = $replyTo;
    }

    public function getCc(): ?array
    {
        return $this->cc;
    }

    public function setCc($cc=[]): void
    {
        $this->cc = $cc;
    }

    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    public function setBcc($bcc=[]): void
    {
        $this->bcc = $bcc;
    }

    public function getPriority(): int
    {
        if($this->priority === null){
            $this->setPriority();
        }
        return $this->priority;
    }

    public function setPriority($priority=1): void
    {
        $this->priority = $priority;
    }

    public function setObject(App $object=null): void
    {
        $this->object = $object;
    }

    public function getObject(){
        return $this->object;
    }

    public function getSubject(): string
    {
        try {
            $object = $this->getObject();
            if(
                is_object($object) &&
                $this->is_subject_decrypted === false
            ){
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                if(File::exist($url)){
                    $string = File::read($url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $this->subject = Crypto::decrypt($this->subject, $key);
                    $this->is_subject_decrypted = true;
                }
            }
        } catch (
        Exception |
        BadFormatException |
        EnvironmentIsBrokenException |
        WrongKeyOrModifiedCiphertextException $exception
        ) {
            $this->is_subject_decrypted = true;
        }
        return $this->subject;
    }

    public function setSubject($subject=''): void
    {
        $object = $this->getObject();
        if(is_object($object)){
            try {
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                $key = Core::key($url);
                $subject = Crypto::encrypt($subject, $key);
                $this->is_subject_decrypted = false;
            } catch (
            Exception |
            BadFormatException |
            EnvironmentIsBrokenException |
            FileWriteException $exception
            ){
                $this->is_subject_decrypted = true;
            }
        }
        $this->subject = $subject;
    }

    public function getText(): string
    {
        try {
            $object = $this->getObject();
            if(
                is_object($object) &&
                $this->is_text_decrypted === false
            ){
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                if(File::exist($url)){
                    $string = File::read($url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $this->text = Crypto::decrypt($this->text, $key);
                    $this->is_text_decrypted = true;
                }
            }
        } catch (
        Exception |
        BadFormatException |
        EnvironmentIsBrokenException |
        WrongKeyOrModifiedCiphertextException $exception
        ) {
            $this->is_text_decrypted = true;
        }
        return $this->text;
    }

    public function setText($text=''): void
    {
        $object = $this->getObject();
        if(is_object($object)){
            try {
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                $key = Core::key($url);
                $text = Crypto::encrypt($text, $key);
                $this->is_text_decrypted = false;
            } catch (
            Exception |
            BadFormatException |
            EnvironmentIsBrokenException |
            FileWriteException $exception
            ){
                $this->is_text_decrypted = true;
            }
        }
        $this->text = $text;
    }

    public function getBody(): string
    {
        try {
            $object = $this->getObject();
            if(
                is_object($object) &&
                $this->is_body_decrypted === false
            ){
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                if(File::exist($url)){
                    $string = File::read($url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $this->body = Crypto::decrypt($this->body, $key);
                    $this->is_body_decrypted = true;
                }
            }
        } catch (
        Exception |
        BadFormatException |
        EnvironmentIsBrokenException |
        WrongKeyOrModifiedCiphertextException $exception
        ) {
            $this->is_body_decrypted = true;
        }
        return $this->body;
    }

    public function setBody($body=''): void
    {
        $object = $this->getObject();
        if(is_object($object)){
            try {
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                $key = Core::key($url);
                $body = Crypto::encrypt($body, $key);
                $this->is_body_decrypted = false;
            } catch (
            Exception |
            BadFormatException |
            EnvironmentIsBrokenException |
            FileWriteException $exception
            ){
                $this->is_body_decrypted = true;
            }
        }
        $this->body= $body;
    }

    public function getAttachment(): ?array
    {
        try {
            $object = $this->getObject();
            if(
                is_object($object) &&
                $this->is_attachment_decrypted === false
            ){
                $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                if(File::exist($url)){
                    $string = File::read($url);
                    $key = Key::loadFromAsciiSafeString($string);
                    if(is_array($this->attachment)){
                        foreach($this->attachment as $nr => $value){
                            $this->attachment[$nr] = Crypto::decrypt($value, $key);
                        }
                    }
                    $this->is_attachment_decrypted = true;
                }
            }
        } catch (
        Exception |
        BadFormatException |
        EnvironmentIsBrokenException |
        WrongKeyOrModifiedCiphertextException $exception
        ) {
            $this->is_attachment_decrypted = true;
        }
        return $this->attachment;
    }

    public function setAttachment($attachment=[]): void
    {
        $object = $this->getObject();
        if(is_object($object)){
            foreach($attachment as $nr => $value){
                try {
                    $url = $object->config('project.dir.data') . 'Defuse/Email.key';
                    $key = Core::key($url);
                    $value = Crypto::encrypt($value, $key);
                    $attachment[$nr] = $value;
                    $this->is_attachment_decrypted = false;
                } catch (
                Exception |
                BadFormatException |
                EnvironmentIsBrokenException |
                FileWriteException $exception
                ){
                    $this->is_attachment_decrypted = true;
                }

            }
        }
        $this->attachment = $attachment;
    }

    public function getIsSend(): ?DateTime
    {
        return $this->isSend;
    }

    public function setIsSend(DateTime $isSend=null): void
    {
        $this->isSend = $isSend;
    }

    public function getIsCreated(): DateTime
    {
        return $this->isCreated;
    }

    public function setIsCreated(DateTime $isCreated): void
    {
        $this->isCreated = $isCreated;
    }

    public function getIsUpdated(): DateTime
    {
        return $this->isUpdated;
    }

    public function setIsUpdated(DateTime $isUpdated): void
    {
        $this->isUpdated = $isUpdated;
    }

    #[PrePersist]
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->setUuid(Core::uuid());
        $dateTime = new DateTime();
        $this->setIsCreated($dateTime);
        $this->setIsUpdated($dateTime);
    }

    #[PreUpdate]
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $object = $this->getObject();
        if($object){
            if($this->is_body_decrypted === true){
                $this->setBody($this->getBody());
            }
            if($this->is_subject_decrypted === true){
                $this->setSubject($this->getSubject());
            }
            if($this->is_text_decrypted === true){
                $this->setText($this->getText());
            }
        }
        $this->setIsUpdated(new DateTime());
    }

}

