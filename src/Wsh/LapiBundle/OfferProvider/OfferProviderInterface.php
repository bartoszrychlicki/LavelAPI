<?php
namespace Wsh\LapiBundle\OfferProvider;


/**
 * Common interface for different travel offers providers (such as Qtravel API)
 *
 * Class OfferProviderInterface
 * @package Wsh\LapiBundle\OfferProvider
 */
interface OfferProviderInterface
{
    public function findOfferById($id);
    public function findOfferByName($name);
    public function findOffersByParams($params);
    public function getProviderName();

}