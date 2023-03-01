<?php

namespace PrestaShop\Module\HsRdv\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;


class TypeReparationType extends TranslatorAwareType
{
    /**
     * TypeReparationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales
    ) {
        parent::__construct($translator, $locales);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_type_reparation', HiddenType::class)
            ->add('name', TextType::class, [
                'required' => true,
                'label' => $this->trans('Nom du type de réparation', 'Modules.Hsrdv.Admin'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'module_hsrdv';
    }
}
