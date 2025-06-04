<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Departement;
use App\Entity\Niveau;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCours', TextType::class, [
                'label' => 'Nom du Cours',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Saisissez le nom du cours'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description du cours (optionnel)'
                ]
            ])
            ->add('enseignant', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function(Utilisateur $utilisateur) {
                    return $utilisateur->getPrenom() . ' ' . $utilisateur->getNom();
                },
                'label' => 'Enseignant',
                'placeholder' => 'Sélectionnez un enseignant',
                'required' => false,
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
            'data_class' => Cours::class,
        ]);
    }
} 