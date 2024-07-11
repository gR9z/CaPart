<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EventSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event name',
                'required' => false,
                'attr' => [
                    'placeholder' => "Search by event name"
                ]
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'placeholder' => 'YYYY-MM-DD'
                ]
            ])
            ->add('endDate', DateType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'placeholder' => 'YYYY-MM-DD'
                ]
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a location',
                'required' => false,
                'label' => 'Location',
            ])
            ->add('filters', ChoiceType::class, [
                'label' => 'Filters',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Events I\'m organizing' => 'organizedEvents',
                    'Events I\'m registered to' => 'myEvents',
                    'Events I\'m not registered to' => 'notMyEvents',
                    'Show past events' => 'pastEvents',
                   ],
                'required' => false,
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search',
            ])
            ->add('clear', ButtonType::class, [
                'label' => 'Clear',
                'attr' => ['onclick' => 'window.location.href = window.location.pathname']
            ]);
    }
}