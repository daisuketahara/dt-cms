<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LogRepository")
 */
class Log
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $accountId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $entity;

    /**
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @ORM\Column(type="text")
     */
    protected $log;

    /**
     * @ORM\Column(type="text")
     */
    protected $comment;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $userIp;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationDate;

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Account Id
     *
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Set the value of Account Id
     *
     * @param mixed accountId
     *
     * @return self
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get the value of Entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of Entity
     *
     * @param mixed entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of Entity Id
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set the value of Entity Id
     *
     * @param mixed entityId
     *
     * @return self
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get the value of Log
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set the value of Log
     *
     * @param mixed log
     *
     * @return self
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get the value of Comment
     *
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the value of Comment
     *
     * @param mixed comment
     *
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the value of User Ip
     *
     * @return mixed
     */
    public function getUserIp()
    {
        return long2ip($this->userIp);
    }

    /**
     * Set the value of User Ip
     *
     * @param mixed userIp
     *
     * @return self
     */
    public function setUserIp($userIp)
    {
        $this->userIp = ip2long($userIp);

        return $this;
    }

    /**
     * Get the value of Creation Date
     *
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate->format('Y-m-d H:i:s');
    }

    /**
     * Set the value of Creation Date
     *
     * @param mixed creationDate
     *
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

}
