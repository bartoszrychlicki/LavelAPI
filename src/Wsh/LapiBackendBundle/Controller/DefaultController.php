<?php

namespace Wsh\LapiBackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        /*
        if ($request->getMethod() == 'POST') {
            // search of given offerProviderSymbol by
        }
        return array();*/

        return $this->redirect($this->generateUrl('sonata_admin_dashboard'));
    }
}
