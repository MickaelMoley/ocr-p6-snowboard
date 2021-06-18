<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom",
                'label_attr' => [
                    'class' => "block text-sm font-medium text-gray-700 mb-1"
                ]
            ])
            ->add('link', TextType::class, [
                'label' => "Lien vers le media",
                'label_attr' => [
                    'class' => "block text-sm font-medium text-gray-700 mb-1"
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => "Type de média",
                'label_attr' => [
                    'class' => "block text-sm font-medium text-gray-700 mb-1"
                ],
                'choices' => [
                    "Image" => 'image',
                    "Video" => 'video'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'trick_media';
    }
}
