<?php

namespace App\Form;

use App\Entity\Salle;
use App\Enum\SalleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSalle', TextType::class, [
                'label' => 'Nom de la Salle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Salle 101, Amphi A, etc.'
                ]
            ])
            ->add('capacite', IntegerType::class, [
                'label' => 'Capacité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre de places',
                    'min' => 1
                ]
            ])
            ->add('type', EnumType::class, [
                'class' => SalleType::class,
                'label' => 'Type de Salle',
                'choice_label' => function (SalleType $salleType): string {
                    return match($salleType) {
                        SalleType::SALLE => 'Salle de Cours',
                        SalleType::TP => 'Salle de TP',
                        SalleType::AMPHI => 'Amphithéâtre',
                        default => $salleType->value
                    };
                },
                'choice_value' => function (?SalleType $salleType): ?string {
                    return $salleType?->value;
                },
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez le type de salle'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
    }
} 