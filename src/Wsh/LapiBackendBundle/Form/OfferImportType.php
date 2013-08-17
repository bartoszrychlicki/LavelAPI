<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bard
 * Date: 17.08.2013
 * Time: 11:35
 * To change this template use File | Settings | File Templates.
 */

namespace Wsh\LapiBackendBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OfferImportType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('offerUrl')
            ->add('isFeatured', 'choice', array(
                'choices'   => array('0' => 'No', '1' => 'Yes'),
                'required'  => true))
            ->add('isHotDeal', 'choice', array(
                'choices'   => array('0' => 'No', '1' => 'Yes'),
                'required'  => true))
            ->add('submit', 'submit');
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
//        $resolver->setDefaults(array(
//            'data_class' => false
//        ));
    }


    public function getName() {
        return "wsh_lapibackendbundle_offerimport";
    }
}