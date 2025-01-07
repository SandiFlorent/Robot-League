<?php

namespace App\Form;

use App\Entity\Championship;
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
            ->add('matchDate', null, [
                'widget' => 'single_text',
            ])
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
                    'Annulé' => State::Canceled->value,
                    'En cours' => State::InProgress->value,
                    'Non commencé' => State::NotStarted->value,
                    'Victoire Blue' => State::WinBlue->value,
                    'Victoire Green' => State::WinGreen->value,
                    'Égalité' => State::Draw->value,
                ],
                'expanded' => true, // Affiche les options sous forme de boutons radio
                'multiple' => false, // On ne permet pas de choisir plusieurs valeurs
                'data' => State::NotStarted->value, // Par défaut, l'état est "Non Commencé"
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
