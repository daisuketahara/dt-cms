<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Redirect;

class RedirectForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_page_route', TextType::class, array('label' => 'Old page route'))
            ->add('new_page_route', TextType::class, array('label' => 'New page route'))
            ->add('redirect_type', ChoiceType::class, array('label' => 'Redirect type','choices'  => array('301' => 301, '302' => 302)))
            ->add('active', CheckboxType::class, array('label' => 'Active'))
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Redirect::class,
        ));
    }
}
