<?php

namespace Wsh\LapiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AlertController extends Controller
{
    /**
     * @Route("/alert/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
    	$service = $this->container->get('wsh_lapi.content');
        return array('name' => $name);
    }
}
