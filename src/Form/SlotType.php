<?php

namespace App\Form;

use App\Entity\Slot;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class SlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'bg-gray-700 border border-blue-500 text-white text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5',
                ],
                'label' => 'Date de début',
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-white']
            ])
            ->add('dateEnd', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'bg-gray-700 border border-blue-500 text-white text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5',
                ],
                'label' => 'Date de fin',
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-white']
            ])
            ->add('length', IntegerType::class, [
                'label' => 'Longueur du slot (en minutes)',
                'attr' => [
                    'class' => 'bg-gray-700 border border-blue-500 text-white text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5',
                    'min' => 1
                ],
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-white']
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Slot::class,
        ]);
    }
}
