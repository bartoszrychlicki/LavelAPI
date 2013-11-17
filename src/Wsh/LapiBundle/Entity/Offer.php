<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Exclude;


/**
 * Offer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\OfferRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("qTravelOfferId")
 */
class Offer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", length=100, unique=true)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="qTravelOfferId", type="string", length=100, unique=true)
     */
    private $qTravelOfferId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isFeatured", type="boolean")
     */
    private $isFeatured;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isHotDeal", type="boolean")
     */
    private $isHotDeal;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="leadPhoto", type="string", length=200, nullable=true)
     */
    private $leadPhoto;

    /**
     * @var array
     *
     * @ORM\Column(name="photos", type="array")
     */
    private $photos;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="termFrom", type="date", nullable=true)
     */
    private $termFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="termTo", type="date", nullable=true)
     */
    private $termTo;

    /**
     * @var array
     *
     * @ORM\Column(name="maintenance", type="array")
     */
    private $maintenance;

    /**
     * @var array
     *
     * @ORM\Column(name="maintenanceShort", type="array")
     */
    private $maintenanceShort;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string")
     */
    private $currency;

    /**
     * @var float
     *
     * @ORM\Column(name="stars", type="integer", nullable=true)
     */
    private $stars;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var array
     *
     * @ORM\Column(name="duration", type="array", nullable=true)
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var array
     *
     * @ORM\Column(name="departs", type="array", nullable=true)
     */
    private $departs;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="checkSum", type="string", length=32)
     */
    private $checkSum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdate", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $lastUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="addDate", type="datetime")
     */
    private $addDate;

    /**
     * @ORM\OneToMany(targetEntity="OfferReadStatus", mappedBy="offer_id", cascade={"persist"})
     */
    private $readStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string")
     */
    private $link;

    /**
     * @var bool
     *
     * @ORM\Column(name="isRead", type="boolean", nullable=true)
     */
    private $isRead;

    /**
     * @var bool
     *
     * @ORM\Column(name="isPriceLastUpdated", type="boolean")
     * @Exclude
     */
    private $isPriceLastUpdated;

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
     * Set qTravelOfferId
     *
     * @param string $qTravelOfferId
     * @return Offer
     */
    public function setQTravelOfferId($qTravelOfferId)
    {
        $this->qTravelOfferId = $qTravelOfferId;
        $this->id = $qTravelOfferId;
    
        return $this;
    }

    /**
     * Get qTravelOfferId
     *
     * @return string 
     */
    public function getQTravelOfferId()
    {
        return $this->qTravelOfferId;
    }

    /**
     * Set isFeatured
     *
     * @param boolean $isFeatured
     * @return Offer
     */
    public function setIsFeatured($isFeatured)
    {
        $this->isFeatured = $isFeatured;
    
        return $this;
    }

    /**
     * Get isFeatured
     *
     * @return boolean 
     */
    public function getIsFeatured()
    {
        return $this->isFeatured;
    }

    /**
     * Set isHotDeal
     *
     * @param boolean $isHotDeal
     * @return Offer
     */
    public function setIsHotDeal($isHotDeal)
    {
        $this->isHotDeal = $isHotDeal;
    
        return $this;
    }

    /**
     * Get isHotDeal
     *
     * @return boolean 
     */
    public function getIsHotDeal()
    {
        return $this->isHotDeal;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Offer
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Offer
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set leadPhoto
     *
     * @param string $leadPhoto
     * @return Offer
     */
    public function setLeadPhoto($leadPhoto)
    {
        $this->leadPhoto = $leadPhoto;
    
        return $this;
    }

    /**
     * Get leadPhoto
     *
     * @return string 
     */
    public function getLeadPhoto()
    {
        return $this->leadPhoto;
    }

    /**
     * Set stars
     *
     * @param float $stars
     * @return Offer
     */
    public function setStars($stars)
    {
        $this->stars = $stars;
    
        return $this;
    }

    /**
     * Get stars
     *
     * @return float 
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Offer
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set duration
     *
     * @param array $duration
     * @return Offer
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    
        return $this;
    }

    /**
     * Get duration
     *
     * @return array
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Offer
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Offer
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set departs
     *
     * @param array $departs
     * @return Offer
     */
    public function setDeparts($departs)
    {
        $this->departs = $departs;
    
        return $this;
    }

    /**
     * Get departs
     *
     * @return array 
     */
    public function getDeparts()
    {
        return $this->departs;
    }

    public function __toString()
    {
        return $this->getQTravelOfferId().' - '.$this->getName();
    }

    /**
     * Set checkSum
     *
     * @param string $checkSum
     * @return Offer
     */
    public function setCheckSum($checkSum)
    {
        $this->checkSum = $checkSum;
    
        return $this;
    }

    /**
     * Get checkSum
     *
     * @return string 
     */
    public function getCheckSum()
    {
        return $this->checkSum;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->readStatus = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return Offer
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set addDate
     *
     * @param \DateTime $addDate
     * @return Offer
     */
    public function setAddDate($addDate)
    {
        $this->addDate = $addDate;

        return $this;
    }

    /**
     * Get addDate
     *
     * @return \DateTime
     */
    public function getAddDate()
    {
        return $this->addDate;
    }
    
    /**
     * Add readStatus
     *
     * @param \Wsh\LapiBundle\Entity\OfferReadStatus $readStatus
     * @return Offer
     */
    public function addReadStatus(\Wsh\LapiBundle\Entity\OfferReadStatus $readStatus)
    {
        $this->readStatus[] = $readStatus;
    
        return $this;
    }

    public function setReadStatus($readStatus)
    {
        $this->readStatus = $readStatus;
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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setAddDate(new \DateTime());
        $this->setLastUpdate(new \DateTime());
        $this->setIsPriceLastUpdated(false);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setLastUpdate(new \DateTime());
    }

    /**
     * Set photos
     *
     * @param array $photos
     * @return Offer
     */
    public function setPhotos($photos)
    {
        $this->photos = $photos;
    
        return $this;
    }

    /**
     * Get photos
     *
     * @return array 
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Set termFrom
     *
     * @param \DateTime $termFrom
     * @return Offer
     */
    public function setTermFrom($termFrom)
    {
        $this->termFrom = $termFrom;
    
        return $this;
    }

    /**
     * Get termFrom
     *
     * @return \DateTime 
     */
    public function getTermFrom()
    {
        return $this->termFrom;
    }

    /**
     * Set termTo
     *
     * @param \DateTime $termTo
     * @return Offer
     */
    public function setTermTo($termTo)
    {
        $this->termTo = $termTo;
    
        return $this;
    }

    /**
     * Get termTo
     *
     * @return \DateTime 
     */
    public function getTermTo()
    {
        return $this->termTo;
    }

    /**
     * Set maintenance
     *
     * @param array $maintenance
     * @return Offer
     */
    public function setMaintenance($maintenance)
    {
        $this->maintenance = $maintenance;
    
        return $this;
    }

    /**
     * Get maintenance
     *
     * @return array 
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * Set maintenanceShort
     *
     * @param array $maintenanceShort
     * @return Offer
     */
    public function setMaintenanceShort($maintenanceShort)
    {
        $this->maintenanceShort = $maintenanceShort;

        return $this;
    }

    /**
     * Get maintenanceShort
     *
     * @return array
     */
    public function getMaintenanceShort()
    {
        return $this->maintenanceShort;
    }

    /**
     * Set currency
     *
     * @param array $currency
     * @return Offer
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return array 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Offer
     */
    public function setRegion($region)
    {
        $this->region = $region;
    
        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Offer
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set isRead
     *
     * @param bool $isRead
     * @return Offer
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return bool
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set isPriceLastUpdated
     *
     * @param bool $isPriceLastUpdated
     * @return Offer
     */
    public function setIsPriceLastUpdated($isPriceLastUpdated)
    {
        $this->isPriceLastUpdated = $isPriceLastUpdated;

        return $this;
    }

    /**
     * Get isPriceLastUpdated
     *
     * @return bool
     */
    public function getIsPriceLastUpdated()
    {
        return $this->isPriceLastUpdated;
    }
}