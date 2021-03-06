<?php
/**
 * Created by PhpStorm.
 * User: Kelly
 * Date: 03/08/2019
 * Time: 15:15
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Title',
                    'attr' => [
                        'class' => 'input',
                        'placeholder' => 'Title'
                    ],
                    'data' => !empty($data) ? $data->getTitle() : null
                ]
            )
            ->add(
                'body',
                TextareaType::class,
                [
                    'label' => 'Content',
                    'attr' => [
                        'class' => 'textarea'
                    ],
                    'data' => !empty($data) ? $data->getBody() : null
                ]
            );
    }
}