<?php

namespace App\Form;

use App\Entity\BreathingExercise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BreathingExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'exercice',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('inhaleSeconds', IntegerType::class, [
                'label' => 'Secondes d\'inspiration',
            ])
            ->add('holdSeconds', IntegerType::class, [
                'label' => 'Secondes de retenue',
            ])
            ->add('exhaleSeconds', IntegerType::class, [
                'label' => 'Secondes d\'expiration',
            ])
            ->add('cycles', IntegerType::class, [
                'label' => 'Nombre de cycles',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BreathingExercise::class,
        ]);
    }
}

