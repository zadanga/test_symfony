<?php
// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    // Ici je fixe le nombre d'annonces par page à 3
    // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
	$nbPerPage = 3;
	// On récupère notre objet Paginator
    $listAdverts = $this->getDoctrine()
      ->getManager()
      ->getRepository('OCPlatformBundle:Advert')
      ->getAdverts($page, $nbPerPage)
    ;
	
	// On calcule le nombre total de page grâce au count($listAdverts) qui retourne le nombre total d'annonces
	$nbPages = ceil(count($listAdverts)/$nbPerPage);
	
	// Si la page n'existe pas, on retourne une 404
	if($page > $nbPages) {
	  throw $this->createNotFoundException("La page ".$page." n'existe pas."); 
	}
	
    // On donne toutes les informations à la vue
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
	  'nbPages'     => $nbPages,
	  'page'        => $page
    ));
  }

  public function viewAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();

    // Pour récupérer une annonce unique : on utilise find()
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    // On vérifie que l'annonce avec cet id existe bien
    if ($advert === null) {
      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On récupère la liste des advertSkill pour l'annonce $advert
    $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findByAdvert($advert);
 
     // On récupère la liste des candidatures de cette annonce
    $listApplications = $em
      ->getRepository('OCPlatformBundle:Application')
      ->findBy(array('advert' => $advert))
    ;

 
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert'           => $advert,
      'listAdvertSkills' => $listAdvertSkills,
	  'listApplications' => $listApplications,
    ));
  }
  /**
  * @Security("has_role('ROLE_USER')")
  */
  public function addAction(Request $request)
  {
    // On crée un objet Advert
	$advert = new Advert();
	// Version courte: $form = $this->createForm(new AdvertType(), $advert).
	$form = $this->get('form.factory')->create(new AdvertType(), $advert);
	  
	// On fait le lien Requête <-> Formulaire
    // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
	// On vérifie que les valeurs entrées sont correctes
    // (Nous verrons la validation des objets en détail dans le prochain chapitre)
	if($form->handleRequest($request)->isValid()) {
	 	 
	 // On l'enregistre notre objet $advert dans la base de données, par exemple
	  $em = $this->getDoctrine()->getManager();
	  $em->persist($advert);
	  $em->flush();
	  
	  $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
	  
	  // On redirige vers la page de visualisation de l'annonce nouvellement crée
	  return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
	  
	  }
		
	// À ce stade, le formulaire n'est pas valide car :
    // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
    // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
      return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
        'form' => $form->createView(),
      ));
	}

  public function editAction($id, Request $request)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();

    // On récupère l'entité correspondant à l'id $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
	
	// Si l'annonce n'existe pas, on affiche une erreur 404
    if ($advert == null) {
      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
    }

    // Ici, on s'occupera de la création et de la gestion du formulaire
	$form = $this->createForm(new AdvertEditType(), $advert);
		
    	
	if($form->handleRequest($request)->isValid()) {
	 // On l'enregistre notre objet $advert dans la base de données, par exemple
	  $em->flush();	 
	  
	  $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
	    
	  // On redirige vers la page de visualisation de l'annonce nouvellement crée
	  return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
	}
	
	return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert,
	  'form' => $form->createView()
    ));
  }

  public function deleteAction($id, Request $request)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();

    // On récupère l'entité correspondant à l'id $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    // Si l'annonce n'existe pas, on affiche une erreur 404
    if ($advert == null) {
      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
    }
	
	// On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
	$form = $this->createFormBuilder()->getForm();
	

    if ($form->handleRequest($request)->isValid()) {
      $em->remove($advert);
	  $em->flush();
	  
      $request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');

      // Puis on redirige vers l'accueil
      return $this->redirect($this->generateUrl('oc_platform_home'));
    }

    // Si la requête est en GET, on affiche une page de confirmation avant de delete
    return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
      'advert' => $advert,
	  'form'   => $form->createView()
    ));
  }

  public function menuAction($limit = 3)
  {
    $listAdverts = $this->getDoctrine()
      ->getManager()
      ->getRepository('OCPlatformBundle:Advert')
      ->findBy(
        array(),                 // Pas de critère
        array('date' => 'desc'), // On trie par date décroissante
        $limit,                  // On sélectionne $limit annonces
        0                        // À partir du premier
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => $listAdverts
    ));
  }
  
  public function createAction() {
    
	$advert = new Advert();
    $advert->setTitle('Mission pour Commercial.');
    $advert->setAuthor('Martial');
    $advert->setContent("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat…");
	 
	$em = $this->getDoctrine()->getManager();
	$em->persist($advert);
	$em->flush();
	
	return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));

	}

  public function translationAction($name)
  {
    return $this->render('OCPlatformBundle:Advert:translation.html.twig', array(
      'name' => $name
    ));
  }

  /**
  * @ParamConverter("json")
  */
  public function ParamConverterAction($json)
  {
    return new Response(print_r($json, true));
  }

}	
	