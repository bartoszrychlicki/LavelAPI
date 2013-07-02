<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bard
 * Date: 02.07.2013
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wsh\LapiBundle\OfferProvider\Qtravel;


use Wsh\LapiBundle\OfferProvider\OfferProviderInterface;

class Provider implements OfferProviderInterface
{
    public function findOfferById($id)
    {
        return true;
    }

    public function findOfferByName($name)
    {

    }

    public function findOffersByParams($params)
    {

    }

    public function getProviderName()
    {
        return "QTravel API";
    }
}