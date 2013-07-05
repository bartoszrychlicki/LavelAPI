<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
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
    public function registerDevice($appId, $securityToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $validator = $this->container->get('validator');

        $user = new User();
        $user->setAppId($appId);
        // now we can see if generate securityToken is the same as the one we will generate
        if(!$user->checkSecurityToken($securityToken, $this->container->getParameter('secret'))) {
            throw new \Exception('Request not authorized. Tokens does not match');
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

        return $user;

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
}