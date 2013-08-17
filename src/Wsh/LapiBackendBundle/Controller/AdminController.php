<?php

namespace Wsh\LapiBackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
                // create new object
                $offer = new Offer();

                // fetch the offer from provider
                $response = $provider->findOfferById($offerId);
                var_dump($response);exit();
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
