<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Team;
use App\Entity\ChampionshipList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('Name', null, [
            'attr' => [
                'class' => 'border rounded-lg p-2 w-full', // Exemple de classes Tailwind
                'placeholder' => 'Nom de l\'équipe'
            ]
        ])
        ->add('championshipList', EntityType::class, [
            'class' => ChampionshipList::class, // La classe ChampionshipList
            'choice_label' => 'ChampionshipName',  // Affiche le nom du championnat
            'label' => 'Choisir un championnat', // Libellé du champ
        ]);
}
}
