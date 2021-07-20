<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

abstract class AppApiTestCase extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected Client $client;

    protected function setUpClient(): void
    {
        self::$purgeWithTruncate = false;
        $this->client = static::createClient();
    }

    /**
     * The TEST environment uses the `plaintext` encoder.
     * By convention all TEST user fixtures share the same username and password.
     */
    protected function loginAsUser(string $username, string $password): void
    {
        $iri = $this->findIriBy(User::class, ['username'=>$username]);
        $this->client->disableReboot();

        $this->client->request('POST', '/login', ['json' => [
            'username' => $username,
            'password' => $password,
        ]]);

        $this->assertJsonContains([
            'user' => $iri,
        ]);
    }

    protected function assertHttpUnauthorized401(): void
    {
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['hydra:description'=>'Full authentication is required to access this resource.']);
    }

    protected function assertHttpForbidden403(): void
    {
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains(['hydra:description'=>'Access Denied.']);
    }

    protected function assertHttpNotFound404(): void
    {
        $this->assertResponseStatusCodeSame(404);;
        $this->assertJsonContains(['hydra:description'=>'Not Found']);
    }

    protected function assertHttpMethodNotAllowed405(string $method, string $url, array $allow): void
    {
        $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)',
            $method, $url, implode(', ', $allow)
        );
        $this->assertResponseStatusCodeSame(405);
        $this->assertJsonContains(['hydra:description'=>$message]);
    }

    protected function getIriFromItem($item): string
    {
        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    protected function findUserByUsername(string $username): ?User
    {
        return $this->entityFindOneBy(User::class, ['username'=>$username]);
    }

    protected function entityFindOneBy(string $class, array $criteria)
    {
        return static::$container->get('doctrine')->getManagerForClass($class)
            ->getRepository($class)->findOneBy($criteria);
    }

    protected function entityFindBy(string $class, array $criteria)
    {
        return static::$container->get('doctrine')->getManagerForClass($class)
            ->getRepository($class)->findBy($criteria);
    }

    protected function findNthSlotIriByUsername(string $username, string $slotClass, int $nth = 1): ?string
    {
        $i = $nth - 1;

        $user = $this->findUserByUsername($username);

        if ( ! $user->getTeam()) {
            return null;
        }

        $slots = $this->entityFindBy($slotClass, ['team'=>$user->getTeam()->getId()]);

        return (array_key_exists($i, $slots))
            ? $this->getIriFromItem($slots[$i])
            : null;
    }
}
