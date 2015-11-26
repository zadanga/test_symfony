<?php


namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use Symfony\Component\HttpFoundation\Response;


class AdvertController extends Controller
{
    public function indexAction($page)
    {
       if($page < 1) {
	   
	    throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
	   
	   }
	   // Notre liste d'annonce en dur
    $listAdverts = array(
      array(
        'title'   => 'Recherche développpeur Symfony2',
        'id'      => 1,
        'author'  => 'Alexandre',
        'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
        'date'    => new \Datetime()),
      array(
        'title'   => 'Mission de webmaster',
        'id'      => 2,
        'author'  => 'Hugo',
        'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
        'date'    => new \Datetime()),
      array(
        'title'   => 'Offre de stage webdesigner',
        'id'      => 3,
        'author'  => 'Mathieu',
        'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
        'date'    => new \Datetime())
    );

	   
	   return $this->render('OCPlatformBundle:Advert:index.html.twig', array('listAdverts' => $listAdverts));
    }
	
	public function viewAction($id)
	{
	  
	  $em = $this->getDoctrine()->getManager();
	  // On récupère le repository
	  $repository= $em->getRepository('OCPlatformBundle:Advert');
	  
	  // On récupère l' entité correspondante à l'id $id
	  $advert = $repository->find($id);
	  
	  if (null === $advert) {
	    throw new NotFoundHttpException("l'annonce d'id".$id."n'existe pas.");
	  }	
      
      // On récupère la liste des candidatures de cette annonce
      $listApplications = $em
	   ->getRepository('OCPlatformBundle:Application')
	   ->findBy(array('advert' => $advert));
	   	   
	  // On récupère maintenant la liste des AdvertSkill
      $listAdvertSkills = $em
      ->getRepository('OCPlatformBundle:AdvertSkill')
      ->findBy(array('advert' => $advert));
	  
	  
	  return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
	  'advert' => $advert,
	  'listApplications' => $listApplications ,
	  'listAdvertSkills' => $listAdvertSkills,
	  ));
	   
	}

    public function addAction(Request $request)
	{
	   
	   // On récupère le service 
	   //$antispam = $this->container->get('oc_platform.antispam');
	   
	   // On part du principe que $text contient le texte d'un message quelconque
	   //$text = '...';
	   //if($antispam->isSpam($text)) {
	   // throw new \Exception('Votre message a été détecté comme spam !'); 
	   //}
	   	   
	   //Gestion d'un formulaire
	   //Si la requete est en POST, c'est que la visiteur a fourni un formulaire
	   
	   // Création de l'entité
	   $advert = new Advert();
	   $advert->setTitle('Recherche développeur Symfony2.');
	   $advert->setAuthor('Alexandre');
	   $advert->setContent('Nous recherchons un développeur Symfony2 débutant sur Lyon.Blabla…');
	   // Date et publication sont des attributs définis automatiquement dans le contstructeur
	   
	   // Création de l'image
	   $image = new Image();
	   $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
	   $image->setAlt('job de rêve');
	   
	   // Création d'un première candidature
	   $application1 = new Application();
	   $application1->setAuthor('Martin');
	   $application1->setContent('J\'ai toutes les qualités requises');
	   
	    // Création d'une deuxième candidature par exemple
       $application2 = new Application();
       $application2->setAuthor('Pierre');
       $application2->setContent("Je suis très motivé.");
	   
	   //On lie les candidatures à l'annonce
	   $application1->setAdvert($advert);
	   $application2->setAdvert($advert);
	   
	   // On lie l'image à l'annonce
	   $advert->setImage($image);	   
	   
	   // On récupère l'EntityManager
	   $em = $this->getDoctrine()->getManager();
	   
	   // On récupère toutes les compétences possibles
	   $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();
	   
	   // Pour chaque compétence
	   foreach($listSkills as $skill) {
	   // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
	    $advertSkill = new AdvertSkill();
		// On la lie à l'annonce, qui est ici toujours la même
		$advertSkill->setAdvert($advert);
		// On la lie à la compétence, qui change ici dans la boucle foreach
		$advertSkill->setSkill($skill);
		// Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
        $advertSkill->setLevel('Expert');
		
		// Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
        $em->persist($advertSkill);
		}
		// Doctrine ne connait pas encore l'entité $advert. Si vous n'avez pas définit la relation AdvertSkill
        // avec un cascade persist (ce qui est le cas si vous avez utilisé mon code), alors on doit persister $advert
        $em->persist($advert);
	   	   
	   
	   // Étape 1 bis : pour cette relation pas de cascade lorsqu'on persiste Advert, car la relation est
       // définie dans l'entité Application et non Advert. On doit donc tout persister à la main ici.
       $em->persist($application1);
       $em->persist($application2);
	   	   
	   // Etape 2: On <<flush>> tout ce qui a été persité avant
	   $em->flush();  
	   
	   
	   if($request->isMethod('POST')) {
	   
		$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
		
		return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
	   }
	   
	   // Si on est pas en POST, alors on affiche le formulaire
	   return $this->render('OCPlatformBundle:Advert:add.html.twig');
	}
    
	public function editAction($id, Request $request)
	{
	
		if($request->isMethod('POST')) {
		  $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifié.');
		  
		  return $this->redirect($this->generateUrl('oc_platform_view', array('id' => 5)));
		
		}
		
		$em = $this->getDoctrine()->getManager();		
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
		
		if(null === $advert) {
		  throw new NotFoundHttpException("L'annonce d'id".$id."n'existe pas.");
		}
		// La méthode findAll retourne toutes les catégories de la base de données
		$listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();
		
		// On boucle sur les catégories pour les liers a l'annonce
		foreach($listCategories as $category) {
		  $advert->addCategory($category);
		}
		
		$em->flush();
		
		//$advert = array(
        //'title'   => 'Recherche développpeur Symfony2',
        //'id'      => $id,
        //'author'  => 'Alexandre',
        //'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
        //'date'    => new \Datetime()
        //);
		
		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
		'advert' => $advert,
		'listCategories' => $listCategories
		));
	}
	
	public function deleteAction($id)
	{
	  $em = $this->getDoctrine()->getManager();
	  $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
	  
	  if (null === $advert)
	  {
	    throw new NotFoundHttpException("L'annonce d'id".$id."n'existe aps.");
	  }
	  // On boucle sur les categories pour les supprimer
	  foreach($advert->getCategories() as $category)
	  {
	    $advert->removeCategory($category);
	  }
	  // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
      // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

      // On déclenche la modification
      $em->flush();
	  
    	return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
	
	public function menuAction() {
	  
	  $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony2'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
      );
	 
	  return $this->render('OCPlatformBundle:Advert:menu.html.twig', array('listAdverts' => $listAdverts));
	  
	}
	
	public function testAction() {
	  
	  $advert = new advert();
	  $advert->setTitle("Recherche développeur");
	  $advert->setAuthor("Joe");
	  $advert->setContent("You make me laugh");
	  
	  $em = $this->getDoctrine()->getManager();
	  $em->persist($advert);
	  $em->flush(); // C'est a ce moment qu'est généré le slug
	  
	  return new response('Slug généré : '.$advert->getSlug());
	
	}
		    
}