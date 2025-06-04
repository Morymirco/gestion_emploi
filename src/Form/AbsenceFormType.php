<?php

namespace App\Form;

use App\Entity\Absence;
use App\Entity\Utilisateur;
use App\Entity\EmploiDuTemps;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsenceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function (Utilisateur $utilisateur) {
                    return $utilisateur->getPrenom() . ' ' . $utilisateur->getNom() . ' (' . $utilisateur->getEmail() . ')';
                },
                'label' => 'Utilisateur',
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez un utilisateur'
            ])
            ->add('emploiDuTemps', EntityType::class, [
                'class' => EmploiDuTemps::class,
                'choice_label' => function (EmploiDuTemps $emploi) {
                    return $emploi->getDate()->format('d/m/Y') . ' - ' . 
                           $emploi->getHeureDebut()->format('H:i') . ' à ' . 
                           $emploi->getHeureFin()->format('H:i') . 
                           ($emploi->getCours() ? ' (' . $emploi->getCours()->getNomCours() . ')' : '');
                },
                'label' => 'Cours concerné',
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez le cours'
            ])
            ->add('dateAbsence', DateTimeType::class, [
                'label' => 'Date et heure de l\'absence',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'absence',
                'choices' => [
                    'Étudiant absent' => 'etudiant',
                    'Enseignant absent' => 'enseignant'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Type d\'absence'
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de l\'absence',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Précisez le motif de l\'absence...',
                    'rows' => 3
                ]
            ])
            ->add('justifiee', CheckboxType::class, [
                'label' => 'Absence justifiée',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('justification', TextareaType::class, [
                'label' => 'Justification',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Document ou explication justifiant l\'absence...',
                    'rows' => 2
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'en_attente',
                    'Approuvée' => 'approuvee',
                    'Rejetée' => 'rejetee'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Absence::class,
        ]);
    }
} 