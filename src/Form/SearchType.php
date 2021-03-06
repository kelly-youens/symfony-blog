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

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'searchTerm',
                TextType::class,
                [
                    'label' => 'Search',
                    'attr' => [
                        'class' => 'input'
                    ]
                ]
            );
    }
}