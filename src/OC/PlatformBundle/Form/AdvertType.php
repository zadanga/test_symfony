<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',      'date')
            ->add('title',     'text')
            ->add('author',    'text')
            ->add('content',   'textarea')
            ->add('image',     new ImageType(), array('required' => false))
			->add('categories', 'entity', array(
			  'class'    => 'OCPlatformBundle:Category',
			  'property' => 'name',
			  'multiple' => true ,
			  'expanded' => false
			  ))
			->add('valider',      'submit') 
			;
	    
		// On ajoute une fonction qui va �couter l' �v�nement PRE_SET_DATA
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA, // 1er argument : l'�v�nement qui nous int�resse ici, PRE_SET_DATA
		  function(FormEvent $event) { // 2em argument : la function � ex�cuter lorsque l'�v�nement est d�clanch�
		    // On r�cup�re notre objet Advert sous-jacent
			$advert = $event->getData();
			
			if(null === $advert) {
			  return; // On sort de la fonction sans rien faire lorsque $advert vaut null
			}
			
			if(!$advert->getPublished() || null === $advert->getId()) {
			// Si l'annonce n'est pas publi�e, ou si elle n'existe pas encore en base de donn�e (id est null)
			// alors on ajoute le champ published
			  $event->getform()->add('published', 'checkbox', array('required' => false));
			} else {
			  // Sinon, on le supprime
			  $event->getForm()->remove('published');
			}
		  }
		);
	}
	/*
	* Exemple: Champ de formulaire => collection
    * Rappel :
    ** - 1er argument : nom du champ, ici � categories �, car c'est le nom de l'attribut
    ** - 2e argument : type du champ, ici � collection � qui est une liste de quelque chose
    ** - 3e argument : tableau d'options du champ
    *		
 	*       ->add('categories', 'collection', array(
	*		  'type'         => new CategoryType(),
	*		  'allow_add'    => true,
	*		  'allow_delete' => true
	*		  ))
	*/	
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oc_platformbundle_advert';
    }
}
