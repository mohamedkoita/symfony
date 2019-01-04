<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Entity\AdvertSkill;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;




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

    // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    $nbPages = ceil(count($listAdverts) / $nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    if ($page > $nbPages) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    // On donne toutes les informations nécessaires à la vue
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages'     => $nbPages,
      'page'        => $page,
    ));
  }


  public function viewAction($id)
  {
    //On recupere l'entity manager
    $em = $this->getDoctrine()->getManager();

    //On récupère l'entité correspondante à l'id $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    //L'action si le id n'existe pas 
     if (null === $advert) {
       throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas");
     }

    //On récupère la liste des candidatures de cette annonce
    $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));


    //On va maintenant récupérer la liste de toutes les compétences liées à l'annonce
    $listAdvertSkill = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert)); 

    //return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert' => $advert,'listApplications' => $listApplications));
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert' => $advert,
      'listApplications' => $listApplications, 
      'listAdvertSkill' => $listAdvertSkill));
  }

  public function addAction(Request $request)
  {
   //On créé un objet Advert
    $advert = new Advert();

    //On créé le form builder grace au service from factory
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::Class, $advert);

    //On ajoute les champs de l'entité que l'on veut à notre formulaire
    $formBuilder
    ->add('date',         DateType::Class)
    ->add('title',        TextType::Class)
    ->add('content',      TextareaType::Class)
    ->add('author',       TextType::Class)
    ->add('author_email', EmailType::Class)
    ->add('published',    CheckboxType::Class)
    ->add('save',         SubmitType::Class)
    ;
    //On génère le formulaire a partir du formBuilder
    $form = $formBuilder->getForm();

    //On vérifie si la requête est un POST
    if ($request->isMethod('POST')){
      //On fait le lien requête <-> formulaire
      //A partir de maintenant la variable $advert contient les données entrées par l'utilisateur via le formulaire

      $form->handleRequest($request);

      //On vérifie la validité des informations entrées dans ele formulaire
      if ($form->isValid()) {
        //On recupère l'entity manager
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('Notice', 'Annonce bien enregistrée !');

        //On redirige vers la page de l'annonce nouvellement créée
        return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId(), 'slug' => $advert->getSlug()));
      }
    }

    //A ce stade on mettra les actions à faire au cas où le formulaire ne serait pas valide
    //Soit parce que la requête est de type GET
    //Soit parce que les données passées au formulaire sont invalides


    

    //On passe la méthode createView du formulaire à la vue
    //Pourqu'elle puisse afficher le formulaire

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('form' => $form->createView(),));



    
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

   //Ici c'est la gestion par le formulaire
   if ($request->isMethod('POST')) {
    $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

    return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
   }   

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert));
  }

  public function deleteAction($id){
   
      $em = $this->getDoctrine()->getManager();
  
      // On récupère l'annonce $id
      $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
  
      if (null === $advert) {
        throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
      }
  
      // On boucle sur les catégories de l'annonce pour les supprimer
      foreach ($advert->getCategories() as $category) {
        $advert->removeCategory($category);
      }
  
      // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
      // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine
  
      // On déclenche la modification
      $em->flush();
    
      return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit)
  {
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
      array(),                 // Pas de critère
      array('date' => 'desc'), // On trie par date décroissante
      $limit,                  // On sélectionne $limit annonces
      0                        // À partir du premier
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => $listAdverts));
  }
}
