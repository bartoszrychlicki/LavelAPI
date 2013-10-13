<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;


/**
 * Alert
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\AlertRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("none")
 */
class Alert
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
     * @var integer
     *
     * @ORM\Column(name="newOffersCount", type="integer", nullable=true)
     */
    private $newOffersCount;

    /**
     * @var stdObject
     *
     * @ORM\Column(name="searchQueryParams", type="object")
     * @Expose
     * @Type("array")
     */
    private $searchQueryParams;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \Wsh\LapiBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Wsh\LapiBundle\Entity\User", inversedBy="alerts")
     * @Exclude
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfPages;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date")
     */
    private $lastNotificationDate;

    /**
     * @var ArrayCollection Already offers downloaded from API
     * @ORM\ManyToMany(targetEntity="Wsh\LapiBundle\Entity\Offer", cascade={"persist"})
     */
    private $offers;

    /**
     * @ORM\OneToMany(targetEntity="OfferReadStatus", mappedBy="alert_id", cascade={"all"})
     * @Exclude
     */
    protected $readStatus;

    public function __construct()
    {
        $this->created = new \DateTime();
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
     * Set newOffersCount
     *
     * @param integer $newOffersCount
     * @return Alert
     */
    public function setNewOffersCount($newOffersCount)
    {
        $this->newOffersCount = $newOffersCount;
    
        return $this;
    }

    /**
     * Get newOffersCount
     *
     * @return integer 
     */
    public function getNewOffersCount()
    {
        return $this->newOffersCount;
    }

    /**
     * Set searchQueryParams
     *
     * @param array $searchQueryParams
     * @return Alert
     */
    public function setSearchQueryParams($searchQueryParams)
    {
        $this->searchQueryParams = $searchQueryParams;
    
        return $this;
    }

    /**
     * Get searchQueryParams
     *
     * @return array 
     */
    public function getSearchQueryParams()
    {
        return $this->searchQueryParams;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Alert
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
     * Set user
     *
     * @param \stdClass $user
     * @return Alert
     */
    public function setUser($user)
    {
        $this->user = $user;
        $user->addAlert($this);

        return $this;
    }

    /**
     * Get user
     *
     * @return \stdClass 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param integer $numberOfPages
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
    }

    /**
     * @return integer
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * @param \Wsh\LapiBundle\Entity\ArrayCollection $offers
     */
    public function setOffers($offers)
    {
        $this->offers = $offers;
    }

    /**
     * @return \Wsh\LapiBundle\Entity\ArrayCollection
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Add offers
     *
     * @param \Wsh\LapiBundle\Entity\Offer $offers
     * @return Alert
     */
    public function addOffer(\Wsh\LapiBundle\Entity\Offer $offers)
    {
        $this->offers[] = $offers;
    
        return $this;
    }

    /**
     * Remove offers
     *
     * @param \Wsh\LapiBundle\Entity\Offer $offers
     */
    public function removeOffer(\Wsh\LapiBundle\Entity\Offer $offers)
    {
        $this->offers->removeElement($offers);
    }

    /**
     * Add readStatus
     *
     * @param \Wsh\LapiBundle\Entity\OfferReadStatus $readStatus
     * @return Alert
     */
    public function addReadStatus(\Wsh\LapiBundle\Entity\OfferReadStatus $readStatus)
    {
        $this->readStatus[] = $readStatus;

        return $this;
    }

    /**
     * Remove readStatus
     *
     * @param \Wsh\LapiBundle\Entity\OfferReadStatus $readStatus
     */
    public function removeReadStatus(\Wsh\LapiBundle\Entity\OfferReadStatus $readStatus)
    {
        $this->readStatus->removeElement($readStatus);
    }

    /**
     * Get readStatus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReadStatus()
    {
        return $this->readStatus;
    }

    /**
     * Set lastNotificationDate
     *
     * @param \DateTime $lastNotificationDate
     * @return Alert
     */
    public function setLastNotificationDate($lastNotificationDate)
    {
        $this->lastNotificationDate = $lastNotificationDate;
    
        return $this;
    }

    /**
     * Get lastNotificationDate
     *
     * @return \DateTime 
     */
    public function getLastNotificationDate()
    {
        return $this->lastNotificationDate;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setLastNotificationDate(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setLastNotificationDate(new \DateTime());
    }
}