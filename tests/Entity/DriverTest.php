<?php

namespace App\Tests\Entity;

use App\Entity\Driver;
use App\Tests\AppApiTestCase;

class DriverTest extends AppApiTestCase
{
    protected string $urlApi = '/api/drivers';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * No one can create a driver
     */
    public function test_POST_is_disabled()
    {
        $this->client->request('POST', $this->urlApi);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can replace a driver
     */
    public function test_PUT_is_disabled()
    {
        $iri = $this->findIriBy(Driver::class, ['name'=>'Jack']);
        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can edit a driver
     */
    public function test_PATCH_is_disabled()
    {
        $iri = $this->findIriBy(Driver::class, ['name'=>'Jack']);
        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * Anyone can view a collection of drivers
     */
    public function test_GET_collection_succeeds()
    {
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertResponseIsSuccessful();
        $this->assertCount(4, $response->toArray()['hydra:member']);
    }

    /**
     * Anyone can view a single driver
     */
    public function test_GET_item_succeeds()
    {
        $iri = $this->findIriBy(Driver::class, ['name'=>'Jack']);
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Jack',
            'marketPrice' => 1000,
        ]);
    }
}
