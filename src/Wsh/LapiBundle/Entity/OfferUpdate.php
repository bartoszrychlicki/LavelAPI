<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * OfferUpdate
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\Repository\OfferUpdateRepository")
 */
class OfferUpdate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="string")
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startedAt", type="datetime")
     */
    protected $startedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finishedAt", type="datetime",   nullable=true)
     */
    protected $finishedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="updatedOffers", type="integer", nullable=true)
     */
    protected $updatedOffers;

    /**
     * @var integer
     *
     * @ORM\Column(name="sentNotifications", type="integer", nullable=true)
     */
    protected $sentNotifications;

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
     * Set status
     *
     * @param string $status
     * @return OfferUpdate
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     * @return OfferUpdate
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;
    
        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime 
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt
     *
     * @param \DateTime $finishedAt
     * @return OfferUpdate
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;
    
        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return \DateTime 
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Set updatedOffers
     *
     * @param integer $updatedOffers
     * @return OfferUpdate
     */
    public function setUpdatedOffers($updatedOffers)
    {
        $this->updatedOffers = $updatedOffers;
    
        return $this;
    }

    /**
     * Get updatedOffers
     *
     * @return integer 
     */
    public function getUpdatedOffers()
    {
        return $this->updatedOffers;
    }

    /**
     * Set sentNotifications
     *
     * @param integer $sentNotifications
     * @return OfferUpdate
     */
    public function setSentNotifications($sentNotifications)
    {
        $this->sentNotifications = $sentNotifications;

        return $this;
    }

    /**
     * Get sentNotifications
     *
     * @return integer
     */
    public function getSentNotifications()
    {
        return $this->sentNotifications;
    }
}