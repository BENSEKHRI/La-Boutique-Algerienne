<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new Length([
                    'min' => 3,
                    'max' => 30
                ])],
                'attr' => [
                    'placeholder' => 'Votre prénom',
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new Length([
                    'min' => 3,
                    'max' => 30
                ])],
                'attr' => [
                    'placeholder' => 'Votre nom',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new Length([
                    'min' => 3,
                    'max' => 60
                ])],
                'attr' => [
                    'placeholder' => 'Votre email',
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'En quoi pouvons nous vous aider ?',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Envoyer",
                'attr' => [
                    'class' => 'btn btn-success btn-block',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
