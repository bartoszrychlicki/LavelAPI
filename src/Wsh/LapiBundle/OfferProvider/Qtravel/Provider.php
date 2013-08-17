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
    protected $apiOfferRequestUrl;

    function __construct(Container $container)
    {
        $this->container = $container;
        $apiKey = $this->container->getParameter('qtravelApiKey');
        $apiRequestUrl = 'http://api.qtravel.pl/json/apis?qapikey='.$apiKey;

        $this->apiOfferRequestUrl = 'http://api.qtravel.pl/json/apio?qapikey='.$apiKey;
        $this->apiGetRequestUrl = $apiRequestUrl;

    }

    public function findOfferById($id)
    {
        $url = $this->apiOfferRequestUrl.'&o_code='.$id;
        return $this->sendRequest($url);
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
        if(!array_key_exists('query', $params)) {
            throw new \Exception('Search params should atleast have "query" param');
        }
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
            $offerEnt = new Offer();
            $offerEnt->setName(strip_tags($offer->o_details->o_name));
            if(!empty($offer->o_details->o_city)) {
                $offerEnt->setCity(strip_tags($offer->o_details->o_city));
            }
            $offerEnt->setCountry(strip_tags($offer->o_details->o_country));
            if(!empty($offer->o_details->o_departs)) {
                $offerEnt->setDeparts($offer->o_details->o_departs->o_depart);
            }
            $offerEnt->setDescription(strip_tags($offer->o_details->o_desc));
            $offerEnt->setIsHotDeal(false);
            $offerEnt->setIsFeatured(false);
            $offerEnt->setLeadPhoto($offer->o_photos->o_photo_link[0]);
            $offerEnt->setPrice($offer->o_details->o_bprice);
            $offerEnt->setQTravelOfferId($offer->o_details->o_code);
            $offerEnt->setDuration($offer->o_best->o_b_period);

            $collection->add($offerEnt);
        }

        return $collection;
    }

    /**
     * Parses given url and resturns offer id if found
     *
     * @param $url string
     */
    public function parseUrl($url)
    {
        return preg_match('/\d{5}/', $url);
    }

}