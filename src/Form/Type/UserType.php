<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author: Marius Bora
 * Date: 20/2/20
 * Time: 10:48
 */

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userName', TextType::class)
            ->add('password', TextType::class)
            ->add('submit', SubmitType::class, ['label' => 'Login'])
        ;

        if(!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }
}