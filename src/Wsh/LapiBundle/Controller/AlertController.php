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
        $alert->setNumberOfPages(null);
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
    public function getAllOffersForAlert($appId, $securityToken, $alertId, $page)
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

        if(empty($maxPage) || $maxPage === null) {
            throw new \Exception('First get number of page with "get_number_of_pages_for_alert" method.');
        } elseif($page == 0 || $page > $maxPage) {
            throw new \Exception("'page' parameter for this alert can't be bigger then ".$maxPage
                                    ." and lesser then 1");
        }
        ////////////////////////////////////////////////////////////////////////////////////////////

        $params = $alert->getSearchQueryParams();

        $params->page = $page;
        $response = $provider->findOffersByParams($params);

        $offerFromProvider = $provider->handleOfferResponse($response);
        $offerToSerialize = new ArrayCollection();

        $amount = array(
            "status-0" => 0,
            "status-1" => 0,
            "status-2" => 0
        );

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
                $offerReadStatus->setStatus(0);
                $offerReadStatus->setTempOfferId($offerFromProvider->get($key)->getId());

                $offerFromProvider->get($key)->addReadStatus($offerReadStatus);

                $amount['status-0']++;
            } else {
                $amount['status-'.$offerReadStatus->getStatus()]++;
            }

            $offerToSerialize->add($offerFromProvider->get($key));
        }

        $em->persist($alert);
        $em->flush();

        foreach($alert->getOffers() as $offer){
            $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                "offer_id" => $offer->getId(),
                "alert_id" => $alertId
            ));

            $readStatus = new ArrayCollection();
            $readStatus->add($offerReadStatus);

            $offer->setReadStatus($readStatus);
        }

        return array(
            'amount' => $amount,
            'offers' => $offerToSerialize,
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

        $em = $this->getDoctrine()->getManager();
        $offerReadStatusRepo = $em->getRepository('WshLapiBundle:OfferReadStatus');

        if(count($user->getAlerts()) == 0) {
            throw new \Exception('This user has no alerts');
        }



        foreach($user->getAlerts() as $alert) {
            if(count($alert->getOffers()) != 0) {
                foreach($alert->getOffers() as $offer){
                    $offerReadStatus = $offerReadStatusRepo->findOneBy(array(
                        "offer_id" => $offer->getId(),
                        "alert_id" => $alert->getId(),
                    ));

                    $readStatus = new ArrayCollection();
                    $readStatus->add($offerReadStatus);

                    $offer->setReadStatus($readStatus);
                }
            }
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
        }

        $offer->setStatus($status);
        $em->persist($offer);
        $em->flush();

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

        if($json->p->p_pages != 0) {
            $alert->setNumberOfPages($json->p->p_pages);
        } else {
            throw new \Exception('No offers for this alert found.');
        }


        $em->persist($alert);
        $em->flush();

        return array(
            'pages' => $alert->getNumberOfPages()
        );

    }

}
