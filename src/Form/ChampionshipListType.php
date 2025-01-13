<?php

namespace App\Form;

use App\Entity\ChampionshipList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChampionshipListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ChampionshipName', null, [
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ])
            ->add('adress', null, [
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ])
            ->add('dateStart', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ])
            ->add('dateEnd', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ])
            ->add('threshold', null, [
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-600 bg-gray-800 text-white rounded-md'
                ],
                'label_attr' => [
                    'class' => 'block text-lg font-semibold text-white mb-2'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChampionshipList::class,
        ]);
    }
}
