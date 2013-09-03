<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Handler\ArrayCollectionHandler;


/**
 * Alert
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\OfferFavRepository")
 * @ExclusionPolicy("none")
 */
class OfferFav
{
    /**
    * @var integer
    *
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @Exclude
    */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Offer")
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id", nullable=true)
     * @Exclude
     */
    protected $offer_id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user_id")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * @Exclude
     */
    protected $user_id;


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
     * Set offer_id
     *
     * @param \Wsh\LapiBundle\Entity\Offer $offerId
     * @return OfferFav
     */
    public function setOfferId(\Wsh\LapiBundle\Entity\Offer $offerId = null)
    {
        $this->offer_id = $offerId;
    
        return $this;
    }

    /**
     * Get offer_id
     *
     * @return \Wsh\LapiBundle\Entity\Offer 
     */
    public function getOfferId()
    {
        return $this->offer_id;
    }

    /**
     * Set user_id
     *
     * @param \Wsh\LapiBundle\Entity\User $userId
     * @return OfferFav
     */
    public function setUserId(\Wsh\LapiBundle\Entity\User $userId = null)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return \Wsh\LapiBundle\Entity\User 
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}