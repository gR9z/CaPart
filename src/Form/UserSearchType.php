<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => "Search by last name"
                ]
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