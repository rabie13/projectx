<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['placeholder' => 'Project Title']
            ])
            ->add('url', TextType::class, [
                'label' => 'Filename\Url',
                'attr' => ['placeholder' => 'File name or url']
            ])
            ->add('tasks', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Number of tasks'
                ]
            ])
            ->add('description', null, [
                'attr' => ['placeholder' => 'Description ...']
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'In progress' => 'In progress',
                    'Done' => 'Done',
                    'Blocked' => 'Blocked',
                ],
            ])
            ->add('photo', FileType::class , [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
