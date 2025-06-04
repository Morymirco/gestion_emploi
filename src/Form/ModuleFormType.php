<?php

namespace App\Form;

use App\Entity\Module;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomModule', TextType::class, [
                'label' => 'Nom du Module',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Semestre 1, Module d\'informatique...'
                ]
            ])
            ->add('code', TextType::class, [
                'label' => 'Code du Module',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: S1-2024, INFO-S1...'
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('anneeAcademique', TextType::class, [
                'label' => 'Année Académique',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 2024-2025'
                ]
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Semestre',
                'choices' => [
                    'Premier Semestre' => 1,
                    'Deuxième Semestre' => 2
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez le semestre'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description optionnelle du module...',
                    'rows' => 3
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Module Actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
} 