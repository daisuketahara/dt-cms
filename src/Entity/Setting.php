<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    protected $settingKey;

    /**
     * @ORM\Column(type="text")
     */
    protected $settingValue;

    /**
     * @ORM\Column(type="integer")
     */
    protected $protected = 0;

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
     * Get the value of Setting Key
     *
     * @return mixed
     */
    public function getSettingKey()
    {
        return $this->settingKey;
    }

    /**
     * Set the value of Setting Key
     *
     * @param mixed settingKey
     *
     * @return self
     */
    public function setSettingKey($settingKey)
    {
        $this->settingKey = $settingKey;

        return $this;
    }

    /**
     * Get the value of Setting Value
     *
     * @return mixed
     */
    public function getSettingValue()
    {
        return $this->settingValue;
    }

    /**
     * Set the value of Setting Value
     *
     * @param mixed settingValue
     *
     * @return self
     */
    public function setSettingValue($settingValue)
    {
        $this->settingValue = $settingValue;

        return $this;
    }

    /**
     * Get the value of Protected
     *
     * @return mixed
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * Set the value of Protected
     *
     * @param mixed protected
     *
     * @return self
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;

        return $this;
    }
}
