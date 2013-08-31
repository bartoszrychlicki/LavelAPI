<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Lead
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\LeadRepository")
 */
class Lead
{
    use ApiEntityTrait;


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
     * @ORM\Column(name="phoneNumber", type="string", length=50)
     */
    private $phoneNumber;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var \Wsh\LapiBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Wsh\LapiBundle\Entity\User", inversedBy="leads")
     * @Exclude()
     */
    private $user;

    /**
     * @var \stdClass
     * @Assert\NotBlank()
     * @ORM\Column(name="offerProviderSymbol", type="string", length=50)
     */
    private $offerProviderSymbol;

    /**
     * @param \Wsh\LapiBundle\Entity\Offer $offer
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
    }

    /**
     * @return \Wsh\LapiBundle\Entity\Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @var \Wsh\LapiBundle\Entity\Offer
     * @ORM\ManyToOne(targetEntity="\Wsh\LapiBundle\Entity\Offer")
     * @Exclude
     */
    private $offer;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Lead
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    
        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string 
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Lead
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * Set user
     *
     * @param \stdClass $user
     * @return Lead
     */
    public function setUser($user)
    {
        $this->user = $user;
    
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
     * Set offerProviderSymbol
     *
     * @param \stdClass $offer
     * @return Lead
     */
    public function setOfferProviderSymbol($offer)
    {
        $this->offerProviderSymbol = $offer;
    
        return $this;
    }

    /**
     * Get offerProviderSymbol
     *
     * @return \stdClass 
     */
    public function getOfferProviderSymbol()
    {
        return $this->offerProviderSymbol;
    }
}
