<?php

namespace App\Tests\Entity;

use App\Entity\Team;
use App\Tests\AppApiTestCase;

abstract class AbstractSlotTest extends AppApiTestCase
{
    protected string $urlApi;
    protected string $humanName;
    protected string $resourceClass;

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * A logged out user can not create a slot
     */
    public function test_POST_by_logged_out_user_fails()
    {
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can create a slot with sufficient credits and free spaces
     */
    public function test_POST_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player1', 'player1');
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertResponseIsSuccessful();

        // Fixture has 1 initial slot for player 1
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertCount(2, $response->toArray()['hydra:member']);

        // Fixture has 10,000 initial credit balance for player 1
        $iri = $this->findIriBy(Team::class, ['name' => "Player One's Team"]);
        $response = $this->client->request('GET', $iri);
        $this->assertSame(5000, $response->toArray(false)['balanceCredits']);
    }

    /**
     * A logged in user can not create a slot with insufficient credits
     */
    public function test_POST_by_logged_in_user_with_insufficient_credits_fails()
    {
        $message = sprintf('You need more credits to purchase a %s slot.', $this->humanName);
        $this->loginAsUser('player2', 'player2');
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>$message]);
    }

    /**
     * A logged in user can not create a slot with no free spaces
     */
    public function test_POST_by_logged_in_user_with_no_free_spaces_fails()
    {
        $message = sprintf('You have already unlocked all your %s slots.', $this->humanName);
        $this->loginAsUser('player3', 'player3');
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description'=>$message]);
    }

    /**
     * A logged out user can not view the slot collection
     */
    public function test_GET_collection_by_logged_out_user_fails()
    {
        $this->client->request('GET', $this->urlApi);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged out in can only view their own slots
     */
    public function test_GET_collection_by_logged_in_user_succeeds()
    {
        // Fixture has 1 initial slot for player 1
        $this->loginAsUser('player1', 'player1');
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $response->toArray()['hydra:member']);

        // Fixture has 3 initial slots for player 3
        $this->loginAsUser('player3', 'player3');
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $response->toArray()['hydra:member']);
    }

    /**
     * A logged out user can not view a specific slot
     */
    public function test_GET_item_by_logged_out_user_fails()
    {
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view their own slot
     */
    public function test_GET_item_by_logged_in_user_for_self_succeeds()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * A logged in user can not view other team's slots
     */
    public function test_GET_item_by_logged_in_user_for_other_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findNthSlotIriByUsername('player3');
        $this->client->request('GET', $iri);
        $this->assertHttpNotFound404();
    }

    /**
     * A slot can never be modified, only created or deleted.
     */
    public function test_PATCH_is_disabled()
    {
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertHttpMethodNotAllowed405('PATCH', $iri, ['GET','DELETE']);
    }

    /**
     * A logged out user can not delete a slot.
     */
    public function test_DELETE_by_logged_out_user_fails()
    {
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can not delete their own slots.
     */
    public function test_DELETE_by_logged_in_user_for_self_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertHttpForbidden403();
    }

    /**
     * A logged in user can not delete another team's slots.
     */
    public function test_DELETE_by_logged_in_user_for_other_user_fails()
    {
        $this->loginAsUser('player1', 'player1');
        $iri = $this->findNthSlotIriByUsername('player3');
        $this->client->request('DELETE', $iri);
        $this->assertHttpNotFound404();
    }

    /**
     * An admin user can delete a driver slot.
     */
    public function test_DELETE_by_admin_user_succeeds()
    {
        $this->loginAsUser('admin', 'admin');
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * No one can replace a driver slot object
     */
    public function test_PUT_is_disabled()
    {
        $iri = $this->findNthSlotIriByUsername('player1');
        $this->client->request('PUT', $iri);
        $this->assertResponseStatusCodeSame(405);
    }
}
