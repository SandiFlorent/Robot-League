<?php

namespace App\Form;

use App\Entity\ChampionshipList;
use App\Entity\Slot;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('dateEnd', null, [
                'widget' => 'single_text',
            ])
            ->add('length', IntegerType::class, [
                'label' => 'Longueur du slot (en minutes)',
                'attr' => ['min' => 1],
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
