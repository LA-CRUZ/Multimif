<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre',TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez le titre du Quiz',
                    'required' => true
                ]
            ])
            ->add('description',TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez la description du Quiz'
                ]
            ])
            ->add('end', CheckBoxType::class, [
                'required' => false,
                'label' => 'Cliquez ici si vous voulez ajouter une date de fin a votre quiz',
                'attr' => [
                    'class' => 'checkboxdate',
                    'placeholder' => 'Cliquez ici si vous voulez ajouter une date de fin a votre quiz',
                ]
            ])
            ->add('Suivant', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success btn-question'],
            ])
            ->add('deadLine', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'date js-datepicker',
                    'placeholder' => 'Date limite'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
