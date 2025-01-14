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
            ->add('email')
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe'
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Organisateur' => 'ROLE_ORGANISATEUR',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('championship', EntityType::class, [
                'class' => ChampionshipList::class,
                'choice_label' => 'championshipName',
                'placeholder' => 'Sélectionner un championnat',
                'mapped' => false,  // Ne lie pas directement à l'entité User
                'attr' => ['onchange' => 'this.form.submit()']  // Recharge le formulaire au changement
            ])
            ->add('myTeams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    if (!empty($options['championship_id']) && isset($options['user'])) {
                        // Vérification si l'utilisateur a déjà une équipe dans ce championnat
                        $userTeam = $er->createQueryBuilder('t')
                            ->where('t.championshipList = :id')
                            ->andWhere('t.creator = :user')
                            ->setParameter('id', $options['championship_id'])
                            ->setParameter('user', $options['user']->getId())
                            ->getQuery()
                            ->getOneOrNullResult();
            
                        if ($userTeam) {
                            // Affiche uniquement l'équipe existante de l'utilisateur
                            return $er->createQueryBuilder('t')
                                ->where('t.id = :teamId')
                                ->setParameter('teamId', $userTeam->getId());
                        } else {
                            // Sinon, affiche les équipes sans créateur
                            return $er->createQueryBuilder('t')
                                ->where('t.championshipList = :id')
                                ->andWhere('t.creator IS NULL')
                                ->setParameter('id', $options['championship_id']);
                        }
                    } else {
                        return $er->createQueryBuilder('t')->where('1=0'); 
                    }
                },
                'choice_value' => function ($choice) {
                    return $choice instanceof \App\Entity\Team ? $choice->getId() : null;
                },
                'label' => 'Sélectionner une équipe'
            ])
            ->add('removeCreator', ChoiceType::class, [
                'label' => 'Retirer le rôle de créateur de cette équipe',
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'mapped' => false,
                'required' => false,
                'expanded' => true,
                'disabled' => !isset($options['userTeam'])  // Désactiver si l'utilisateur n'a pas d'équipe
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'championship_id' => null,
            'user' => null, // Ajout de l'utilisateur dans les options
            'userTeam' => null  // Ajout explicite de l'option userTeam
        ]);
    }

}
