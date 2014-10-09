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

use Wsh\LapiBundle\OfferProvider\OfferProviderInterface;
use Wsh\LapiBundle\Entity\Offer;
use Wsh\LapiBundle\Entity\OfferReadStatus;
use Wsh\LapiBundle\Entity\User;
use Wsh\LapiBundle\Entity\Alert;

class Provider implements OfferProviderInterface
{

    protected $container;
    protected $apiGetRequestUrl;
    protected $lastSentRequestUrl;
    protected $apiOfferRequestUrl;
    protected $buzz;
    protected $doctrine;

    function __construct(Container $container)
    {
        $this->container = $container;
        $this->buzz = $this->container->get('buzz.browser');
        $this->doctrine = $container->get("doctrine")->getManager();
        $apiKey = $this->container->getParameter('qtravelApiKey');
        $apiRequestUrl = 'http://api.qtravel.pl/json/apis?qapikey='.$apiKey;

        $this->apiOfferRequestUrl = 'http://api.qtravel.pl/json/apio?qapikey='.$apiKey;
        $this->apiGetRequestUrl = $apiRequestUrl;

    }

    public function findOfferById($id)
    {
        $url = $this->apiOfferRequestUrl.'&o_code='.$id.'&phsize=500x500';
        return $this->sendRequest($url);
    }

    public function findOfferByName($name)
    {
        // by name we mean the search query
        $url = $this->apiGetRequestUrl.'&query='.$name.'&phsize=500x500';
        return $this->sendRequest($url);
    }

    public function findOffersByParams($params)
    {
        $urlQueryParams = "";
        if(!array_key_exists('query', $params)) {
            if(!array_key_exists('empty_q', $params) || $params->empty_q == 'f') {
                throw new \Exception('Search params should atleast have "query" param, or "empty_q" param set to "t"');
            }
        }
        foreach($params as $key => $value)
        {
            $urlQueryParams .= '&'.$key.'='.$value;
        }

        $urlQueryParams .= '&phsize=500x500';

        return $this->sendRequest($this->apiGetRequestUrl.$urlQueryParams);

    }

    public function getProviderName()
    {
        return "QTravel API";
    }

    public function sendRequest($url)
    {
        $response = $this->buzz->get($url);
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

    public function handleOfferResponse($json) {
        $offerRepo = $this->doctrine->getRepository('WshLapiBundle:Offer');

        $i=1;

        $collection = new ArrayCollection();
        foreach($json->offers->o as $offer) {
            $checkSum = md5(serialize($offer));
            $offerDB = $offerRepo->findOneByQTravelOfferId($offer->o_details->o_code);

            if($offerDB){
                if($offerDB->getCheckSum() != $checkSum){
                    $collection->set(200 + $i, $this->transformToEntity($offer, $offerDB));
                } else {
                    $collection->set(100 + $i, $offerDB);
                }
            } else {
                $collection->set($i, $this->transformToEntity($offer));
            }
            $i++;
        }
        $this->doctrine->flush();

        return $collection;
    }

    public function transformToEntity($offer, &$offerDB = null)
    {
        if($offerDB == null) {
            $offerEnt = new Offer();
        } else {
            $offerEnt = $offerDB;
        }
        $offerEnt->setName(strip_tags($offer->o_details->o_name));
        if(!empty($offer->o_details->o_city)) {
            $offerEnt->setCity(strip_tags($offer->o_details->o_city));
        }

        if(!empty($offer->o_details->o_country)) {
            $offerEnt->setCountry(strip_tags($offer->o_details->o_country));
        }

        if(!empty($offer->o_details->o_desc)) {
            $offerEnt->setDescription(strip_tags($offer->o_details->o_desc));
        }

        if(!empty($offer->o_photos)) {
            $offerEnt->setLeadPhoto($offer->o_photos->o_photo_link[0]);
        }

        if (empty($offer->o_details->o_bprice) || $offer->o_details->o_bprice instanceof stdClass) {
			$offerEnt->setPrice(0);
		} else {
        	$offerEnt->setPrice($offer->o_details->o_bprice);
		}
		
        $offerEnt->setQTravelOfferId($offer->o_details->o_code);

        if($offerDB == null) {
            $offerEnt->setIsHotDeal(false);
            $offerEnt->setIsFeatured(false);
        }

        if(count($offer->o_best->o_b_period) == 1) {
            $offerEnt->setDuration(array($offer->o_best->o_b_period));
        } else {
            $offerEnt->setDuration($offer->o_best->o_b_period);
        }

        if(!empty($offer->o_details->o_departs)) {
            if(count($offer->o_details->o_departs->o_depart) == 1) {
                $offerEnt->setDeparts(array($offer->o_details->o_departs->o_depart));
            } else {
                $offerEnt->setDeparts($offer->o_details->o_departs->o_depart);
            }
        }

        if(!empty($offer->o_details->o_region)) {
            $offerEnt->setRegion(strip_tags($offer->o_details->o_region));
        }

        if(!empty($offer->o_details->o_hcat)) {
            $offerEnt->setStars($offer->o_details->o_hcat);
        }

        if(!empty($offer->o_best->o_b_datefr)) {
            $offerEnt->setTermFrom(new \DateTime($offer->o_best->o_b_datefr));
        }

        if(!empty($offer->o_best->o_b_dateto)) {
            $offerEnt->setTermTo(new \DateTime($offer->o_best->o_b_dateto));
        }

        if(!empty($offer->o_details->o_currency)) {
            $offerEnt->setCurrency($offer->o_details->o_currency);
        }

        if(!empty($offer->o_details->o_maintedescs)) {
            if(count($offer->o_details->o_maintedescs->o_maintedesc) == 1) {
                $offerEnt->setMaintenance(array($offer->o_details->o_maintedescs->o_maintedesc));
            } else {
                $offerEnt->setMaintenance($offer->o_details->o_maintedescs->o_maintedesc);
            }
        }

        if(!empty($offer->o_details->o_maintes)) {
            if(count($offer->o_details->o_maintes->o_mainte) == 1) {
                $offerEnt->setMaintenanceShort(array($offer->o_details->o_maintes->o_mainte));
            } else {
                $offerEnt->setMaintenanceShort($offer->o_details->o_maintes->o_mainte);
            }
        }

        if(!empty($offer->o_details->o_link)) {
            $offerEnt->setLink($offer->o_details->o_link);
        }

        if(!empty($offer->o_photos)) {
            if(count($offer->o_photos->o_photo_link) == 1) {
                $offerEnt->setPhotos(array($offer->o_photos->o_photo_link));
            } else {
                $offerEnt->setPhotos($offer->o_photos->o_photo_link);
            }
        }

        /**
         * checkSum will be used to check changes in offer
         */
        $offerEnt->setCheckSum(md5(serialize($offer)));

        return $offerEnt;
    }

    public function transformSingleOfferToEntity($response, $isFeatured = false, $isHotDeal = false) {
        $json = json_decode($response, true);

        if(!($json["offer"])) {
            throw new \Exception('Response does not have offer added to JSON object in '.__FUNCTION__);
        }
        if(count($json["offer"]) <= 0) {
            throw new \Exception('Array with offer on JSON response object is empty in '.__FUNCTION__);
        }

        $offer = new Offer();

        $offer->setQTravelOfferId($json["offer"]["o_code"]);

        $offer->setIsFeatured($isFeatured);
        $offer->setIsHotDeal($isHotDeal);
        $offer->setName(strip_tags($json["offer"]["o_name"]));

        if(!empty($json["offer"]["o_desc"])) {
            $offer->setDescription(strip_tags($json["offer"]["o_desc"]));
        }

        if(!empty($json["offer"]["o_photos"])) {
            $offer->setLeadPhoto($json["offer"]["o_photos"]["o_photo"][0]["@attributes"]["url"]);

            $photos = array();

            for($i = 0; $i < count($json["offer"]["o_photos"]["o_photo"]); $i++) {
                $photos[$i] = $json["offer"]["o_photos"]["o_photo"][$i]["@attributes"]["url"];
            }

            $offer->setPhotos($photos);
        }

        $offer->setPrice($json["offer"]["o_bprice"]);

        if(!empty($json["offer"]["o_region"])) {
            $offer->setRegion($json["offer"]["o_region"]);
        }

        if(!empty($json["offer"]["o_country"])) {
            $offer->setCountry(strip_tags($json["offer"]["o_country"]));
        }

        if(!empty($json["offer"]["o_hcat"])) {
            $offer->setStars($json["offer"]["o_hcat"]);
        }
        if(!empty($json["offer"]["o_cities"])) {
            $offer->setCity($json["offer"]["o_cities"]);
        }

        if(count($json["offer"]["o_periods"]["o_period"]) == 1) {
            $offer->setDuration(array($json["offer"]["o_periods"]["o_period"]));
        } else {
            $offer->setDuration($json["offer"]["o_periods"]["o_period"]);
        }

        if(!empty($json["offer"]["o_departures"])) {
            if(count($json["offer"]["o_departures"]["o_departure"]) == 1) {
                $offer->setDeparts(array($json["offer"]["o_departures"]["o_departure"]));
            } else {
                $offer->setDeparts($json["offer"]["o_departures"]["o_departure"]);
            }
        }

        if(!empty($json["offer"]["o_umaintences"])) {
            $maintence = array();
            $maintenceShort = array();

            foreach ($json["offer"]["o_umaintences"]["o_umaintence"] as $value) {
                $maintence[] = $value["description"];
                $maintenceShort[] = $value["code"];
            }

            $offer->setMaintenance($maintence);
            $offer->setMaintenanceShort($maintenceShort);
        }

        if(!empty($json["offer"]["o_link"])) {
            $offer->setLink($json["offer"]["o_link"]);
        }


        $offer->setTermFrom(new \DateTime($json["trips"]["trip"][0]["t_datefrom"]));
        $offer->setTermTo(new \DateTime($json["trips"]["trip"][0]["t_dateto"]));

        $offer->setCurrency($json["offer"]["o_currency"]);

        $offer->setCheckSum('null');

        return $offer;
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
