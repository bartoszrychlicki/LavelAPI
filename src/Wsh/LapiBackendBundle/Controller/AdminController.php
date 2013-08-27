<?php

namespace Wsh\LapiBackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Components\CssSelector\Parser;
use Symfony\Component\DomCrawler\Crawler;
use Wsh\LapiBackendBundle\Form\OfferImportType;
use Wsh\LapiBundle\Entity\Offer;
use Wsh\LapiBundle\OfferProvider\Qtravel\Provider as Qtravel;

class AdminController extends Controller
{

    /**
     * @Route("/import-offer")
     * @Template()
     */
    public function importOfferAction(Request $request)
    {
        $offer = new Offer();
        $form = $this->createForm(new OfferImportType());

        $form->handleRequest($request);

        if($form->isValid()) {
            $data = $form->getData();
            // parse the url
            $provider = new Qtravel($this->container);
            $offerId = $provider->parseUrl($data['offerUrl']);

            $em = $this->getDoctrine()->getManager();
            // check if this offer allready exists
            $repo = $em->getRepository('WshLapiBundle:Offer');
            $offer = $repo->findOneByQTravelOfferId($offerId);
            if(!$offer) {
                set_time_limit(0);

                $content = $provider->sendRequest($data['offerUrl']);
                $crawler = new Crawler($content);
                $crawler = $crawler->filter('a.book-term')->first()->attr('href');

                $content = $provider->sendRequest($crawler);
                $crawler = new Crawler($content);
                $offerCode = $crawler->filter('div.code strong')->text();

                $offer = new Offer();

                $offerJson = $provider->findOfferById($offerCode);
                $offer = $provider->transformSingleOfferToEntity($offerJson, $data['isFeatured'], $data['isHotDeal']);

            }
            $em->persist($offer);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Nowa oferta została zaimportowana'
            );

            return $this->redirect($this->generateUrl('admin_wsh_lapi_offer_list'));
        }

        return array(
            'form' => $form->createView(),
            'admin_pool'      => $this->container->get('sonata.admin.pool'),
        );
    }
}