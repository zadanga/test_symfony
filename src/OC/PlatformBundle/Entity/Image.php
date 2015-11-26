<?php
// src/OC/PlatformBundle/Entity/Image

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Entity\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;

	
	private $file;
	
	// Attribut pour stocker temporairement le nom du fichier
	private $tempFilename;
	
	
	public function getFile()
	{
	  return $this->file;
	}
	
	// On modifie le setter de File,pour prendre en compte l'upload d'un fichier lorqu'il en exite déja un autre
	public function setFile(UploadedFile $file = null)
	{
	  $this->file = $file;
	  
	  //On vérifie si on avait déja un fichier pour cette entité
	  if(null !== $this->url) {
	    $this->tempFilename = $this->url;
		
		// On réinitialise les valeurs des attributs 'url' et 'alt'
		$this->url = null;
		$this->alt = null;		
	  }
	}
	
	/**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }
	
	
	/**
	* @ORM\PrePersist()
	* @ORM\PreUpdate()
	*/
	public function preUpload()
	{
	  // Si il n'y a pas de fichier (Champ facultatif)
	  if(null === $this->file) {
	    return ;
	  }
	  
      // Le nom du fichier est son id, on doit juste stocker également son extension
      // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url »
	  $this->url = $this->file->guessExtension();
	  
	  // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
	  $this->alt = $this->file->getClientOriginalName();	
	}
	
    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()
    */	
	public function upload() 
	{
	  // Si il n'y a pas de fichier (champs facultatif), on ne fait rien
	  if(null == $this->file) {
	   return;
	  }
	  
	  // Si on avait un ancien fichier, on le supprime
	  if(null !== $this->tempFilename) {
	    $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempFilename;
		if(file_exists($oldFile)) {
		  unlink($oldFile);
		}
	  }
	  
	  // On déplace le fichier envoyé dans le repertoire de notre choix
	  $this->file->move(
	    $this->getUploadRootDir(), // Répertoire de destination
	    $this->id.'.'.$this->url   // Nom du fichier a crée, ici "id.extension"
	  );
	}
	
	/**
	* @ORM\PreRemove
	*/
	public function preRemoveUpload() 
	{
	  // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
	  $this->tempFilename = $this->getUploadRootDir.'/'.$this->id.'.'.$this->url;
	}

	/**
	* @ORM\PostRemove
	*/
	public function removeUpload(){
	  // En "PostRemove" on n'a pas accès à l'id, on utilise le nom sauvegardé
	  if(file_exists($this->tempFilename)) {
	    unlink($this->tempFilename);
	  }
	}
	
	public function getUploadDir()
	{
	 // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
	  return 'uploads/img';
	}
	
	public function getUploadRootDir()
	{
	 // On retourne le chemin relatif vers l'image pour notre code PHP
	  return __DIR__.'/../../../../web/'.$this->getUploadDir();
	}
	
	public function getWebPath()
	{
	  return $this->getUploadDir().'/'.$this->getId().'.'.$this->getUrl();
	}
}
