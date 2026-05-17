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
                'choice_label' => function (Emotion $emotion) {
                    // Show parent name for secondary emotions
                    if ($emotion->getParent()) {
                        return $emotion->getParent()->getName().' > '.$emotion->getName();
                    }

                    return $emotion->getName();
                },
                'placeholder' => 'Choisir une émotion',
                'attr' => ['class' => 'w-full'],
                'query_builder' => function ($repo) {
                    // Only show secondary emotions (those with a parent)
                    return $repo->createQueryBuilder('e')
                        ->andWhere('e.parent IS NOT NULL')
                        ->orderBy('e.parent, e.name', 'ASC');
                },
                'choice_attr' => function (?Emotion $emotion, $key, $index) {
                    // attach color attribute for JS preview using parent color
                    if (!$emotion) {
                        return [];
                    }

                    $color = '';
                    if ($emotion->getParent() && $emotion->getParent()->getColor()) {
                        $color = $emotion->getParent()->getColor();
                    } elseif ($emotion->getColor()) {
                        $color = $emotion->getColor();
                    }

                    return ['data-color' => $color];
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
