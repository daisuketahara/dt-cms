<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\UserApiKeyRepository")
*/
class UserApiKey
{
    /**
    * @ORM\Id()
    * @ORM\GeneratedValue()
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\User")
    */
    private $user;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $keyName;

    /**
    * @ORM\Column(type="string", length=100, unique=true)
    */
    protected $token;

    /**
    * @ORM\Column(type="datetime")
    */
    protected $expire;

    /**
    * @ORM\Column(name="locked", type="boolean")
    */
    private $locked = false;

    /**
    * @ORM\Column(name="active", type="boolean")
    */
    private $active;


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
    * Get the value of User
    *
    * @return mixed
    */
    public function getUser()
    {
        return $this->user;
    }

    /**
    * Set the value of User
    *
    * @param mixed user
    *
    * @return self
    */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
    * Get the value of Key Name
    *
    * @return mixed
    */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
    * Set the value of Key Name
    *
    * @param mixed keyName
    *
    * @return self
    */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }

    /**
    * Get the value of Token
    *
    * @return mixed
    */
    public function getToken()
    {
        return $this->token;
    }

    /**
    * Set the value of Token
    *
    * @param mixed token
    *
    * @return self
    */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
    * Get the value of Expire
    *
    * @return mixed
    */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
    * Set the value of Expire
    *
    * @param mixed expire
    *
    * @return self
    */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * Get the value of Locked
     *
     * @return mixed
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set the value of Locked
     *
     * @param mixed $locked
     *
     * @return self
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
    * Get the value of Active
    *
    * @return mixed
    */
    public function getActive()
    {
        return $this->active;
    }

    /**
    * Set the value of Active
    *
    * @param mixed active
    *
    * @return self
    */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
