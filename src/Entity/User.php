<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Locale;

/**
* @ORM\Table(name="users")
* @ORM\Entity(repositoryClass="App\Repository\UserRepository")
*/
class User implements UserInterface
{

    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    * @Assert\NotBlank()
    */
    private $firstname;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $lastname;

    /**
    * @ORM\Column(type="string", length=1, nullable=true)
    */
    private $gender;

    /**
    * @ORM\Column(type="string", length=100, unique=true)
    */
    private $email;

    /**
    * @ORM\Column(type="string", length=64)
    * @Assert\NotBlank()
    */
    private $password;

    /**
    * @ORM\Column(type="string", length=60, nullable=true)
    */
    private $phone;

    /**
    * @ORM\Column(type="string", length=60, nullable=true)
    */
    private $mobile;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
    */
    protected $locale;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $settings;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\UserInformation", cascade={"persist", "remove"})
    */
    private $information;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\UserNote", cascade={"persist", "remove"})
    */
    private $note;

    /**
    * @ORM\ManyToMany(targetEntity="App\Entity\Permission")
    */
    protected $permissions;

    /**
    * @ORM\ManyToMany(targetEntity="App\Entity\Role")
    */
    protected $roles = [];

    /**
    * @ORM\Column(type="boolean")
    */
    private $emailConfirmed = false;

    /**
    * @ORM\Column(type="boolean")
    */
    private $phoneConfirmed = false;

    /**
    * @ORM\Column(type="string", length=60, unique=true, nullable=true)
    */
    private $confirmKey;

    /**
    * @ORM\Column(type="datetime")
    */
    protected $creationDate;

    /**
    * @ORM\Column(name="active", type="boolean")
    */
    private $active;

    public function __construct()
    {
        $this->active = true;
        $this->permissions = new ArrayCollection();
        $this->roles = new ArrayCollection();
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }

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
    * Get the value of Email
    *
    * @return mixed
    */
    public function getEmail()
    {
        return $this->email;
    }

    /**
    * Set the value of Email
    *
    * @param mixed email
    *
    * @return self
    */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
    * Get the value of Firstname
    *
    * @return mixed
    */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
    * Set the value of Firstname
    *
    * @param mixed firstname
    *
    * @return self
    */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
    * Get the value of Lastname
    *
    * @return mixed
    */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
    * Set the value of Lastname
    *
    * @param mixed lastname
    *
    * @return self
    */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of Gender
     *
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set the value of Gender
     *
     * @param mixed gender
     *
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
    * Get the value of Lastname
    *
    * @return mixed
    */
    public function getFullname()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
    * Get the value of Phone
    *
    * @return mixed
    */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
    * Set the value of Phone
    *
    * @param mixed phone
    *
    * @return self
    */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
    * Set the value of Mobile
    *
    * @param mixed mobile
    *
    * @return self
    */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
    * Set the value of Roles
    *
    * @param mixed roles
    *
    * @return self
    */
    public function setRoles($roles)
    {
        $this->roles = $roles;

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
    * Get the value of Email Confirmed
    *
    * @return mixed
    */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
    * Set the value of Email Confirmed
    *
    * @param mixed emailConfirmed
    *
    * @return self
    */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
    * Get the value of Phone Confirmed
    *
    * @return mixed
    */
    public function getPhoneConfirmed()
    {
        return $this->phoneConfirmed;
    }

    /**
    * Set the value of Phone Confirmed
    *
    * @param mixed phoneConfirmed
    *
    * @return self
    */
    public function setPhoneConfirmed($phoneConfirmed)
    {
        $this->phoneConfirmed = $phoneConfirmed;

        return $this;
    }

    /**
    * Get the value of Confirm Key
    *
    * @return mixed
    */
    public function getConfirmKey()
    {
        return $this->confirmKey;
    }

    /**
    * Set the value of Confirm Key
    *
    * @param mixed confirmKey
    *
    * @return self
    */
    public function setConfirmKey($confirmKey)
    {
        $this->confirmKey = $confirmKey;

        return $this;
    }

    /**
    * Get the value of Creation Date
    *
    * @return mixed
    */
    public function getCreationDate()
    {
        return $this->creationDate;
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

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }


    /**
    * Get the value of Password
    *
    * @return mixed
    */
    public function getPassword()
    {
        return $this->password;
    }

    /**
    * Set the value of Password
    *
    * @param mixed password
    *
    * @return self
    */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials()
    {
    }


    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->active;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            $this->active,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            $this->active,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

    /**
    * Get the value of Information
    *
    * @return mixed
    */
    public function getInformation()
    {
        return $this->information;
    }

    /**
    * Set the value of Information
    *
    * @param mixed information
    *
    * @return self
    */
    public function setInformation($information)
    {
        $this->information = $information;

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

    /**
    * Get the value of Permissions
    *
    * @return mixed
    */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
    * Set the value of Permissions
    *
    * @param mixed permissions
    *
    * @return self
    */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
    * @param Permission $permission
    */
    public function addPermission(Permission $permission)
    {
        if ($this->permissions->contains($permission)) {
            return;
        }
        $this->permissions->add($permission);
    }

    /**
    * @param Permission $permission
    */
    public function removePermission(Permission $permission)
    {
        if (!$this->permissions->contains($permission)) {
            return;
        }
        $this->permissions->removeElement($permission);
    }

    /**
    * Get the value of roles
    *
    * @return mixed
    */
    public function getRoles()
    {
        //return $this->roles;
        return array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN');
    }

    /**
    * Get the value of roles
    *
    * @return mixed
    */
    public function getUserRoles()
    {
        return $this->roles;
    }

    /**
    * @param Role $role
    */
    public function addRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            return;
        }
        $this->roles->add($role);
    }

    /**
    * @param Role $role
    */
    public function removeRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            return;
        }
        $this->roles->removeElement($role);
    }

    /**
    * Get the value of Mobile
    *
    * @return mixed
    */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
    * Get array of Settings
    *
    * @return mixed
    */
    public function getSettings()
    {
        if (!empty($this->settings)) return json_decode($this->settings);

        return false;
    }

    /**
    * Get the value of a Setting
    *
    * @return mixed
    */
    public function getSetting($key)
    {
        if (!empty($this->settings)) {
            $settings = json_decode($this->settings);

            if (array_key_exists($key, $settings)) return $settings[$key];
        }

        return false;
    }

    /**
    * Set the value of Setting
    *
    * @param mixed settings
    *
    * @return self
    */
    public function setSettings($key, $value)
    {
        $settings = json_decode($this->settings);
        $settings[$key] = $value;

        $this->settings = json_encode($settings);

        return $this;
    }
}
