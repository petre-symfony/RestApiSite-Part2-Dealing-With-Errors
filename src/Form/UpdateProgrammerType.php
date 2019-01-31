<?php

namespace App\Form;

use App\Entity\Programmer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateProgrammerType extends ProgrammerType {

  public function configureOptions(OptionsResolver $resolver) {
  	parent::configureOptions($resolver);

    $resolver->setDefaults([
			'is_edit' => true
    ]);
  }

}
