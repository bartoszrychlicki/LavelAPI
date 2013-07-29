<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Lead;
use Wsh\LapiBundle\Entity\User;


/**
 * Class UserController
 *
 * Controlls data related to users, such as adding new user or removing it
 *
 * @package Wsh\LapiBundle\Controller
 */
class UserController extends Controller
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register new user or return existing one based on given AppId token
     * @param $appIdToken unique user token generated in client app
     * @return User
     */
    public function registerDevice($appId, $developerToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $validator = $this->container->get('validator');

        $user = new User();
        $user->setAppId($appId);

        // we must check the developer token

        if($this->container->getParameter('developerToken') != $developerToken) {
            throw new \Exception('Developer token is not correct. Request not authorized.');
        }
        // lets validate user
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $errors;
        } else {
            // if no errors go ahead and save user
        }
        $em->persist($user);
        $em->flush();

        $salt = $this->container->getParameter('secret');

        return array(
            'user' => $user,
            'securityToken' => $user->createSecurityToken($salt)
        );

    }

    /**
     * Removes user from database so he's token will no longer be authenticated
     *
     * @param $appIdToken
     * @param $securityToken
     * @return string
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function unRegisterDevice($appId, $securityToken)
    {
        $user = $this->getAppUser($appId, $securityToken);
        $em = $this->getDoctrine()->getManager();

        $em->remove($user);
        $em->flush();
        return "OK";
    }

    /**
     * Finds given user in DB by appId and check if request is authorized
     *
     * @param $appId
     * @param $securityToken
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     */
    public function getAppUser($appId, $securityToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('WshLapiBundle:User');
        $user = $repo->findOneByAppId($appId);
        $salt = $this->container->getParameter('secret');
        if(!$user) {
            throw $this->createNotFoundException('No user with AppId: '.$appId.' has been found');
        }
        // check security token
        if(!$user->checkSecurityToken($securityToken, $salt)) {
            throw new \Exception('Request not authorized. Tokens does not match');
        }
        return $user;
    }

    /**
     * Register new sells lead
     *
     * @param $appId
     * @param $params
     * @param $securityToken
     * @return Lead
     */
    public function registerSalesLead($appId, $params, $securityToken)
    {
        $user = $this->getAppUser($appId, $securityToken);

        $lead = new Lead();
        $lead->setUser($user);

        $lead->populateFromObject($params);

        $validator = $this->container->get('validator');
        $em = $this->getDoctrine()->getManager();

        // lets validate
        $errors = $validator->validate($lead);
        if (count($errors) > 0) {
            return $errors;
        }
        $em->persist($lead);
        $em->flush();

        // todo: now send the lead to qtravel e-mail
        $this->container->getParameter('sent_sales_leads_to')
        return $lead;
    }

    /**
     * User edit function
     *
     * @param $appId
     * @param \stdClass $newData
     * @param $securityToken
     * @return mixed
     */
    public function updateUser($appId, \stdClass $newData, $securityToken)
    {
        $validator = $this->container->get('validator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->getAppUser($appId, $securityToken);
        $user->populateFromObject($newData);
        // lets validate user
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $errors;
        }
        $em->persist($user);
        $em->flush();
        return $user;

    }
}