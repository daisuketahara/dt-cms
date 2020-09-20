<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
* @ORM\Entity(repositoryClass="App\Repository\TranslationTranslationRepository")
*/
class TranslationTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $translation;

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
     * Get the value of Translation
     *
     * @return mixed
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set the value of Translation
     *
     * @param mixed translation
     *
     * @return self
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }
}
