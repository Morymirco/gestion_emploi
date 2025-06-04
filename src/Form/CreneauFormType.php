<?php

namespace App\Form;

use App\Entity\Creneau;
use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Niveau;
use App\Entity\Module;
use App\Entity\Utilisateur;
use App\Enum\TypeCreneau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreneauFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jourSemaine', ChoiceType::class, [
                'label' => 'Jour de la Semaine',
                'choices' => [
                    'Lundi' => 'lundi',
                    'Mardi' => 'mardi',
                    'Mercredi' => 'mercredi',
                    'Jeudi' => 'jeudi',
                    'Vendredi' => 'vendredi',
                    'Samedi' => 'samedi'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('typeCreneau', EnumType::class, [
                'class' => TypeCreneau::class,
                'label' => 'Type de Créneau',
                'choice_label' => function (TypeCreneau $typeCreneau): string {
                    return match($typeCreneau) {
                        TypeCreneau::SEANCE => 'Séance de Cours',
                        TypeCreneau::PAUSE => 'Pause',
                        TypeCreneau::HEURE_CREUSE => 'Heure Creuse',
                        default => $typeCreneau->value
                    };
                },
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez le type de créneau'
            ])
            ->add('cours', EntityType::class, [
                'class' => Cours::class,
                'label' => 'Cours',
                'choice_label' => 'nomCours',
                'required' => false,
                'placeholder' => 'Sélectionnez un cours (optionnel)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'label' => 'Salle',
                'choice_label' => 'nomSalle',
                'required' => false,
                'placeholder' => 'Sélectionnez une salle (optionnel)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'label' => 'Niveau',
                'choice_label' => 'nomNiveau',
                'required' => false,
                'placeholder' => 'Sélectionnez un niveau (optionnel)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'label' => 'Module',
                'choice_label' => function (Module $module): string {
                    return $module->getNomModule() . ' (' . $module->getCode() . ')';
                },
                'required' => false,
                'placeholder' => 'Sélectionnez un module (optionnel)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('enseignant', EntityType::class, [
                'class' => Utilisateur::class,
                'label' => 'Enseignant',
                'choice_label' => function (Utilisateur $user): string {
                    return $user->getPrenom() . ' ' . $user->getNom();
                },
                'required' => false,
                'placeholder' => 'Sélectionnez un enseignant (optionnel)',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'ENSEIGNANT');
                }
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Commentaire optionnel...'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Créneau Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Creneau::class,
        ]);
    }
} 