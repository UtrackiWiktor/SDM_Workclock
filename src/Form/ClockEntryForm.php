<?php

namespace App\Form;

use App\Entity\ClockEntry;
use App\Entity\ProjectItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClockEntryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('project', EntityType::class, [
                'class' => ProjectItem::class,
                'choice_label' => 'name',
                'placeholder' => 'No project',
                'required' => false,
                'label' => 'Project',
            ])
            ->add('startTime', DateTimeType::class)
            ->add('endTime', DateTimeType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClockEntry::class,
        ]);
    }


}