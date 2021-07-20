<?php

namespace App\Tests\Entity;

use App\Entity\Mechanic;
use App\Entity\MechanicSlot;
use App\Entity\Team;
use App\Tests\AppApiTestCase;

class HiredMechanicTest extends AppApiTestCase
{
    protected string $urlApi = '/api/hired_mechanics';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * A logged out user can not hire a mechanic
     */
    public function test_POST_by_logged_out_user_fails()
    {
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can hire a mechanic if:
     * - the mechanic slot is empty
     * - they have sufficient credits
     */
    public function test_POST_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player1', 'player1');

        $mechanicIri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $mechanicSlotIri =$this->findNthSlotIriByUsername('player1', MechanicSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'mechanicSlot' => $mechanicSlotIri,
            'mechanic' => $mechanicIri,
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'mechanicSlot' => $mechanicSlotIri,
            'mechanic' => $mechanicIri,
        ]);

        // Fixture has 10,000 initial credit balance for player 1
        $iri = $this->findIriBy(Team::class, ['name' => "Player One"]);
        $response = $this->client->request('GET', $iri);
        $this->assertSame(9600, $response->toArray(false)['balanceCredits']);
    }

    /**
     * A logged in user can not hire a mechanic if all mechanic slots are full
     */
    public function test_POST_by_logged_in_user_with_full_slot_fails()
    {
        $this->loginAsUser('player3', 'player3');

        $mechanicIri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $mechanicSlotIri =$this->findNthSlotIriByUsername('player3', MechanicSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'mechanicSlot' => $mechanicSlotIri,
            'mechanic' => $mechanicIri,
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'mechanicSlot: Mechanic slot is already full.']);
    }

    /**
     * A logged in user can not hire a mechanic if they have insufficient credits
     */
    public function test_POST_by_logged_in_user_with_insufficient_credits_fails()
    {
        $this->loginAsUser('player2', 'player2');

        $mechanicIri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $mechanicSlotIri =$this->findNthSlotIriByUsername('player2', MechanicSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'mechanicSlot' => $mechanicSlotIri,
            'mechanic' => $mechanicIri,
        ]]);

        $message = 'You need more credits to hire Bob.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged in user can not hire a mechanic the same mechanic twice
     */
    public function test_POST_by_logged_in_user_already_hired_mechanic_fails()
    {
        $this->loginAsUser('player5', 'player5');

        $mechanicIri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);

        $mechanicSlotIri =$this->findNthSlotIriByUsername('player5', MechanicSlot::class, 2);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'mechanicSlot' => $mechanicSlotIri,
            'mechanic' => $mechanicIri,
        ]]);

        $message = 'You have already hired Bob.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged out user can not fire a mechanic
     */
    public function test_DELETE_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can fire a mechanic if they have more than one
     */
    public function test_DELETE_by_logged_in_user_with_more_than_one_mechanic_succeeds()
    {
        $this->loginAsUser('player3','player3');

        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * A logged in user can not fire a drive if they only have one
     */
    public function test_DELETE_by_logged_in_user_with_only_one_mechanic_fails()
    {
        $this->loginAsUser('player5','player5');

        $user = $this->findUserByUsername('player5');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'You can not fire your last mechanic.']);
    }

    /**
     * No one can replace a hired mechanic (remove then create instead)
     */
    public function test_PUT_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertHttpMethodNotAllowed405('PUT', $iri, ['GET','DELETE']);
    }

    /**
     * No one can modify a hired mechanic
     */
    public function test_PATCH_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertHttpMethodNotAllowed405('PATCH', $iri, ['GET','DELETE']);
    }

    /**
     * No one can view a collection of all hired drives
     */
    public function test_GET_collection_is_disabled()
    {
        $this->client->request('GET', $this->urlApi);
        $this->assertHttpMethodNotAllowed405('GET', $this->urlApi, ['POST']);
    }

    /**
     * A logged out user can not view a hired mechanic
     */
    public function test_GET_item_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view their own hired mechanic
     */
    public function test_GET_item_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player3', 'player3');

        $user = $this->findUserByUsername('player3');
        $hiredMechanic = $user->getTeam()->getMechanicSlots()[0]->getHiredMechanic();
        $iri = $this->getIriFromItem($hiredMechanic);

        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }
}
