<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Wsh\LapiBundle\Entity\Lead;
use Wsh\LapiBundle\Entity\User;
use RMS\PushNotificationsBundle\Message\iOSMessage;


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
     * @param $appIdToken String unique user token generated in client app
     * @return User
     */
    public function registerDevice($appId, $developerToken)
    {
        // first let see if user not allready registered
        $em = $this->getDoctrine()->getManager();
        $validator = $this->container->get('validator');

        $user = new User();
        $userRepo = $em->getRepository('WshLapiBundle:User');

        if($userRepo->findOneBy(array('appId' => $appId))){
            throw new \Exception('This appId is already used.');
        }


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
    public function unRegisterDevice($appId, $developerToken)
    {
        if($this->container->getParameter('developerToken') != $developerToken) {
            throw new \Exception('Developer token is not correct. Request not authorized.');
        }

        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('WshLapiBundle:User');

        $user = $userRepo->findOneBy(array(
           'appId' => $appId
        ));

        if(!$user) {
            throw new \Exception('User with '.$appId.' was not found.');
        }


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
    public function registerSalesLead($appId, $arguments, $securityToken)
    {
        $user = $this->getAppUser($appId, $securityToken);
        $em = $this->getDoctrine()->getManager();

        if(!array_key_exists('offerProviderSymbol', $arguments)) {
            throw new \Exception("Parameter 'offerProviderSymbol' must exist in 'arguments'.");
        }

        if(!array_key_exists('price', $arguments)) {
            throw new \Exception("Parameter 'price' must exist in 'arguments'.");
        } elseif(!is_numeric($arguments->price)) {
            throw new \Exception("Parameter 'price' in 'arguments' must be numeric.");

        }

        $lead = new Lead();
        $lead->setUser($user);

        $lead->populateFromObject($arguments);

        $validator = $this->container->get('validator');

        // lets validate
        $errors = $validator->validate($lead);
        if (count($errors) > 0) {
            return $errors;
        }
        $em->persist($lead);
        $em->flush();

        // todo: now send the lead to qtravel e-mail
        $address = $this->container->getParameter('sent_sales_leads_to');
        $message  = \Swift_Message::newInstance()
            ->setSubject('Travel Alert Sales Lead')
            ->setTo($address)
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setBody(
                $this->renderView(
                    'WshLapiBundle:Emails:sent_sales_lead.txt.twig',
                    array(
                        'lead' => $lead
                    )
                )
            );

        $this->container->get('mailer')->send($message);

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
    public function updateUser($appId, \stdClass $options, $securityToken)
    {
        $validator = $this->container->get('validator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->getAppUser($appId, $securityToken);
        $user->populateFromObject($options);
        // lets validate user
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $errors;
        }
        $em->persist($user);
        $em->flush();
        return $user;

    }

    public function sendTestNotification($appId, $securityToken, $notificationParams)
    {
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $notificationService = $this->container->get('rms_push_notifications');

        $notification = new iOSMessage();

        if($user->getApplePushToken() !== null) {
            $notification->setDeviceIdentifier($user->getApplePushToken());
        } else {
            throw new \Exception("Apple Push Token for given user is not set");
        }

        foreach ($notificationParams as $key => $value) {
            $methodName = 'set'.ucfirst($key);
            if(method_exists($notification, $methodName)) {
                $notification->$methodName($value);
            }
        }

        $notificationService->send($notification);

        return "Notification sent";
    }
}