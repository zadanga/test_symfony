<?php
// src/OC/PlatformBundle/Validator/Antiflood.php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint
{
  public $message = "Votre message '%string%' est considéré comme flood.";
  
  public function validatedBy() 
  {
    return 'oc_platform_antiflood';
  }
}