<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('startDateTime', DateTimeType::class, [
                'label' => 'Start date',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('registrationDeadline', DateType::class, [
                'label' => 'Registration deadline',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('maxRegistrations', IntegerType::class, [
                'label' => 'Maximum registrations',
                'attr' => ['class' => 'form-control']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (in minutes)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('eventDetails', TextareaType::class, [
                'label' => 'Event details',
                'attr' => ['class' => 'form-control']
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'label' => 'City',
                'placeholder' => '-- choice your city --',
                'attr' => ['class' => 'form-control']
            ])
            ->
            add('submit', SubmitType::class, [
                'label' => 'Submit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

