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
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\OfferReadStatusRepository")
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
     * @ORM\ManyToOne(targetEntity="Alert", inversedBy="readStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="alert_id", referencedColumnName="id")
     * @Exclude
     */
    protected $alert_id;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="readStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id", nullable=true)
     * @Exclude
     */
    protected $offer_id;

    /**
     * @var bool
     *
     * @ORM\Column(name="isRead", type="boolean")
     */
    private $isRead;

    /**
     * @var integer
     *
     * @ORM\Column(name="temp_offer_id", type="string", length=100)
     * @Exclude
     */
    private $tempOfferId;

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
     * Set isRead
     *
     * @param bool $isRead
     * @return OfferReadStatus
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
     * Set tempOfferId
     *
     * @param string $tempOfferId
     * @return OfferReadStatus
     */
    public function setTempOfferId($tempOfferId)
    {
        $this->tempOfferId = $tempOfferId;

        return $this;
    }

    /**
     * Get tempOfferId
     *
     * @return string
     */
    public function getTempOfferId()
    {
        return $this->tempOfferId;
    }
}