<?php

namespace App\Form;

use App\Entity\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'attr' => [
                    'class' => 'bg-gray-700 border border-blue-500 text-white text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5',
                    'placeholder' => 'Nom du terrain'
                ],
                'label' => 'Nom du Terrain',
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-white']
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Field::class,
        ]);
    }
}
