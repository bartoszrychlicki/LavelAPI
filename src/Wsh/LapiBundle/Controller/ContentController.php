<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;

use Wsh\LapiBundle\Entity\OfferFav;

class ContentController extends Controller
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Returns array of offerProviderSymbol objects marked as featured
     */
    public function getFeaturedOffers()
    {
        $em = $this->getDoctrine()->getManager();
        $offersRepo = $em->getRepository('WshLapiBundle:Offer');

        $featuredOffers = $offersRepo->findBy(array('isFeatured' => 1));

        if(!$featuredOffers){
            throw new \Exception('There is no offers with "Featured Offers" flag.');
        }

        foreach($featuredOffers as $offer) {
            $offer->setReadStatus(null);
            $offer->setIsRead(null);
        }

        return array(
            'offers' => $featuredOffers
        );
    }

    /**
     * Returns array of offerProviderSymbol objects marked as hot deals
     */
    public function getHotDeals()
    {
        $em = $this->getDoctrine()->getManager();
        $offersRepo = $em->getRepository('WshLapiBundle:Offer');

        $hotDealOffers = $offersRepo->findBy(array('isHotDeal' => 1));

        if(!$hotDealOffers){
            throw new \Exception('There is no offers with "Hot Deal" flag.');
        }

        foreach($hotDealOffers as $offer) {
            $offer->setReadStatus(null);
            $offer->setIsRead(null);
        }

        return array(
            'offers' => $hotDealOffers
        );
    }

    public function setAsFavourite($appId, $securityToken, $offerId, $status = 1)
    {
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $em = $this->getDoctrine()->getManager();
        $offer = $em->getRepository('WshLapiBundle:Offer')->findOneById($offerId);

        if(!$offer) {
            $provider = $this->container->get('wsh_lapi.provider.qtravel');
            $offerJson = $provider->findOfferById($offerId);

            if ($offerJson) {
                $offer = $provider->transformSingleOfferToEntity($offerJson);
                $em->persist($offer);
                $em->flush();
            } else {
                throw new \Exception('No offer with id '.$offerId.' found in '.$provider->getProviderName());
            }
        }

        $offerFav = $em->getRepository('WshLapiBundle:OfferFav')->findOneBy(array(
            'user_id' => $user->getId(),
            'offer_id' => $offerId
        ));

        if($offerFav && $status == 1) {
            throw new \Exception('This offer is already set as favourite by this user.');
        } else if ($status == 1 && !$offerFav) {

            $offerFav = new OfferFav();

            $offerFav->setOfferId($offer);
            $offerFav->setUserId($user);

            $em->persist($offerFav);
        } else if ($status == 0 && $offerFav) {
            $em->remove($offerFav);
        } else if ($status == 0 && !$offerFav) {
            throw new \Exception('This offer is not set as favourite by this user.');
        } else {
            throw new \Exception("Unknown status.");
        }

        $em->flush();

        if ($status == 1) {
            return "Offer set as favourite";
        } elseif ($status == 0) {
            return "Offer unset as favourite";
        }

    }

    public function getAmountOfFavForOffer($offerId)
    {
        $em = $this->getDoctrine()->getManager();
        $offer = $em->getRepository('WshLapiBundle:Offer')->findOneById($offerId);

        if(!$offer) {
            return array(
                "amount" => 0
            );
        }

        $offerFav = $em->getRepository('WshLapiBundle:OfferFav')->findBy(array(
            'offer_id' => $offerId
        ));

        return array(
            "amount" => count($offerFav)
        );

    }

    public function getUserFavOffers($appId, $securityToken)
    {
        if($this->container->has('wsh_lapi.users')) {
            $userService = $this->container->get('wsh_lapi.users');
            $user = $userService->getAppUser($appId, $securityToken);
        } else {
            throw new \Exception('No wsh_lapi.users service registered');
        }

        $em = $this->getDoctrine()->getManager();

        $offerRepo = $em->getRepository('WshLapiBundle:Offer');

        $offerFav = $em->getRepository('WshLapiBundle:OfferFav')->findBy(array(
            'user_id' => $user->getId()
        ));

        $offerAmount = count($offerFav);
        $offers = array();

        foreach($offerFav as $fav) {
            $offer = $offerRepo->findOneBy(array(
                'id' => $fav->getOfferId()
            ));
            $offer->setReadStatus(null);
            $offer->setIsRead(null);
            $offers[] = $offer;
        }

        return array(
            'amount' => $offerAmount,
            'offers' => $offers
        );
    }
}
