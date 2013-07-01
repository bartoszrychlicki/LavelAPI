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
    protected  $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register new user or return existing one based on given AppId token
     * @param $appIdToken unique user token generated in client app
     * @return User
     */
    public function registerDevice($appIdToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('WshLapiBundle:User');
        $user = $repo->findOneByAppId($appIdToken);
        if(!$user) {
            // user not found create new one
            $user = new User();
            $user->setAppId($appIdToken);
            $em->persist($user);
            $em->flush();
        }
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
    public function unRegisterDevice($appIdToken, $securityToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('WshLapiBundle:User');
        $user = $repo->findOneByAppId($appIdToken);
        if(!$user) {
            throw $this->createNotFoundException('No user with appIdToken: '.$appIdToken.' has been found');
        }
        // check security token
        $this->checkSecurityToken($user, $securityToken);

        $em->remove($user);
        $em->flush();
        return "OK";
    }

    private function checkSecurityToken(User $user, $token)
    {
        if($user->getSecurityToken() != $token) {
            throw new \Exception('Request not authorized. Tokens does not match');
        }
    }
}
