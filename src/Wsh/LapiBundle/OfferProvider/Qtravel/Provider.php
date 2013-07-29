<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bard
 * Date: 02.07.2013
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wsh\LapiBundle\OfferProvider\Qtravel;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Offer;
use Wsh\LapiBundle\OfferProvider\OfferProviderInterface;

class Provider implements OfferProviderInterface
{

    protected $container;
    protected $apiGetRequestUrl;
    protected $lastSentRequestUrl;

    function __construct(Container $container)
    {
        $this->container = $container;
        $apiKey = $this->container->getParameter('qtravelApiKey');
        $apiRequestUrl = 'http://api.qtravel.pl/json/apis?qapikey='.$apiKey;
        $this->apiGetRequestUrl = $apiRequestUrl;

    }

    public function findOfferById($id)
    {
        return true;
    }

    public function findOfferByName($name)
    {
        // by name we mean the search query
        $url = $this->apiGetRequestUrl.'&query='.$name;
        return $this->sendRequest($url);
    }

    public function findOffersByParams($params)
    {
        $urlQueryParams = "";
        foreach($params as $key => $value)
        {
            $urlQueryParams .= '&'.$key.'='.$value;
        }
        return $this->sendRequest($this->apiGetRequestUrl.$urlQueryParams);

    }

    public function getProviderName()
    {
        return "QTravel API";
    }

    protected function sendRequest($url)
    {
        $buzz = $this->container->get('buzz');
        $response = $buzz->get($url);
        $this->setLastSentRequestUrl($url);
        return $response->getContent();
    }

    /**
     * @param mixed $lastSentRequestUrl
     */
    public function setLastSentRequestUrl($lastSentRequestUrl)
    {
        $this->lastSentRequestUrl = $lastSentRequestUrl;
    }

    /**
     * @return mixed
     */
    public function getLastSentRequestUrl()
    {
        return $this->lastSentRequestUrl;
    }

    public function transformToEntity($response) {
        // decode JSON
        $json = json_decode($response);
        if(!($json->offers->o)) {
            throw new \Exception('Response does not have any offers added to JSON object in '.__FUNCTION__);
        }
        if(count($json->offers->o) <= 0) {
            throw new \Exception('Array with offers on JSON response object is empty in '.__FUNCTION__);
        }
        // now iterate over each
        $collection = new ArrayCollection();
        foreach($json->offers->o as $offer) {
            $offer = new Offer();
            $offer->setName($offer->o_details->o_name);
            $offer->setCity($offer->o_details->o_city);
            $offer->setCountry($offer->o_details->o_country);
            $offer->setDeparts($offer->o_details->o_departs->o_depart);
            $offer->setDescription(strip_tags($offer->o_details->o_desc));
            $offer->setIsHotDeal(false);
            $offer->setIsFeatured(false);
            $offer->setLeadPhoto($offer->o_photos->o_photo_link[0]);
            $offer->setPrice($offer->o_details->o_bprice);
            $offer->setQTravelOfferId($offer->o_details->o_code);
            $offer->setDuration($offer->o_best->o_b_period);

            $collection->add($offer);
        }

        return $collection;
    }

}