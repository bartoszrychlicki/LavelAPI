<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Alert;
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
            throw new Exception('No wsh_lapi.users service registered');
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
    public function getAllOffersForAlert($alertId)
    {
        // fetches all offers that fits alert query
        $provider = $this->container->get('wsh_lapi.provider.qtravel');
        //get the alert
        $em = $this->getDoctrine()->getManager();
        $alertRepo = $em->getRepository('WshLapiBundle:Alert');
        $alert = $alertRepo->find($alertId);
        if(!$alert) {
            throw $this->createNotFoundException('No alert with id: '.$alertId.' found');
        }

        $response = $provider->findOffersByParams($alert->getSearchQueryParams());
        // now we can transport response to entity of offers
        $offers = $provider->transformToEntity($response);
        // todo: we must see if the offer exists in db update it with new data
        // we dont want to save each call for offer

        foreach($alert->getOffers() as $offer) {
            $em->remove($offer);
        }

        $em->flush();
        $alert->setOffers($offers);
        // flush the changes
        $em->persist($alert);
        $em->flush();

        return array(
            'offers' => $offers,
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
            throw new Exception('No wsh_lapi.users service registered');
        }
        return $user->getAlerts();
    }

}
