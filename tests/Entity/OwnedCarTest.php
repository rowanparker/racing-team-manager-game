<?php

namespace App\Tests\Entity;

use App\Entity\Car;
use App\Entity\GarageSlot;
use App\Entity\Team;
use App\Tests\AppApiTestCase;

class OwnedCarTest extends AppApiTestCase
{
    protected string $urlApi = '/api/owned_cars';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * A logged out user can not buy a car
     */
    public function test_POST_by_logged_out_user_fails()
    {
        $this->client->request('POST', $this->urlApi, ['json' => []]);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can buy a car if:
     * - the car slot is empty
     * - they have sufficient credits
     */
    public function test_POST_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player1', 'player1');

        $carIri = $this->findIriBy(Car::class, ['make'=>'Hyundai']);
        $garageSlotIri =$this->findNthSlotIriByUsername('player1', GarageSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'garageSlot' => $garageSlotIri,
            'car' => $carIri,
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'garageSlot' => $garageSlotIri,
            'car' => $carIri,
        ]);

        // Fixture has 10,000 initial credit balance for player 1
        $iri = $this->findIriBy(Team::class, ['name' => "Player One"]);
        $response = $this->client->request('GET', $iri);
        $this->assertSame(9400, $response->toArray(false)['balanceCredits']);
    }

    /**
     * A logged in user can not buy a car if all car slots are full
     */
    public function test_POST_by_logged_in_user_with_full_slot_fails()
    {
        $this->loginAsUser('player3', 'player3');

        $carIri = $this->findIriBy(Car::class, ['make'=>'Lexus']);
        $garageSlotIri =$this->findNthSlotIriByUsername('player3', GarageSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'garageSlot' => $garageSlotIri,
            'car' => $carIri,
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'garageSlot: Garage slot is already full.']);
    }

    /**
     * A logged in user can not buy a car if they have insufficient credits
     */
    public function test_POST_by_logged_in_user_with_insufficient_credits_fails()
    {
        $this->loginAsUser('player2', 'player2');

        $carIri = $this->findIriBy(Car::class, ['make'=>'Hyundai']);
        $garageSlotIri =$this->findNthSlotIriByUsername('player2', GarageSlot::class);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'garageSlot' => $garageSlotIri,
            'car' => $carIri,
        ]]);

        $message = 'You need more credits to buy a Hyundai, Excel (X3), 1994.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged in user can not buy the same car twice
     */
    public function test_POST_by_logged_in_user_already_bought_car_fails()
    {
        $this->loginAsUser('player5', 'player5');

        $carIri = $this->findIriBy(Car::class, ['make'=>'Hyundai']);

        $garageSlotIri =$this->findNthSlotIriByUsername('player5', GarageSlot::class, 2);
        $this->client->request('POST', $this->urlApi, ['json' => [
            'garageSlot' => $garageSlotIri,
            'car' => $carIri,
        ]]);

        $message = 'You have already bought a Hyundai, Excel (X3), 1994.';
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => $message]);
    }

    /**
     * A logged out user can not sell a car
     */
    public function test_DELETE_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('DELETE', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can sell a car if they have more than one
     */
    public function test_DELETE_by_logged_in_user_with_more_than_one_car_succeeds()
    {
        $this->loginAsUser('player3','player3');

        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('DELETE', $iri);
        $this->assertResponseIsSuccessful();
    }

    /**
     * A logged in user can not sell a car if they only have one
     */
    public function test_DELETE_by_logged_in_user_with_only_one_car_fails()
    {
        $this->loginAsUser('player5','player5');

        $user = $this->findUserByUsername('player5');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'You can not sell your last car.']);
    }

    /**
     * No one can replace a bought car (remove then create instead)
     */
    public function test_PUT_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertHttpMethodNotAllowed405('PUT', $iri, ['GET','DELETE']);
    }

    /**
     * No one can modify a bought car
     */
    public function test_PATCH_is_disabled()
    {
        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertHttpMethodNotAllowed405('PATCH', $iri, ['GET','DELETE']);
    }

    /**
     * No one can view a collection of all bought drives
     */
    public function test_GET_collection_is_disabled()
    {
        $this->client->request('GET', $this->urlApi);
        $this->assertHttpMethodNotAllowed405('GET', $this->urlApi, ['POST']);
    }

    /**
     * A logged out user can not view a bought car
     */
    public function test_GET_item_by_logged_out_user_fails()
    {
        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('GET', $iri);
        $this->assertHttpUnauthorized401();
    }

    /**
     * A logged in user can view their own bought car
     */
    public function test_GET_item_by_logged_in_user_succeeds()
    {
        $this->loginAsUser('player3', 'player3');

        $user = $this->findUserByUsername('player3');
        $ownedCar = $user->getTeam()->getGarageSlots()[0]->getOwnedCar();
        $iri = $this->getIriFromItem($ownedCar);

        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
    }
}
