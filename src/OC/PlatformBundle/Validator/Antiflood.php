<?php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint 
{
    public $message = "Le Contenu de l'annonce que vous voulez poster contient moins de 3 caractères et est considéré comme flood.";

    public function validateBy()
    {
        return 'oc_platform_antiflood'; //Ici on fait appel à l'alias du service
    }
}