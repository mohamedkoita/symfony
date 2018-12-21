<?php

namespace OC\PlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Table(name="oc_advert")
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Repository\AdvertRepository")
 */
class Advert
{

  /**
   * ORM\ManyToOne(targetEntity="OC\PlatformBundle\Entity\Applications", mappedBy="Advert")
   */
  private $applications;

  /**
   * @ORM\ManyToMany(targetEntity="OC\PlatformBundle\Entity\Category", cascade={"persist"})
   * @ORM\JoinTable(name="oc_advert_category")
  */
  private $categories;

  //Etant donné que la propriété categories est un arrayCollection il va falloir le déclarer dans 
  //le constructeur
  
  public function __construct() {
    $this->date = new \Datetime();
    $this->categories = new ArrayCollection();
    $this->applications = new ArrayCollection();
  }

  //On définit le setter. Ici on ajoute une catégorie à la fois 
  //Donc ici on va mettre category en singulier

  public function addCategory(Category $category) {
    $this->categories[] = $category;
  }

  //On définit maintenant la méthode pour supprimer les catégories

  public function removeCategory(Category $category){
    $this->categories->removeElement($category);
  }

  //On définit maintenant le getter. Celui ci ne sera pas au singulier car il doit ramener toutes
  //les categories de l'annonce
  public function getCategories() {
    return $this->categories;
  }


  /**
   * @ORM\OneToOne(targetEntity="OC\PlatformBundle\Entity\Image", cascade={"persist"})
  */
  private $image;

  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date", type="datetime")
   */
  private $date;

  /**
   * @var string
   *
   * @ORM\Column(name="title", type="string", length=255)
   */
  private $title;

  /**
   * @var string
   *
   * @ORM\Column(name="author", type="string", length=255)
   */
  private $author;

  /**
   * @var string
   *
   * @ORM\Column(name="content", type="string", length=255)
   */
  private $content;

  /**
   * @var boolean
   * 
   * @ORM\Column(name="published", type="boolean")
   */
  private $published = true;


  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param \DateTime $date
   */
  public function setDate($date)
  {
    $this->date = $date;
  }

  /**
   * @return \DateTime
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * @param string $author
   */
  public function setAuthor($author)
  {
    $this->author = $author;
  }

  /**
   * @return string
   */
  public function getAuthor()
  {
    return $this->author;
  }

  /**
   * @param string $content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }


    /**
     * Set published.
     *
     * @param bool $published
     *
     * @return Advert
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set image.
     *
     * @param \OC\PlatformBundle\Entity\Image|null $image
     *
     * @return Advert
     */
    public function setImage(\OC\PlatformBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return \OC\PlatformBundle\Entity\Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    public function addApplication(Application $application)
    { 
      //On ajoute l'application à la liste d'applications
      $this->applications[] = $application;

      //On lie l'annonce à la candidature
      $application->setAdvert($this);

    }
  
    public function removeApplication(Application $application)
    {
      $this->applications->removeElement($application);
    }
  
    public function getApplications()
    {
      return $this->applications;
    }
}