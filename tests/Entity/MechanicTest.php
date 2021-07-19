<?php

namespace App\Tests\Entity;

use App\Entity\Mechanic;
use App\Tests\AppApiTestCase;

class MechanicTest extends AppApiTestCase
{
    protected string $urlApi = '/api/mechanics';

    public function setUp(): void
    {
        $this->setUpClient();
    }

    /**
     * No one can create a mechanic
     */
    public function test_POST_is_disabled()
    {
        $this->client->request('POST', $this->urlApi);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can replace a mechanic
     */
    public function test_PUT_is_disabled()
    {
        $iri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $this->client->request('PUT', $iri, ['json' => []]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * No one can edit a mechanic
     */
    public function test_PATCH_is_disabled()
    {
        $iri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $this->client->request('PATCH', $iri, ['json' => [], 'headers' => [
            'content-type'=> 'application/merge-patch+json'
        ]]);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * Anyone can view a collection of mechanics
     */
    public function test_GET_collection_succeeds()
    {
        $response = $this->client->request('GET', $this->urlApi);
        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $response->toArray()['hydra:member']);
    }

    /**
     * Anyone can view a single mechanic
     */
    public function test_GET_item_succeeds()
    {
        $iri = $this->findIriBy(Mechanic::class, ['name'=>'Bob']);
        $this->client->request('GET', $iri);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Bob',
            'marketPrice' => 400,
        ]);
    }
}
