<?php

namespace App\Form;

use App\Entity\Emotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'émotion',
            ])
            ->add('parent', EntityType::class, [
                'class' => Emotion::class,
                'choice_label' => 'name',
                'placeholder' => '-- Émotion primaire (laisser vide) --',
                'required' => false,
                'label' => 'Émotion parente',
                'help' => 'Sélectionner une émotion parent si c\'est une sous-émotion',
                'query_builder' => function ($repo) {
                    // Only show primary emotions (those without a parent)
                    return $repo->createQueryBuilder('e')
                        ->andWhere('e.parent IS NULL')
                        ->orderBy('e.name', 'ASC');
                },
            ])
            ->add('color', ColorType::class, [
                'required' => false,
                'label' => 'Couleur (optionnel)',
                'help' => 'Laisser vide pour hériter de la couleur parente',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Brève description de cette émotion...',
                ],
                'help' => 'Description optionnelle pour les utilisateurs',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emotion::class,
        ]);
    }
}
