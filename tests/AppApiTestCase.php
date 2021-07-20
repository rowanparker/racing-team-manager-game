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

    protected function findNthSlotIriByUsername(string $username, string $slotClass, int $nth = 1): ?string
    {
        $i = $nth - 1;

        /** @var User $user */
        $user = static::$container->get('doctrine')->getManagerForClass(User::class)
            ->getRepository(User::class)->findOneBy(['username'=>$username]);

        if ( ! $user->getTeam()) {
            return null;
        }

        $slots = static::$container->get('doctrine')->getManagerForClass($slotClass)
            ->getRepository($slotClass)->findBy(['team'=>$user->getTeam()->getId()]);

        return (array_key_exists($i, $slots))
            ? static::$container->get('api_platform.iri_converter')->getIriFromItem($slots[$i])
            : null;
    }
}
