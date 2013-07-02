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

    public function postAlert($appIdToken, $securityToken, $searchParams)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        //$user = $this->container->get('wsh_lapi.users')->getUser($appIdToken, $securityToken);
        if($this->container->has('wsh_lapi.users')) {
            // this throws FatalErrorExecption
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appIdToken, $securityToken);
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
}
