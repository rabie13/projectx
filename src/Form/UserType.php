<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roles = [ "Admin" => "ROLE_ADMIN"  , "Basic User" => "ROLE_USER" ];
        $builder
        ->add('email', EmailType::class, [
            'label' => "Email",
            "attr" => ['placeholder' => "Email"]
        ])
        ->add('fullname', null, [
            'label' => 'Full name'
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => false,
            'first_options' => [
                'attr' => [
                    'placeholder' => 'Enter the password',
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limite }} caractères.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'label' => 'New password',
                'always_empty' => false,
            ],
            'second_options' => [
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Repeat password',
            ],
            'invalid_message' => 'The password fields must be identical.',
            'mapped' => false,
        ])
        ->add('photo', FileType::class , [
            'mapped' => false,
            'required' => false
        ])
        ->add('roles', ChoiceType::class, [
            'label' => 'Role',
            'choices'  => $roles
        ])
        ;

         //roles field data transformer
         $builder->get('roles')
         ->addModelTransformer(new CallbackTransformer(
             function ($rolesArray) {
                 // transform the array to a string
                 return count($rolesArray)? $rolesArray[0]: null;
             },
             function ($rolesString) {
                 // transform the string back to an array
                 return [$rolesString];
             }
         ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
