<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * App user, identified by appId generated in client app
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("appId")
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="appId", type="string", length=255, unique=true)
     * @Exclude
     * @Assert\NotBlank()
     */
    private $appId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastActive", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $lastActive;

    /**
     * @var string
     *
     * @ORM\Column(name="applePushToken", type="string", length=255, nullable=true)
     */
    private $applePushToken;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Wsh\LapiBundle\Entity\Alert", mappedBy="user")
     * @Exclude
     */
    private $alerts;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="bool")
     */
    private $sendHotDealsAlert;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->sendHotDealsAlert = true;
        $this->alerts = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set appId
     *
     * @param string $appId
     * @return User
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    
        return $this;
    }

    /**
     * Get appId
     *
     * @return string 
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    
        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string 
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastActive
     *
     * @param \DateTime $lastActive
     * @return User
     */
    public function setLastActive($lastActive)
    {
        $this->lastActive = $lastActive;
    
        return $this;
    }

    /**
     * Get lastActive
     *
     * @return \DateTime 
     */
    public function getLastActive()
    {
        return $this->lastActive;
    }

    /**
     * Set applePushToken
     *
     * @param string $applePushToken
     * @return User
     */
    public function setApplePushToken($applePushToken)
    {
        $this->applePushToken = $applePushToken;
    
        return $this;
    }

    /**
     * Get applePushToken
     *
     * @return string 
     */
    public function getApplePushToken()
    {
        return $this->applePushToken;
    }

    /**
     * @param boolean $sendHodDealsAlert
     */
    public function setSendHotDealsAlert($sendHodDealsAlert)
    {
        $this->sendHotDealsAlert = $sendHodDealsAlert;
    }

    /**
     * @return boolean
     */
    public function getSendHotDealsAlert()
    {
        return $this->sendHotDealsAlert;
    }

    /**
     * Enables hot deals alert for this user
     */
    public function enableHotDeals()
    {
        $this->setSendHotDealsAlert(true);
    }

    /**
     * Disables hot deals for this user
     */
    public function disableHotDeals()
    {
        $this->setSendHotDealsAlert(false);
    }

    /**
     * Returns string for security token based on AppIdToken
     */
    private function createSecurityToken($salt)
    {
        if(!$this->getAppId()) {
            throw new \Exception(
                'No appId given for user object thus no security token can be created. Set appId on object first')
            ;
        }
        // todo: change method of generating token
        $token = substr(sha1($this->getAppId().$salt), 5, 22);
        return $token;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {

    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $alerts
     */
    public function setAlerts($alerts)
    {
        $this->alerts = $alerts;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    public function addAlert(Alert $alert)
    {
        $this->alerts->add($alert);
    }

    public function checkSecurityToken($token, $salt)
    {
        return $this->createSecurityToken($salt) == $token;
    }
}
