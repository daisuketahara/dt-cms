<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\UserNoteRepository")
*/
class UserNote
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="text")
    */
    protected $note;

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
    * Get the value of Note
    *
    * @return mixed
    */
    public function getNote()
    {
        return $this->note;
    }

    /**
    * Set the value of Note
    *
    * @param mixed note
    *
    * @return self
    */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }
}
