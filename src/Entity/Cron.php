<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="App\Repository\CronRepository")
*/
class Cron
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="string", length=255, unique=true)
    */
    protected $name;

    /**
    * @ORM\Column(type="string", length=255, unique=true)
    */
    protected $script;

    /**
    * @ORM\Column(type="string", length=10)
    */
    protected $minute;

    /**
    * @ORM\Column(type="string", length=10)
    */
    protected $hour;

    /**
    * @ORM\Column(type="string", length=10)
    */
    protected $day;

    /**
    * @ORM\Column(type="string", length=10)
    */
    protected $month;

    /**
    * @ORM\Column(type="string", length=10)
    */
    protected $dayOfWeek;

    /**
    * @ORM\Column(type="datetime", nullable=true)
    */
    protected $lastRun;

    /**
    * @ORM\Column(type="datetime", nullable=true)
    */
    protected $nextRun;

    /**
    * @ORM\Column(type="integer")
    */
    protected $runCount = 0;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $protected = false;

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
    * Get the value of Script
    *
    * @return mixed
    */
    public function getScript()
    {
        return $this->script;
    }

    /**
    * Set the value of Script
    *
    * @param mixed script
    *
    * @return self
    */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
    * Get the value of Minute
    *
    * @return mixed
    */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
    * Set the value of Minute
    *
    * @param mixed minute
    *
    * @return self
    */
    public function setMinute($minute)
    {
        $this->minute = $minute;

        return $this;
    }

    /**
    * Get the value of Hour
    *
    * @return mixed
    */
    public function getHour()
    {
        return $this->hour;
    }

    /**
    * Set the value of Hour
    *
    * @param mixed hour
    *
    * @return self
    */
    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    /**
    * Get the value of Day
    *
    * @return mixed
    */
    public function getDay()
    {
        return $this->day;
    }

    /**
    * Set the value of Day
    *
    * @param mixed day
    *
    * @return self
    */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
    * Get the value of Month
    *
    * @return mixed
    */
    public function getMonth()
    {
        return $this->month;
    }

    /**
    * Set the value of Month
    *
    * @param mixed month
    *
    * @return self
    */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
    * Get the value of Day Of Week
    *
    * @return mixed
    */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
    * Set the value of Day Of Week
    *
    * @param mixed dayOfWeek
    *
    * @return self
    */
    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
    * Get the value of Last Run
    *
    * @return mixed
    */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
    * Set the value of Last Run
    *
    * @param mixed lastRun
    *
    * @return self
    */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    /**
    * Get the value of Next Run
    *
    * @return mixed
    */
    public function getNextRun()
    {
        return $this->nextRun;
    }

    /**
    * Set the value of Next Run
    *
    * @param mixed nextRun
    *
    * @return self
    */
    public function setNextRun($nextRun)
    {
        $this->nextRun = $nextRun;

        return $this;
    }

    /**
    * Get the value of Run Count
    *
    * @return mixed
    */
    public function getRunCount()
    {
        return $this->runCount;
    }

    /**
    * Set the value of Run Count
    *
    * @param mixed runCount
    *
    * @return self
    */
    public function setRunCount($runCount)
    {
        $this->runCount = $runCount;

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
