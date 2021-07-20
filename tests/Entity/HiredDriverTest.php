<?php

namespace App\Tests\Entity;

use App\Entity\Driver;
use App\Entity\DriverSlot;
use App\Entity\Team;
use App\Tests\AppApiTestCase;

class HiredDriverTest extends AppApiTestCase
{
    protected string $urlApi = '/api/hired_drivers';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * A logged out user can not hire a driver
     */
    public function test_POST_by_logged_out_user_fails()
    {
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can hire a driver if:
     * - the driver slot is empty
     * - they have sufficient credits
     */
    public function test_POST_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player1', 'player1');

        $driverIri = $this->findIriBy(Driver::class, ['name'=>'Jack']);
        $driverSlotIri =$this->findNthSlotIriByUsername('player1', DriverSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'driverSlot' => $driverSlotIri,
            'driver' => $driverIri,
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'driverSlot' => $driverSlotIri,
            'driver' => $driverIri,
        ]);

        // Fixture has 10,000 initial credit balance for player 1
        $iri = $this->findIriBy(Team::class, ['name' => "Player One"]);
        $response = $this->client->request('GET', $iri);
        $this->assertSame(9000, $response->toArray(false)['balanceCredits']);
    }

    /**
     * A logged in user can not hire a driver if all driver slots are full
     */
    public function test_POST_by_logged_in_user_with_full_slot_fails()
    {
        $this->loginAsUser('player3', 'player3');

        $driverIri = $this->findIriBy(Driver::class, ['name'=>'Joe']);
        $driverSlotIri =$this->findNthSlotIriByUsername('player3', DriverSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'driverSlot' => $driverSlotIri,
            'driver' => $driverIri,
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'driverSlot: Driver slot is already full.']);
    }

    /**
     * A logged in user can not hire a driver if they have insufficient credits
     */
    public function test_POST_by_logged_in_user_with_insufficient_credits_fails()
    {
        $this->loginAsUser('player2', 'player2');

        $driverIri = $this->findIriBy(Driver::class, ['name'=>'Jack']);
        $driverSlotIri =$this->findNthSlotIriByUsername('player2', DriverSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'driverSlot' => $driverSlotIri,
            'driver' => $driverIri,
        ]]);

        $message = 'You need more credits to hire Jack.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged in user can not hire a driver the same driver twice
     */
    public function test_POST_by_logged_in_user_already_hired_driver_fails()
    {
        $this->loginAsUser('player5', 'player5');

        $driverIri = $this->findIriBy(Driver::class, ['name'=>'Jack']);

        $driverSlotIri =$this->findNthSlotIriByUsername('player5', DriverSlot::class, 2);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'driverSlot' => $driverSlotIri,
            'driver' => $driverIri,
        ]]);

        $message = 'You have already hired Jack.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged out user can not fire a driver
     */
    public function test_DELETE_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can fire a driver if they have more than one
     */
    public function test_DELETE_by_logged_in_user_with_more_than_one_driver_succeeds()
    {
        $this->loginAsUser('player3','player3');

        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * A logged in user can not fire a drive if they only have one
     */
    public function test_DELETE_by_logged_in_user_with_only_one_driver_fails()
    {
        $this->loginAsUser('player5','player5');

        $user = $this->findUserByUsername('player5');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'You can not fire your last driver.']);
    }

    /**
     * No one can replace a hired driver (remove then create instead)
     */
    public function test_PUT_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertHttpMethodNotAllowed405('PUT', $iri, ['GET','DELETE']);
    }

    /**
     * No one can modify a hired driver
     */
    public function test_PATCH_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

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
     * A logged out user can not view a hired driver
     */
    public function test_GET_item_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view their own hired driver
     */
    public function test_GET_item_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player3', 'player3');

        $user = $this->findUserByUsername('player3');
        $hiredDriver = $user->getTeam()->getDriverSlots()[0]->getHiredDriver();
        $iri = $this->getIriFromItem($hiredDriver);

        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }
}
