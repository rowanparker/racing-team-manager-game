<?php

namespace App\Tests\Entity;

use App\Entity\Car;
use App\Tests\AppApiTestCase;

class CarTest extends AppApiTestCase
{
    protected string $urlApi = '/api/cars';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * No one can create a car
     */
    public function test_POST_is_disabled()
    {
        $this->client->request('POST', $this->urlApi);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can replace a car
     */
    public function test_PUT_is_disabled()
    {
        $iri = $this->findIriBy(Car::class, [
            'make' => 'Hyundai',
            'model' => 'Excel (X3)',
        ]);
        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can edit a car
     */
    public function test_PATCH_is_disabled()
    {
        $iri = $this->findIriBy(Car::class, [
            'make' => 'Hyundai',
            'model' => 'Excel (X3)',
        ]);
        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * Anyone can view a collection of cars
     */
    public function test_GET_collection_succeeds()
    {
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertResponseIsSuccessful();
        $this->assertCount(4, $response->toArray()['hydra:member']);
    }

    /**
     * Anyone can view a single car
     */
    public function test_GET_item_succeeds()
    {
        $iri = $this->findIriBy(Car::class, [
            'make' => 'Hyundai',
            'model' => 'Excel (X3)',
        ]);
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'make' => 'Hyundai',
            'model' => 'Excel (X3)',
            'year' => 1994,
            'marketPrice' => 600,
        ]);
    }
}
