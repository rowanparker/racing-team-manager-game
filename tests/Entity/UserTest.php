<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\AppApiTestCase;

class UserTest extends AppApiTestCase
{
    public function setUp(): void
    {
        $this->setUpClient();
    }

    public function test_GET_collection_by_logged_out_user_fails(): void
    {
        $this->client->request('GET', '/api/users');
        $this->assertHttpUnauthorized401();
    }

    public function test_GET_collection_by_logged_in_user_fails(): void
    {
        $this->loginAsUser('player1', 'player1');
        $this->client->request('GET', '/api/users');
        $this->assertHttpForbidden403();
    }

    public function test_GET_collection_by_admin_user_succeeds(): void
    {
        $this->loginAsUser('admin', 'admin');
        $this->client->request('GET', '/api/users');
        $this->assertResponseIsSuccessful();
    }

    public function test_GET_item_by_logged_out_user_fails()
    {
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    public function test_GET_item_by_logged_in_user_is_successful_for_self()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }

    public function test_GET_item_by_logged_in_user_is_blocked_for_others()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findIriBy(User::class, ['username'=>'player2']);
        $this->client->request('GET', $iri);
        $this->assertHttpForbidden403();
    }

    public function test_GET_item_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin', 'admin');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }

    public function test_GET_item_matches_schema()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $response = $this->client->request('GET', $iri);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Check sensitive fields are not visible
        $json = json_decode($response->getContent());
        $this->assertObjectNotHasAttribute('salt', $json);
        $this->assertObjectNotHasAttribute('plainPassword', $json);
        $this->assertObjectNotHasAttribute('roles', $json);
    }

    public function test_PUT_is_disabled()
    {
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('PUT', $iri);
        $this->assertResponseStatusCodeSame(405);
    }

    public function test_PATCH_by_logged_out_user_fails()
    {
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('PATCH', $iri);
        $this->assertHttpUnauthorized401();
    }

    public function test_PATCH_by_logged_in_user_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('PATCH', $iri);
        $this->assertHttpForbidden403();
    }

    public function test_PATCH_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin','admin');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('PATCH', $iri, ['json'=>[
            'username' => 'player1000',
        ], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'player1000',
        ]);
    }

    public function test_DELETE_by_logged_out_user_fails()
    {
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    public function test_DELETE_by_logged_in_user_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('DELETE', $iri);
        $this->assertHttpForbidden403();
    }

    public function test_DELETE_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin', 'admin');
        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();

        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->assertNull($iri);
    }

    public function test_POST_succeeds()
    {
        $this->client->disableReboot();

        $this->client->request('POST', '/api/users', ['json' => [
            'username' => 'donkey',
            'plainPassword' => 'kong',
        ]]);

        $this->assertResponseStatusCodeSame(201);

        $this->loginAsUser('donkey', 'kong');
        $this->assertResponseIsSuccessful();

        // Roles are not visible via API, so check internally
        /** @var User $user */
        $user = static::$container->get('doctrine')->getManagerForClass(User::class)
            ->getRepository(User::class)->findOneBy(['username'=>'donkey']);

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function test_POST_with_uppercase_username_succeeds()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'username' => 'DONKEY',
            'plainPassword' => 'kong',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['username' => 'donkey']);
    }

    public function test_POST_with_whitespace_username_succeeds()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'username' => ' donkey ',
            'plainPassword' => 'kong',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['username' => 'donkey']);
    }

    public function test_POST_with_invalid_username_fails()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'username' => ' #a/bc*1~23%',
            'plainPassword' => 'kong',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>'username: Must be alpha-numeric and lower case.']);
    }

    public function test_POST_with_duplicate_username_fails()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'username' => 'player1',
            'plainPassword' => 'player1',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>'username: This value is already used.']);
    }

    public function test_POST_with_empty_username_fails()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'plainPassword' => 'kong',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>'username: User must have a username']);
    }

    public function test_POST_with_empty_password_fails()
    {
        $this->client->request('POST', '/api/users', ['json' => [
            'username' => 'donkey',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>'password: User must have a password']);
    }
}
