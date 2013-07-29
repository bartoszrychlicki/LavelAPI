<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Offer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\OfferRepository")
 * @UniqueEntity("qTravelOfferId")
 */
class Offer
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
     * @Assert\NotBlank()
     * @ORM\Column(name="qTravelOfferId", type="string", length=100)
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
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="leadPhoto", type="string", length=200)
     */
    private $leadPhoto;

    /**
     * @var float
     *
     * @ORM\Column(name="stars", type="float")
     */
    private $stars;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var array
     *
     * @ORM\Column(name="departs", type="array")
     */
    private $departs;


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
     * @param integer $duration
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
     * @return integer 
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
}
