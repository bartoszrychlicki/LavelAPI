<?php

namespace Wsh\LapiBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Alert;
use Wsh\LapiBundle\Entity\OfferReadStatus;
use Wsh\LapiBundle\Entity\User;
use Wsh\LapiBundle\OfferProvider\Qtravel\Provider;

class AlertController extends Controller
{
    protected $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postAlert($appId, $securityToken, $searchParams, $name)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }
        // check if that alert does not exist allready
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        // todo: do checking

        // create new alert object
        $alert = new Alert();
        $alert->setUser($user);
        $alert->setSearchQueryParams($searchParams);
        $alert->setName($name);

        $params = $searchParams;
        $params->page = 1;
        $params->sort = "c";

        $provider = $this->container->get('wsh_lapi.provider.qtravel');
        $response = $provider->findOffersByParams($params);
        $json = json_decode($response);
        $alert->setNumberOfPages($json->p->p_pages);
        $alert->setOffersTotal($json->p->p_offers);
        $alert->setOffersUnread(0);
        $alert->setOffersRead($json->p->p_offers);

        $em->persist($alert);
        $em->flush();

        return $alert;

    }

    public function deleteAlert($alertId, $securityToken)
    {
        $em = $this->getDoctrine()->getManager();
        //find alert
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        $alert = $alertRepo->find($alertId);
        if(!$alert) {
            throw $this->createNotFoundException('Alert with ID '.$alertId.' not found');
        }
        // check token
        $appIdToken = $alert->getUser()->getAppId();
        $userService = $this->container->get('wsh_lapi.users');

        $user = $userService->getAppUser($appIdToken, $securityToken);
        // remove alert from user
        $em->remove($alert);
        $em->flush();



        return "OK";
    }

    public function updateAlert($alertId, $newValues, $securityToken)
    {
        $em = $this->getDoctrine()->getManager();
        //find alert
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        $alert = $alertRepo->find($alertId);
        if(!$alert) {
            throw $this->createNotFoundException('Alert with ID '.$alertId.' not found');
        }
        // check token
        $appIdToken = $alert->getUser()->getAppId();
        $userService = $this->container->get('wsh_lapi.users');

        // this check token
        $user = $userService->getAppUser($appIdToken, $securityToken);

        // now pass new values to object
        if(count($newValues) > 0) {
            foreach($newValues as $key => $newValue) {
                // check if set metthod exists
                $method = 'set'.ucfirst($key);
                if(method_exists($alert, $method)) {
                    $alert->$method($newValue);
                }
            }
        }
        $alert->setNumberOfPages(null);


        $params = $newValues->searchQueryParams;
        $params->page = 1;
        $params->sort = "c";

        $provider = $this->container->get('wsh_lapi.provider.qtravel');
        $response = $provider->findOffersByParams($params);
        $json = json_decode($response);
        $alert->setNumberOfPages($json->p->p_pages);
        $alert->setOffersTotal($json->p->p_offers);
        $alert->setOffersUnread(0);
        $alert->setOffersRead($json->p->p_offers);

        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        foreach($alert->getOffers() as $offer){
            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offer->getId(),
                "alert_id" => $alertId
            ));
            $em->remove($offerReadStatus);
        }

        $alert->setOffers(null);
        $em->persist($alert);

        $em->flush();

        return "Alert ".$alertId." updated.";
    }

    /**
     * Returns all offers for given alert.
     *
     * @todo this probably should be somehow paginated
     *
     * @param $alertId
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getAllOffersForAlert($appId, $securityToken, $alertId, $page, $date = false)
    {
        set_time_limit(0);

        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $this->container->get('wsh_lapi.provider.qtravel');
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        /////////////////// CHECKING PARAMETERS ////////////////////////////////////////////////////////////
        $alert = $alertRepo->find($alertId);

        if(!$alert) {
            throw $this->createNotFoundException('No alert with id: '.$alertId.' found');
        }

        if($alert->getUser()->getId() != $user->getId()) {
            throw new \Exception("This alert don't belong to this user.");
        }

        $maxPage = $alert->getNumberOfPages();
        $params = $alert->getSearchQueryParams();
        $params->page = $page;
        $params->sort = "c";

        if($date) {
            $params->f_adate_min = $alert->getPreviousNotificationDate()->format('Y-m-d');
            $maxPage = $alert->getNumberOfPagesInUpdate();
        }

        $response = $provider->findOffersByParams($params);

        $json = json_decode($response);

        if(!empty($json)) {
            if(!($json->offers->o)) {
                throw new \Exception('Response does not have any offers added to JSON object in '.__FUNCTION__);
            }
            if(count($json->offers->o) <= 0) {
                throw new \Exception('Array with offers on JSON response object is empty in '.__FUNCTION__);
            }
            goto responseOk;
        }
        responseOk:

        if($maxPage === null ) {
            $alert->setNumberOfPages($json->p->p_pages);
            $alert->setOffersTotal($json->p->p_offers);
            $maxPage = $alert->getNumberOfPages();
        }

        $numberOfOffers = $alert->getOffersTotal();

        if($page == 0 || $page > $maxPage) {
            throw new \Exception("'page' parameter for this alert can't be bigger then ".$maxPage
                                    ." and lesser then 1");
        }
        ////////////////////////////////////////////////////////////////////////////////////////////



        $offerFromProvider = $provider->handleOfferResponse($json);
        $offerToSerialize = new ArrayCollection();

        $alertOffertsCount = count($alert->getOffers());

        foreach($offerFromProvider->getKeys() as $key) {

            if($key < 100) {
                $alert->addOffer($offerFromProvider->get($key));
            } else {
                if($alertOffertsCount == 0) {
                    $alert->addOffer($offerFromProvider->get($key));
                } else {
                    $exist = false;
                    foreach($alert->getOffers() as $offer) {
                        if($offer->getId() == $offerFromProvider->get($key)->getId()) {
                            $exist = true;
                        }
                    }
                    if(!$exist) {
                        $alert->addOffer($offerFromProvider->get($key));
                    }
                }
            }

            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offerFromProvider->get($key)->getId(),
                "alert_id" => $alertId
            ));

            if(!$offerReadStatus){
                $offerReadStatus = new OfferReadStatus();
                $offerReadStatus->setAlertId($alert);
                $offerReadStatus->setOfferId($offerFromProvider->get($key));
                $offerReadStatus->setIsRead(true);
                $offerReadStatus->setTempOfferId($offerFromProvider->get($key)->getId());

                $offerFromProvider->get($key)->addReadStatus($offerReadStatus);
            } /*else if ($offerReadStatus && $key < 200) {
                $offerFromProvider->get($key)->removeReadStatus($offerReadStatus);
                $offerReadStatus->setIsRead(true);
                $offerFromProvider->get($key)->addReadStatus($offerReadStatus);
            }*/

            $offerToSerialize->add($offerFromProvider->get($key));
        }

        $em->persist($alert);
        $em->flush();

        foreach($alert->getOffers() as $offer){
            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offer->getId(),
                "alert_id" => $alertId
            ));

            $offer->setReadStatus(null);
            $offer->setIsRead($offerReadStatus->getIsRead());
        }

        if($alert->getOffersTotal() != $json->p->p_offers) {
            $alert->setOffersTotal($json->p->p_offers);
            $alert->setOffersUnread($alert->getOffersTotal() - $alert->getOffersRead());
            $numberOfOffers = $alert->getOffersTotal();
            $em->persist($alert);
            $em->flush();
        }

        return array(
            'pages' => $maxPage,
            'numberOfOffers' => $numberOfOffers,
            'offers' => $offerToSerialize,
            'requestUrl' => $provider->getLastSentRequestUrl()
        );

    }

    public function getAllUpdatedOffersForAlert($appId, $securityToken, $alertId, $page)
    {
        $response = $this->getAllOffersForAlert($appId, $securityToken, $alertId, $page, true);

        return $response;
    }

    /**
     * Returns all new offers (that where not send via API before)
     */
    public function getNewOffersForAlert()
    {
        //todo
    }


    /**
     * The sames as getNewOffersForAlert but returns only count
     */
    public function getNewOffersForAlertCount()
    {
        //todo
    }

    /**
     * Returns array with user alerts
     *
     * @param $appId
     * @param $securityToken
     * @return mixed Collection of user alerts if any
     * @throws Exception When wsh_lapi.users service not found
     */
    public function getUserAlerts($appId, $securityToken)
    {
        // first let see if user not allready registered
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $em = $this->getDoctrine()->getManager();
        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        $amount = array(
            'totalOffersRead' => 0,
            'totalOffersUnread' => 0,
            'totalOffers' => 0
        );

        $alertORS = array();

        if(count($user->getAlerts()) != 0) {
            foreach($user->getAlerts() as $alert) {

                $alertORS[$alert->getId()] = array(
                    "offersRead" => 0,
                    "totalOffersInDb" => count($alert->getOffers())
                );

                if(count($alert->getOffers()) != 0) {
                    foreach($alert->getOffers() as $offer){
                        $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                            "offer_id" => $offer->getId(),
                            "alert_id" => $alert->getId(),
                        ));

                        $offer->setReadStatus(null);
                        $offer->setIsRead($offerReadStatus->getIsRead());

                        if($offerReadStatus->getIsRead()) {
                            $alertORS[$alert->getId()]['offersRead']++;
                        }
                    }
                }
                $alert->setOffersRead($alert->getOffersTotal() - ($alertORS[$alert->getId()]['totalOffersInDb'] - $alertORS[$alert->getId()]['offersRead']));
                $alert->setOffersUnread($alert->getOffersTotal() - $alert->getOffersRead());
                $em->persist($alert);

                $amount['totalOffersRead'] += $alert->getOffersRead();
                $amount['totalOffersUnread'] += $alert->getOffersUnread();
                $amount['totalOffers'] += $alert->getOffersTotal();

            }
        }

        $em->flush();

        return array(
            'amount' => $amount,
            'alerts' => $user->getAlerts()
        );
    }

    public function setReadStatus($appId, $securityToken, $alertId, $offerId)
    {
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $em = $this->getDoctrine()->getManager();

        $alert = $em->getRepository('WshLapiBundle:Alert')->findOneById($alertId);
        $offer = $em->getRepository('WshLapiBundle:Offer')->findOneById($offerId);

        if(!$alert) {
            throw new \Exception('No alert with id '.$alertId.' found');
        } elseif(!$offer) {
            throw new \Exception('No offer with id '.$offerId.' found');
        }

        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        $ors = $offerReadStatusRepo->findOneBy(array(
            "offer_id" => $offerId,
            "alert_id" => $alertId,
        ));

        if(!$ors) {
            throw new \Exception("In given alert(".$alertId."), offer with id ".$offerId." was not found.");
        }


        if (!$ors->getIsRead()) {
            $ors->setIsRead(true);
            $alert->setOffersRead($alert->getOffersRead() + 1);
            $alert->setOffersUnread($alert->getOffersUnread() - 1);
            $em->persist($ors);
            $em->persist($alert);
            $em->flush();
        }

        return 'Status changed';
    }

    public function getNumberOfPages($alertId)
    {
        if(!is_numeric($alertId)) {
            throw new \Exception('Alert id must be number.');
        }

        $em = $this->getDoctrine()->getManager();
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        $alert = $alertRepo->find($alertId);

        if(!$alert) {
            throw new \Exception('No alert with id '.$alertId.' found');
        }

        $provider = $this->container->get('wsh_lapi.provider.qtravel');

        $params = $alert->getSearchQueryParams();
        $offerFirstPage = $provider->findOffersByParams($params);
        $json = json_decode($offerFirstPage);

        if (!is_object($json)) {
            throw new \Exception('Error occurs while trying to connect to provider.');
        }

        $alert->setNumberOfPages($json->p->p_pages);

        $em->persist($alert);
        $em->flush();

        return array(
            'pages' => $alert->getNumberOfPages()
        );

    }

}
