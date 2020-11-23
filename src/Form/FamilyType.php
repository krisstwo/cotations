<?php

namespace App\Form;

use App\Entity\Family;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FamilyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', null, ['required' => true, 'constraints' => [new NotBlank()]])
            ->add('label', null, ['required' => true, 'constraints' => [new NotBlank()]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Family::class,
            'csrf_protection' => false
        ]);
    }
}
