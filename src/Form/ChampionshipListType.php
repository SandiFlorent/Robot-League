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
            ->add('ChampionshipName')
            ->add('description')
            ->add('adress')
            ->add('dateStart', null, [
                'widget' => 'single_text',
            ])
            ->add('dateEnd', null, [
                'widget' => 'single_text',
            ])
            ->add('threshold')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChampionshipList::class,
        ]);
    }
}
