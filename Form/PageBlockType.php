<?php

namespace Arkounay\BlockI18nBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Arkounay\BlockI18nBundle\Entity\PageBlock;

class PageBlockType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'disabled' => !$options['editable']
            ])
            ->add('translations',
                TranslationsType::class, [
                    'fields' => [
                        'content' => [
                            'field_type' => TextareaType::class,
                            'label' => 'Description*',
                            'attr' => ['class' => 'tinymce']
                        ]
                    ]
                ]
            );
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PageBlock::class,
            'editable' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'arkounay_pageblock';
    }
}
