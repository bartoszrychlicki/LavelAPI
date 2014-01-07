<?php
namespace Wsh\LapiBackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class OfferUpdateAdmin extends Admin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'startedAt'
    );

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('startedAt')
            ->add('status')
            ->add('finishedAt')
            ->add('updatedOffers')
            ->add('sentNotifications')
        ;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('export', 'list'));
    }
}