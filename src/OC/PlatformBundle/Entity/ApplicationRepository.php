<?php

namespace OC\PlatformBundle\Entity;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{
  public function getApplicationsWithAdvert($limit)
  {
	$qb = $this->createQueryBuilder('a');
	// On fait une jointure avec l'entit� Advert avec pour alias � adv �
	$qb
	  ->join('a.advert','adv')
	  ->addSelect('adv')
	  ;
	// Puis on ne retourne que $limit r�sultats
	$qb->setMaxResults($limit);  
	  
	  return $qb
	    ->getQuery
		->getResult
		;
	    
  }
}