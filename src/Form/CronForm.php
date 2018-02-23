<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Cron;

class CronForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Name'))
            ->add('script', TextType::class, array('label' => 'Script'))
            ->add('minute', TextType::class, array('label' => 'Minute'))
            ->add('hour', TextType::class, array('label' => 'Hour'))
            ->add('day', TextType::class, array('label' => 'Day'))
            ->add('month', TextType::class, array('label' => 'Month'))
            ->add('dayOfWeek', TextType::class, array('label' => 'Day of week'))
            ->add('active', CheckboxType::class, array('label' => 'Active'))
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Cron::class,
        ));
    }
}
