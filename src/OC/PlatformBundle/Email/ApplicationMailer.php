<?php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Application;

class ApplicationMailer {

    private $mailer;

    public function __construct(\Swift_Mailer $mailer){
        $this->mailer = $mailer;
    }

    public function sendNewNotification(Application $application) {
        
        //On créé ici le message qui sera envoyé par mail. Le premier argument de la fonction est le 
        //sujet du mail et le deuxieme argument est le contenu du mail
        $message = new \Swift_Message('Nouvelle Candidature', 'Vous avez recu une nouvelle candidature');

        //On ajoute au message les paramètres tels que l'email du destinataire et de l'envoyeur
        $message->addTo($application->getAdvert()->getAuthorEmail())->addFrom('admin@advert.com');

        //Maintenant on envoie le mail
        $this->mailer->send($message);
    }
}