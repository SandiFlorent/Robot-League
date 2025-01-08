<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Team;
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
        ]);
}
}
