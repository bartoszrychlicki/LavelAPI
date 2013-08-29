<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;

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

        return array(
            'offers' => $hotDealOffers
        );
    }
}
