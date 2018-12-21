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




class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    // Notre liste d'annonce en dur
    /*$listAdverts = array(
      array(
        'title'   => 'Recherche développpeur Symfony',
        'id'      => 1,
        'author'  => 'Alexandre',
        'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
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
    );*/
    $repository = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert');
    $listAdverts = $repository->myFindAll();

    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
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
    //$listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));
    $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));


    //On va maintenant récupérer la liste de toutes les compétences liées à l'annonce
    $listAdvertSkill = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert)); 

    //return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert' => $advert,'listApplications' => $listApplications));
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert' => $advert,'listApplications' => $listApplications, 'listAdvertSkill' => $listAdvertSkill));
  }

  public function addAction(Request $request)
  {
   //On créé une annonce
    $advert = new Advert;
    $advert->setTitle('Recherche d\'un développeur Senior Java ');
    $advert->setContent('Une grande structure de la place recherche un développeur expérimenté JAVA afin de l\'intégrer à un projet Urgent !!');
    $advert->setAuthor('Smile Côte d\'Ivoire');

    //On créé des applications à cette annonce
    $application1 = new Application;
    $application1->setAuthor('Koffi Steve');
    $application1->setContent('Je suis un développeur Java confirmé avec de plus de 15 années d\'expérience dans le domaine');
    

    $application2 = new Application;
    $application2->setAuthor('Aka Lambelin');
    $application2->setContent('Je suis certifié en développement Java niveau 8 ');
    

    //On lie les candidatures aux annonces
    //$application1->setAdvert($advert);
    //$application2->setAdvert($advert);
    $advert->AddApplication($application1);
    $advert->AddApplication($application2);


    //On déclare l'entity manager
    $em = $this->getDoctrine()->getManager();

    
    
    //On va maintenant rajouter les compétences nécessaires à toutes les annonces

   /* //On récupère ici toutes les compétences possibles
    $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

    //On insère toutes les compétences dans l'annonce qu'on vient de créer
    foreach ($listSkills as $skill) {

      //On instancie AdvertSkill
      $advertSkill = new AdvertSkill;

      //On y associe l'annonce qu'on a créé
      $advertSkill->setAdvert($advert);

      //On y associe une compétence 
      $advertSkill->setSkill($skill);

      //On y associe le level 
      $advertSkill->setLevel('Expert');

      //On persiste l'entité
      $em->persist($advertSkill);
    } */




    //On persiste l'annonce créée
    $em->persist($advert);

    //On persiste les deux candidatures posées car il n'y a pas de persistance en cascade ici
    $em->persist($application1);
    $em->persist($application2);

    //On valide tout ce qui à été persisté
    $em->flush();


    // Reste de la méthode qu'on avait déjà écrit
    if ($request->isMethod('POST')) {
    $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

    // Puis on redirige vers la page de visualisation de cettte annonce
    return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
    
    
    

     /*// Si la requête est en POST, c'est que le visiteur a soumis le formulaire
     if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // Puis on redirige vers la page de visualisation de cettte annonce
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));*/
  }

  public function editAction($id, Request $request)
  {
    /*if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => 5));
    }

    $advert = array(
      'title'   => 'Recherche développpeur Symfony',
      'id'      => $id,
      'author'  => 'Alexandre',
      'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
      'date'    => new \Datetime()
    );

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert
    ));*/
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // La méthode findAll retourne toutes les catégories de la base de données
    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // Étape 2 : On déclenche l'enregistrement
    $em->flush();
    

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert));




  }

  public function deleteAction($id)
  {
   
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
    //return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit)
  {
    // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
    $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }
}
