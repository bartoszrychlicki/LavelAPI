<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bard
 * Date: 02.07.2013
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wsh\LapiBundle\OfferProvider\Qtravel;


use Symfony\Component\DependencyInjection\Container;
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
        $apiRequestUrl = 'http://api.qtravel.pl/apis?qapikey='.$apiKey;
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

}