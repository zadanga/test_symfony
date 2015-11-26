<?php
// src/OC/PlatformBundle/DoctrineListener/ApplicationNotification.php

namespace OC\PlatformBundle\DoctrineListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OC\PlatformBundle\Entity\Application;

class ApplicationNotification
{
  private $mailer;
  
  public function __construct(\Swift_Mailer $mailer)
  {
    $this->mailer = $mailer;
  }
  
  public function postPersit(LifecycleEventArgs $args)
  {
    $entity = $args->getEntity();
	
	// On veut envoyer un email que pour les entités Application
	if(!$entity instanceof Application){
	  return;
	}
	
	$message = new \Swift_Message(
	 'Nouvelle candidature' ,
	 'Vous avez reçu une nouvelle candidature.'
	);
	
	$message
	  ->addTo($entity->getAdvert()->getAuthor()) // Ici il faut un attribut "email", utilisation "author" pour l'exemple
	  ->addFrom('admin@votresite.com')
	  ;
	  
	  $this->mailer->send($message);
  }
}
