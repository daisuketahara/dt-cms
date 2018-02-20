<?php

// src/Form/LocaleForm.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Locale;

class LocaleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Language'))
            ->add('locale', TextType::class, array('label' => 'Locale'))
            ->add('lcid', TextType::class, array('label' => 'LCID'))
            ->add('iso_code', TextType::class, array('label' => 'ISO Code'))
            ->add('default', CheckboxType::class, array('label' => 'Default', 'required' => false))
            ->add('active', CheckboxType::class, array('label' => 'Active', 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Locale::class,
        ));
    }
}
