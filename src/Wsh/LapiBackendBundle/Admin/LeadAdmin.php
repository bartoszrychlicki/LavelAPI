<?php
namespace Wsh\LapiBackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LeadAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
//            ->add('name')
//            ->add('isHotDeal', null, array('required' => false))
//            ->add('isFeatured', null, array('required' => false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
//            ->add('sendHotDealsAlert')
//            ->add('sendLastMinuteAlert')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('phoneNumber')
            ->add('createdAt')
            ->add('offerProviderSymbol')
            ->add('user')
        ;
    }
}