<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Place;
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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $location = $options['location'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name'
            ])
            ->add('startDateTime', DateTimeType::class, [
                'label' => 'Start date',
                'data' => new \DateTime(),
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['min' => (new \DateTime())->format('Y-m-d\TH:i')]
            ])
            ->add('registrationDeadline', DateType::class, [
                'label' => 'Registration deadline',
                'data' => new \DateTime(),
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')]
            ])
            ->add('maxRegistrations', IntegerType::class, [
                'label' => 'Maximum registrations'
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (in minutes)'
            ])
            ->add('eventDetails', TextareaType::class, [
                'label' => 'Event details'
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Host',
                'choice_label' => 'name',
                'choices' => $location ? [$location] : $location,
                'data' => $location,
                'attr' => [
                    'readonly' => true,
                    'class' => 'bg-gray-200',
                ],
            ])
            ->add('city', EntityType::class, [
                'mapped' => false,
                'class' => City::class,
                'choice_label' => 'name',
                'label' => 'City',
                'placeholder' => '-- choice your city --'
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'label' => 'Place',
                'placeholder' => '-- select a place --',
                'choices' => []
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit'
            ]);

        $builder->get('city')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->addPlaceField($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                /* @var $place Place */
                $place = $data->getPlace();
                $form = $event->getForm();
                if ($place) {
                    $city = $place->getCity();
                    $this->addPlaceField($form, $city);
                    $form->get('city')->setData($city);
                } else {
                    $this->addPlaceField($form, null);
                }
            }
        );

    }

    private function addPlaceField(FormInterface $form, ?City $city): void
    {
        $form->add('place', EntityType::class, [
            'class' => Place::class,
            'placeholder' => $city ? '-- select a place --' : '-- choose a city first --',
            'choices' => $city ? $city->getPlaces() : [],
            'choice_label' => 'name',
            'label' => 'Place',
            'attr' => ['class' => 'form-control']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'location' => null,
        ]);
    }
}

