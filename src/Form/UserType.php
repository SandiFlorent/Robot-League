<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
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
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ORGANISATEUR' => 'ROLE_ORGANISATEUR',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
            ])
            ->add('myTeam', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id', // Affiche l'id de l'équipe
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->leftJoin('t.creator', 'u') // Join avec l'utilisateur créateur
                        ->where('u.id IS NULL') // Filtrer pour les équipes sans créateur
                        ->orderBy('t.Name', 'ASC'); // Optionnel : trier par nom de l'équipe
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
