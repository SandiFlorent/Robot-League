<?php

namespace App\Form;

use App\Entity\Championship;
use App\Entity\ChampionshipList;
use App\Entity\Team;
use App\Enum\State;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChampionshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('blueScore')
            ->add('greenScore')
            ->add('blueTeam', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',  // Affiche le nom de l'équipe (au lieu de l'ID)
            ])
            ->add('greenTeam', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',  // Affiche le nom de l'équipe (au lieu de l'ID)
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Annulé' => State::CANCELED->value ,
                    'En cours' => State::IN_PROGRESS->value,
                    'Non commencé' => State::NOT_STARTED->value,
                    'Victoire Blue' => State::WIN_BLUE->value,
                    'Victoire Green' => State::WIN_GREEN->value,
                    'Égalité' => State::DRAW->value,
                ],
                'expanded' => true, // Affiche les options sous forme de boutons radio
                'multiple' => false, // On ne permet pas de choisir plusieurs valeurs
                'data' => State::NOT_STARTED->value, // Par défaut, l'état est "Non Commencé"
            ])
            ->add('championship_list_id', EntityType::class, [
                'class' => ChampionshipList::class,
                'choice_label' => 'name',  // Affiche le nom du championnat (au lieu de l'ID)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Championship::class,
        ]);
    }
}
