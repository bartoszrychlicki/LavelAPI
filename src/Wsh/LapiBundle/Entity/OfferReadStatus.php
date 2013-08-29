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
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\OfferReadStatusRepository")
 * @ExclusionPolicy("none")
 */
class OfferReadStatus
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
     * @ORM\ManyToOne(targetEntity="Alert", inversedBy="alert_id", cascade={"all"})
     * @ORM\JoinColumn(name="alert_id", referencedColumnName="id")
     * @Exclude
     */
    protected $alert_id;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="offer_id", cascade={"persist"})
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id")
     * @Exclude
     */
    protected $offer_id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user_id")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Exclude
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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
     * Set alert_id
     *
     * @param \Wsh\LapiBundle\Entity\Alert $alertId
     * @return OfferReadStatus
     */
    public function setAlertId(\Wsh\LapiBundle\Entity\Alert $alertId = null)
    {
        $this->alert_id = $alertId;
    
        return $this;
    }

    /**
     * Get alert_id
     *
     * @return \Wsh\LapiBundle\Entity\Alert 
     */
    public function getAlertId()
    {
        return $this->alert_id;
    }

    /**
     * Set offer_id
     *
     * @param \Wsh\LapiBundle\Entity\Offer $offerId
     * @return OfferReadStatus
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
     * @return OfferReadStatus
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

    /**
     * Set status
     *
     * @param integer $status
     * @return OfferReadStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}