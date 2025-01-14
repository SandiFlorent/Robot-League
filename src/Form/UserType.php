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
use Symfony\Component\Form\Extension\Core\Type\EmailType;

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

            // Champs standards
            ->add('email', EmailType::class)
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

