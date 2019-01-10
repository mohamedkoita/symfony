<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Vich\UploaderBundle\Form\Type\VichImageType;




class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Arbitrairement on va chercher les categories qui commencent par un D
        $pattern = "D%";

        $builder
        ->add('date',         DateType::Class)
        ->add('title',        TextType::Class)
        ->add('content',      TextareaType::Class)
        ->add('author',       TextType::Class)
        ->add('author_email', EmailType::Class)
        ->add('image',        ImageType::Class, array('required' => false))
        ->add('categories',     EntityType::Class, array(
            'class'   => 'OCPlatformBundle:Category',
            'choice_label'    => 'name',
            'multiple' => true,
            'expanded' => true,
            /*'query_builder' => function(CategoryRepository $repository) use($pattern) {
                return $repository->getLikeQueryBuilder($pattern);
            }*/
        ))
        ->add('save',         SubmitType::Class)
        ;
        
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,    // 1er argument : L'évènement qui nous intéresse : ici, PRE_SET_DATA
            function(FormEvent $event) { // 2e argument : La fonction à exécuter lorsque l'évènement est déclenché
              // On récupère notre objet Advert sous-jacent
              $advert = $event->getData();
      
              // Cette condition est importante, on en reparle plus loin
              if (null === $advert) {
                return; // On sort de la fonction sans rien faire lorsque $advert vaut null
              }
      
              // Si l'annonce n'est pas publiée, ou si elle n'existe pas encore en base (id est null)
              if (!$advert->getPublished() || null === $advert->getId()) {
                // Alors on ajoute le champ published
                $event->getForm()->add('published', CheckboxType::class, array('required' => false));
              } else {
                // Sinon, on le supprime
                $event->getForm()->remove('published');
              }
            }
          );

    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oc_platformbundle_advert';
    }


}
