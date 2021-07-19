<?php

namespace App\EventSubscriber\Team;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Team;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This subscriber ensures that Team entities are created with the correct credit balance.
 */
class SetStartingCreditBalanceSubscriber implements EventSubscriberInterface
{
    private ContainerBagInterface $params;

    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setStartingCreditBalanceSubscriber', EventPriorities::PRE_WRITE],
        ];
    }

    public function setStartingCreditBalanceSubscriber(ViewEvent $event)
    {
        $team = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ( ! $team instanceof Team || Request::METHOD_POST !== $method) {
            return;
        }

        $balanceCredits = $this->params->get('app.starting_credits');
        $team->setBalanceCredits($balanceCredits);
    }
}
