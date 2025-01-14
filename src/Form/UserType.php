<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\ChampionshipList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        // Sélection du championnat (non lié à l'entité User)
        ->add('championship', EntityType::class, [
            'class' => ChampionshipList::class,
            'choice_label' => 'championshipName',
            'placeholder' => 'Sélectionner un championnat',
            'mapped' => false,
            'required' => true
        ])

        ->add('myTeams', EntityType::class, [
            'class' => Team::class,
            'choice_label' => 'name',
            'multiple' => true,  // Gestion de la collection
            'expanded' => false,
            'required' => false, // Pas obligatoire lors du rechargement
            'query_builder' => function (EntityRepository $er) use ($options) {
                if (!empty($options['championship_id']) && isset($options['user'])) {
                    return $er->createQueryBuilder('t')
                        ->where('t.championshipList = :id')
                        ->andWhere('(t.creator IS NULL OR t.creator = :user)')
                        ->setParameter('id', $options['championship_id'])
                        ->setParameter('user', $options['user']->getId());
                }
                return $er->createQueryBuilder('t')->where('1=0');
            },
            'choice_value' => function (?Team $team) {
                return $team ? (string)$team->getId() : '';
            }
        ])

        // Option pour retirer le créateur
        ->add('removeCreator', ChoiceType::class, [
            'label' => 'Retirer le rôle de créateur',
            'choices' => ['Oui' => true, 'Non' => false],
            'mapped' => false,
            'required' => false,
            'expanded' => true,
            'disabled' => empty($options['userTeam']),
        ])

        // Champs standards
        ->add('email')
        ->add('password', PasswordType::class, [
            'mapped' => false,
            'required' => false
        ])
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Utilisateur' => 'ROLE_USER',
                'Organisateur' => 'ROLE_ORGANISATEUR',
                'Administrateur' => 'ROLE_ADMIN'
            ],
            'multiple' => true,
            'expanded' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'championship_id' => null,
            'user' => null,
            'userTeam' => null
        ]);
    }
}

