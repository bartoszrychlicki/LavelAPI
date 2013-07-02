<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * App user, identified by appId generated in client app
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="appId", type="string", length=255)
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
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastActive", type="datetime", nullable=true)
     */
    private $lastActive;

    /**
     * @var string
     *
     * @ORM\Column(name="applePushToken", type="string", length=255, nullable=true)
     */
    private $applePushToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $securityToken;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Wsh\LapiBundle\Entity\Alert", mappedBy="user")
     */
    private $alerts;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
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
     * @param string $securityToken
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;
    }

    /**
     * @return string
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * Returns string for security token based on AppIdToken
     */
    public function createSecurityToken()
    {
        if(!$this->getAppId()) {
            throw new \Exception(
                'No appId given for user object thus no security token can be created. Set appId on object first')
            ;
        }
        // todo: change method of generating token
        $salt = 'WeiserDawidek';
        $token = substr(sha1($this->getAppId().$salt), 0, 12);
        return $token;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setSecurityToken($this->createSecurityToken());
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

    public function checkSecurityToken($token)
    {
        return $this->getSecurityToken() == $token;
    }
}
