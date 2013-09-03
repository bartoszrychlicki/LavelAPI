<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Wsh\LapiBundle\Entity\Offer;

/**
 * App user, identified by appId generated in client app
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("appId")
 */
class User
{
    use ApiEntityTrait;
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
     * @ORM\OneToMany(targetEntity="Wsh\LapiBundle\Entity\Alert", mappedBy="user", cascade={"all"})
     * @Exclude
     */
    private $alerts;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Wsh\LapiBundle\Entity\Lead", mappedBy="user", cascade={"persist"})
     * @Exclude
     */
    private $leads;

    /**
     * @ORM\OneToMany(targetEntity="OfferFav", mappedBy="user_id", cascade={"persist", "remove"})
     */
    private $favourites;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="bool")
     */
    private $sendHotDealsAlert;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="bool")
     */
    private $sendLastMinuteAlert;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->sendHotDealsAlert = true;
        $this->sendLastMinuteAlert = true;
        $this->alerts = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getAppId();
    }

    /**
     * @param mixed $leads
     */
    public function setLeads($leads)
    {
        $this->leads = $leads;
    }

    /**
     * @return mixed
     */
    public function getLeads()
    {
        return $this->leads;
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
    private function setCreated($created)
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
    public function createSecurityToken($salt)
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
     * @ORM\PreRemove
     */
    public function prePersist()
    {
        foreach($this->getLeads() as $lead) {
            $lead->setUser(null);
        }

        foreach($this->getFavourites() as $fav) {
            $fav->setUserId(null);
        }
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        // anonymise the lead

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

    /**
     * @param boolean $sendLastMinuteAlert
     */
    public function setSendLastMinuteAlert($sendLastMinuteAlert)
    {
        $this->sendLastMinuteAlert = $sendLastMinuteAlert;
    }

    /**
     * @return boolean
     */
    public function getSendLastMinuteAlert()
    {
        return $this->sendLastMinuteAlert;
    }

    /**
     * Remove alerts
     *
     * @param \Wsh\LapiBundle\Entity\Alert $alerts
     */
    public function removeAlert(\Wsh\LapiBundle\Entity\Alert $alerts)
    {
        $this->alerts->removeElement($alerts);
    }

    /**
     * Add leads
     *
     * @param \Wsh\LapiBundle\Entity\Lead $leads
     * @return User
     */
    public function addLead(\Wsh\LapiBundle\Entity\Lead $leads)
    {
        $this->leads[] = $leads;
    
        return $this;
    }

    /**
     * Remove leads
     *
     * @param \Wsh\LapiBundle\Entity\Lead $leads
     */
    public function removeLead(\Wsh\LapiBundle\Entity\Lead $leads)
    {
        $this->leads->removeElement($leads);
    }

    /**
     * Add favourites
     *
     * @param \Wsh\LapiBundle\Entity\OfferFav $favourites
     * @return User
     */
    public function addFavourite(\Wsh\LapiBundle\Entity\OfferFav $favourites)
    {
        $this->favourites[] = $favourites;
    
        return $this;
    }

    /**
     * Remove favourites
     *
     * @param \Wsh\LapiBundle\Entity\OfferFav $favourites
     */
    public function removeFavourite(\Wsh\LapiBundle\Entity\OfferFav $favourites)
    {
        $this->favourites->removeElement($favourites);
    }

    /**
     * Get favourites
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFavourites()
    {
        return $this->favourites;
    }
}