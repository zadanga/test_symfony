<?php
// src/OC/PlatformBundle/Form/Type/CkeditorType.php

namespace OC\PlatformBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CkeditorType extends AbstractType
{
  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefault(array(
      'attr' => array('class' => 'ckeditor') // On ajoute la classe
    ));
  }

  public function getParent()
  {
    return 'textarea';
  }
  
  public function getName()
  {
    return 'ckeditor';
  }


}