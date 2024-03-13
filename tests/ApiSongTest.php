<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ApiSongTest extends ApiTestCase
{
    public function  testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/songs', [], [], ['ACCEPT' => 'application/json']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);

        $this->assertGreaterThan(0, count($responseData['data']));

        $this->assertArrayHasKey('message', $responseData);
        $this->assertNull($responseData['message']);
    }

    public function testShow(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/songs/1', [], [], ['ACCEPT' => 'application/json']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Aperiam voluptatem expedita.', $responseData['data']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertNull($responseData['message']);
    }

    public function testShowNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/songs/999', [], [], ['ACCEPT' => 'application/json']);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('error', $responseData['status']);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertNull($responseData['data']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals("No s'ha trobat ninguna cançó", $responseData['message']);
    }

    public function testEdit(): void
    {
        $client = static::createClient();

        $songId = 1;
        $requestData = [
            'title' => 'Nuevo título de la canción',
            'album_id' => 2,
            'duration' => 180
        ];

        $client->request('GET', '/api/v1/songs/' . $songId . '/edit', [], [], [], json_encode($requestData));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($requestData['Earum rerum quam.'], $responseData['data']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals("La cançò s'ha actualitzat correctament!", $responseData['message']);
    }

}