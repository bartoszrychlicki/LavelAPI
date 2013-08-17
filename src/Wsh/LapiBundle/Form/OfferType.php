<?php

namespace Wsh\LapiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OfferType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('qTravelOfferId')
            ->add('isFeatured')
            ->add('isHotDeal')
            ->add('name')
            ->add('description')
            ->add('leadPhoto')
            ->add('stars')
            ->add('price')
            ->add('duration')
            ->add('country')
            ->add('city')
            ->add('departs')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wsh\LapiBundle\Entity\Offer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wsh_lapibundle_offer';
    }
}
