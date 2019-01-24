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
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;





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

    //On va ramener le nom de l'utilisateur courant
    $user = $this->getUser();
    
    // On donne toutes les informations nécessaires à la vue
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages'     => $nbPages,
      'page'        => $page,
      'user'        => $user,
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

  /**
   * @Security("has_role('ROLE_AUTEUR')")
  */
  public function addAction(Request $request)
  {
    $advert = new Advert();
    $form   = $this->get('form.factory')->create(AdvertType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($advert);
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId(), 'slug'=>$advert->getSlug()));
    }

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  /**
   * @Security("has_role('ROLE_AUTEUR')")
  */
  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    //On passe la variable qu'on a créé au formulaire
    $form = $this->createForm(AdvertEditType::class, $advert);


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

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
        //On redirige vers la page de l'annonce nouvellement modifiée
        return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId(), 'slug' => $advert->getSlug()));
      }
    }
   return $this->render('OCPlatformBundle:Advert:edit.html.twig', array('form' => $form->createView(), 'advert' => $advert));

  }

  /**
   * @Security("has_role('ROLE_AUTEUR')")
  */
  public function deleteAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($advert);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

      return $this->redirectToRoute('oc_platform_home');
    }
    
    return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
      'advert' => $advert,
      'form'   => $form->createView(),
    ));
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

  public function testAction()
  {
    //On créé une nouvelle annonce
    $advert = new Advert();

    $advert->setTitle('Lorem'); //On insère un titre 
    //$advert->setContent('Lo'); //On insère le contenu dans l'annonce
    $advert->setDate(New \DateTime()); //On met la date
    $advert->setAuthor('Lorem Lorem Lorem'); //On insère le nom de l'auteur
    $advert->setAuthorEmail('ju@ju.vb'); //On insère l'email

    $validator = $this->get('validator');
    $listErrors = $validator->validate($advert);

    if (count($listErrors) > 0)
    {
      //$listErrors est un objet 
      return new Response((string) $listErrors);
    } else {
      return new Response('L\'annonce est valide !');
    }
  }
}
