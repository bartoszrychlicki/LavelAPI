<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Alert;

class AlertController extends Controller
{
    protected $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postAlert($appId, $securityToken, $searchParams)
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
}
