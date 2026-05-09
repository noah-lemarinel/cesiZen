<?php

namespace App\Form;

use App\Entity\Emotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmotionEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emotion', EntityType::class, [
                'class' => Emotion::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une émotion',
                'attr' => ['class' => 'w-full'],
                'choice_attr' => function (?Emotion $emotion, $key, $index) {
                    // attach color attribute for JS preview
                    if (!$emotion) {
                        return [];
                    }

                    return ['data-color' => $emotion->getColor() ?? ''];
                },
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Ajouter des notes sur cette émotion...'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
