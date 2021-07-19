<?php

namespace App\EventSubscriber\User;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * This subscriber encodes the plainPassword value (if supplied) on
 * any writable method (POST, PATCH or PUT).
 */
class EncodePasswordSubscriber implements EventSubscriberInterface
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function encodePassword(ViewEvent $event)
    {
        $user = $event->getControllerResult();

        if ( ! $user instanceof User || $event->getRequest()->isMethodSafe()) {
            return;
        }

        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $user->getPlainPassword()),
            );
            $user->eraseCredentials();
        }
    }
}
