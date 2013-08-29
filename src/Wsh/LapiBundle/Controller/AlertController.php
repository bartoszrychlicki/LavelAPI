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
            foreach($newValues as $key => $newValues) {
                // check if set metthod exists
                $method = 'set'.ucfirst($key);
                if(method_exists($alert, $method)) {
                    $alert->$method($newValues);
                }
            }
        }
        $em->persist($alert);
        $em->flush();

        return $alert;
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
    public function getAllOffersForAlert($appId, $securityToken, $alertId)
    {
        set_time_limit(0);

        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        if ($this->has('debug.stopwatch')) {
            $stopwatch = $this->get('debug.stopwatch');
        }

        // fetches all offers that fits alert query
        $provider = $this->container->get('wsh_lapi.provider.qtravel');
        //get the alert
        $em = $this->getDoctrine()->getManager();
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        //$alert = $alertRepo->find($alertId);

        $alert = $alertRepo->findOneBy(array(
            'id' => $alertId,
        ));

        if($alert->getUser()->getId() != $user->getId()) {
            throw new \Exception("This alert don't belong to this user.");
        }

        if(!$alert) {
            throw $this->createNotFoundException('No alert with id: '.$alertId.' found');
        }

        $params = $alert->getSearchQueryParams();

        // Fetch first page to get number of pages for given alert//
        $offerFirstPage = $provider->findOffersByParams($params);
        $json = json_decode($offerFirstPage);
        $pages =  $json->p->p_pages;
        //--------------------------------------------------------//

        $offers = new \Doctrine\Common\Collections\ArrayCollection();

        for($i = 1; $i <= $pages; $i++) {
            if($i == 1) {
                $response = $offerFirstPage;
            } else {
                $params->page = $i;
                $response = $provider->findOffersByParams($params);
            }

            $stopwatch->start('transformToEntity');
            $offer = $provider->handleOfferResponse($response);
            $stopwatch->stop('transformToEntity');

            if($offer != null){
                $offers = new \Doctrine\Common\Collections\ArrayCollection(
                    array_merge($offers->toArray(), $offer->toArray())
                );
            }
        }




        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        foreach($offers as $offer){
            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offer->getId(),
                "alert_id" => $alertId,
                "user_id" => $user->getId()
            ));

            if(!$offerReadStatus){
                $offerReadStatus = new OfferReadStatus();
                $offerReadStatus->setAlertId($alert);
                $offerReadStatus->setOfferId($offer);
                $offerReadStatus->setUserId($user);
                $offerReadStatus->setStatus(0);

                $offer->addReadStatus($offerReadStatus);
            }
        }

        $alert->setOffers($offers);
        $em->persist($alert);
        $em->flush();

        foreach($alert->getOffers() as $offer){
            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offer->getId(),
                "alert_id" => $alertId,
                "user_id" => $user->getId()
            ));

            $readStatus = new ArrayCollection();
            $readStatus->add($offerReadStatus);

            $offer->setReadStatus($readStatus);
        }

        return array(
            'offers' => $alert->getOffers(),
            'requestUrl' => $provider->getLastSentRequestUrl()
        );

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

        return $user->getAlerts();
    }

    public function setReadStatus($appId, $securityToken, $alertId, $offerId, $status)
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
        } elseif($status < 1 || $status > 2) {
            throw new \Exception('Status must be 1(downloaded - not readed), or 2(downloaded - readed)');
        }

        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        $offer = $offerReadStatusRepo->findOneBy(array(
            "offer_id" => $offerId,
            "alert_id" => $alertId,
        ));

        if(!$offer) {
            throw new \Exception("In given alert(".$alertId."), offer with id ".$offerId." was not found.");
        } elseif($offer->getUserId()->getId() != $user->getId()) {
            throw new \Exception("Given alert(".$alertId.") don't belong to this user");
        }

        $offer->setStatus($status);
        $em->persist($offer);
        $em->flush();

        return 'Status changed';

    }

}
