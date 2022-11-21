<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function onLogoutEvent(LogoutEvent $event): void
    {
        // Si on a 'application/json' dans le tableau des requêtes que l'on accepte
        if (in_array('application/json', $event->getRequest()->getAcceptableContentTypes())) {
            // On précise qu'il n'y a pas de contenu à la déconnexion
            $event->setResponse(new JsonResponse(null, Response::HTTP_NO_CONTENT));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
