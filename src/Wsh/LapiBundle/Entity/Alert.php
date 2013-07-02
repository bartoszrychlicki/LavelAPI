<?php

namespace Wsh\LapiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;


/**
 * Alert
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Wsh\LapiBundle\Entity\AlertRepository")
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
     * @var array
     *
     * @ORM\Column(name="searchQueryParams", type="object")
     *
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
}