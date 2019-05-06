<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\TranslationTextRepository")
*/
class TranslationText
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Translation",cascade={"persist"})
    * @ORM\JoinColumn(onDelete="CASCADE")
    */
    private $translation;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
    */
    protected $locale;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $text;

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

    /**
     * Get the value of Locale
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the value of Locale
     *
     * @param mixed locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the value of Text
     *
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the value of Text
     *
     * @param mixed text
     *
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
