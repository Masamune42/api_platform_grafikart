<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

class MeController extends AbstractController
{
    // ON injecte le composant securité
    public function __construct(private Security $security)
    {
    }

    public function __invoke()
    {
        $user = $this->security->getUser();
        return $user;
    }
}
