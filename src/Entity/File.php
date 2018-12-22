<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FileGroup")
     */
    protected $fileGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $filePath;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    protected $fileSize;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $fileType;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

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
     * Get the value of File Group
     *
     * @return mixed
     */
    public function getFileGroup()
    {
        return $this->fileGroup;
    }

    /**
     * Set the value of File Group
     *
     * @param mixed fileGroup
     *
     * @return self
     */
    public function setFileGroup($fileGroup)
    {
        $this->fileGroup = $fileGroup;

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
     * Set the value of User Id
     *
     * @param mixed userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of Name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name
     *
     * @param mixed name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of File Name
     *
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the value of File Name
     *
     * @param mixed fileName
     *
     * @return self
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get the value of File Path
     *
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set the value of File Path
     *
     * @param mixed filePath
     *
     * @return self
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get the value of File Size
     *
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set the value of File Size
     *
     * @param mixed fileSize
     *
     * @return self
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get the value of File Type
     *
     * @return mixed
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set the value of File Type
     *
     * @param mixed fileType
     *
     * @return self
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get the value of Hidden
     *
     * @return mixed
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the value of Hidden
     *
     * @param mixed hidden
     *
     * @return self
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

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
