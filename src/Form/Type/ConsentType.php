<?php
/**
 * @author: Marius Bora
 * Date: 20/2/20
 * Time: 11:52
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ConsentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', CheckboxType::class)
            ->add('campaigns', CheckboxType::class)
            ->add('submit', SubmitType::class, ['label' => 'I agree']);


        if(!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }
}