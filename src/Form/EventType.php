<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
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
                'mapped' => false,
                'class' => City::class,
                'choice_label' => 'name',
                'label' => 'City',
                'placeholder' => '-- choice your city --',
                'attr' => ['class' => 'form-control']
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'label' => 'Place',
                'placeholder' => '-- select a place --',
                'choices' => [],
                'attr' => ['class' => 'form-control']
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
        ]);
    }
}

