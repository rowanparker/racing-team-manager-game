<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\AppApiTestCase;

class TeamTest extends AppApiTestCase
{
    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * A logged out user can not create a team
     */
    public function test_POST_with_logged_out_user_fails()
    {
        $this->client->request('POST', '/api/teams', ['json'=>[
            'name' => 'My new team',
        ]]);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can create a team if they do not already have one.
     */
    public function test_POST_with_logged_in_user_and_no_existing_team_succeeds()
    {
        $balanceCredits = self::$kernel->getContainer()->getParameter('app.starting_credits');

        $userIri = $this->findIriBy(User::class, ['username'=>'player4']);
        $this->loginAsUser('player4','player4');
        $response = $this->client->request('POST', '/api/teams', ['json'=>[
            'name' => 'My new team',
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'name' => 'My new team',
            'user' => $userIri,
            'balanceCredits' => $balanceCredits,
        ]);

        $this->assertCount(1, $response->toArray()['garageSlots']);
        $this->assertCount(1, $response->toArray()['driverSlots']);
        $this->assertCount(1, $response->toArray()['mechanicSlots']);
    }

    /**
     * A logged in user can not create a team if they already have one.
     */
    public function test_POST_with_logged_in_user_and_existing_team_fails()
    {
        $this->loginAsUser('player2','player2');
        $this->client->request('POST', '/api/teams', ['json'=>[
            'name' => 'My new team',
        ]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>'User already has a team assigned.']);
    }

    /**
     * A logged out user can not view a collection of teams.
     */
    public function test_GET_collection_by_logged_out_user_fails(): void
    {
        $this->client->request('GET', '/api/teams');
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view a collection of teams.
     */
    public function test_GET_collection_by_logged_in_user_succeeds(): void
    {
        $this->loginAsUser('player1', 'player1');
        $this->client->request('GET', '/api/teams');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => 4
        ]);
    }

    /**
     * A logged out user can not view an individual team.
     */
    public function test_GET_item_by_logged_out_user_fails(): void
    {
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view an their own team.
     */
    public function test_GET_item_by_logged_in_user_for_own_team_succeeds()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player1');
        $response = $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();

        $this->assertCount(1, $response->toArray()['garageSlots']);
        $this->assertCount(1, $response->toArray()['driverSlots']);
        $this->assertCount(1, $response->toArray()['mechanicSlots']);
    }

    /**
     * A logged in user can view another team.
     */
    public function test_GET_item_by_logged_in_user_for_other_team_succeeds()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player2');
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * No one can replace a team object.
     */
    public function test_PUT_is_disabled()
    {
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('PUT', $iri);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * A logged out user can not change any team data.
     */
    public function test_PATCH_by_logged_out_user_fails()
    {
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('PATCH', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can change name of their team.
     */
    public function test_PATCH_by_logged_in_user_for_own_team_succeeds()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('PATCH', $iri, ['json' => [
            'name' => 'a new team name'
        ], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'a new team name',
        ]);
    }

    /**
     * A logged in user can not change the name of other teams.
     */
    public function test_PATCH_by_logged_in_user_for_other_team_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player2');
        $this->client->request('PATCH', $iri, ['json' => [
            'name' => 'a new team name'
        ], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertHttpForbidden403();
    }

    /**
     * An admin user can change the name of any team.
     */
    public function test_PATCH_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin', 'admin');
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('PATCH', $iri, ['json' => [
            'name' => 'a new team name'
        ], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'a new team name',
        ]);
    }

    /**
     * A logged out user can not delete a team.
     */
    public function test_DELETE_by_logged_out_user_fails()
    {
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can not delete their own team.
     */
    public function test_DELETE_by_logged_in_user_for_own_team_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player2');
        $this->client->request('DELETE', $iri);
        $this->assertHttpForbidden403();
    }

    /**
     * A logged in user can not delete another team.
     */
    public function test_DELETE_by_logged_in_user_for_other_team_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertHttpForbidden403();
    }

    /**
     * An admin user can delete a team.
     */
    public function test_DELETE_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin', 'admin');
        $iri = $this->findTeamIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();

        $iri = $this->findIriBy(User::class, ['username'=>'player1']);
        $this->assertNull($iri);
    }

    private function findTeamIriByUsername(string $username): ?string
    {
        /** @var User $user */
        $user = static::$container->get('doctrine')->getManagerForClass(User::class)
            ->getRepository(User::class)->findOneBy(['username'=>$username]);

        return ($user->getTeam())
            ? static::$container->get('api_platform.iri_converter')->getIriFromItem($user->getTeam())
            : null;
    }
}
