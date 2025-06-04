<?php

namespace App\Form;

use App\Entity\EmploiDuTemps;
use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Departement;
use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmploiDuTempsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Sélectionnez la date'
                ]
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 08:00'
                ]
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10:00'
                ]
            ])
            ->add('duree', TextType::class, [
                'label' => 'Durée',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 02:00',
                    'readonly' => true
                ],
                'required' => false
            ])
            ->add('cours', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'nomCours',
                'label' => 'Cours',
                'placeholder' => 'Sélectionnez un cours',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nomSalle',
                'label' => 'Salle',
                'placeholder' => 'Sélectionnez une salle',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('departement', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'nomDepartement',
                'label' => 'Département',
                'placeholder' => 'Sélectionnez un département',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'nomNiveau',
                'label' => 'Niveau',
                'placeholder' => 'Sélectionnez un niveau',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmploiDuTemps::class,
        ]);
    }
} 