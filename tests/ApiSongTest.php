<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiSongTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function  testIndex(): void
    {
        $client = static::createClient();

        $response = $client->request('GET', '/songs', ["headers" => ["Accept: application/json"]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = $response->toArray();

        $this->assertArrayHasKey("response", $responseData);

        $this->assertArrayHasKey('status', $responseData["response"]);
        $this->assertEquals('success', $responseData["response"]["status"]);

        $this->assertArrayHasKey('data', $responseData["response"]);
        $this->assertCount(15, $responseData["response"]["data"]);

        $data = $responseData["response"]["data"];

        $this->assertArrayHasKey("id", $data[0]);
        $this->assertArrayHasKey("title", $data[0]);
        $this->assertArrayHasKey("duration", $data[0]);
        $this->assertArrayHasKey("album", $data[0]);

        $this->assertArrayHasKey('message', $responseData["response"]);
        $this->assertNull($responseData["response"]['message']);
    }

    public function testShow(): void
    {
        $client = static::createClient();

        $response = $client->request('GET', '/songs/1', ["headers" => ["Accept: application/json"]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = $response->toArray();

        $this->assertArrayHasKey("response", $responseData);

        $this->assertArrayHasKey('status', $responseData["response"]);
        $this->assertEquals('success', $responseData["response"]["status"]);

        $this->assertArrayHasKey('data', $responseData["response"]);

        $data = $responseData["response"]["data"];

        $this->assertArrayHasKey("id", $data);
        $this->assertArrayHasKey("title", $data);
        $this->assertArrayHasKey("duration", $data);
        $this->assertArrayHasKey("album", $data);

        $this->assertArrayHasKey('message', $responseData["response"]);
        $this->assertNull($responseData["response"]['message']);

        //$this->assertEquals('Doloremque sapiente est quam.', $data['title']);


/*        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);

        $this->assertArrayHasKey('data', $responseData);


        $this->assertArrayHasKey('message', $responseData);
        $this->assertNull($responseData['message']);*/
    }

    public function testShowNotFound(): void
    {
        $client = static::createClient();

        $response = $client->request('GET', '/songs/999999', ["headers" => ["Accept: application/json"]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

//        $this->assertEquals(404, $client->getResponse()->getStatusCode());
//        $responseData = json_decode($client->getResponse()->getContent(), true);
//
//        $this->assertArrayHasKey('status', $responseData);
//        $this->assertEquals('error', $responseData['status']);
//
//        $this->assertArrayHasKey('data', $responseData);
//        $this->assertNull($responseData['data']);
//
//        $this->assertArrayHasKey('message', $responseData);
//        $this->assertEquals("No s'ha pogut trobar la cançò", $responseData['message']);
    }
    function testCreateNewSong()
    {
        $client = static::createClient();

        //Quan les dades són valides 201
        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept: application/json"],
            "json" => [
                "title" => "Eligendi et ut.",
                "duration" => "121",
                "album" => 1
                ]
            ]
        );

        $responseData = $response->toArray()["response"]["data"];
        $this->assertSame("Eligendi et ut.", $responseData["title"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        //Quan les dades no són correectes 400
        $invalidResponse = $client->request('POST', '/songs', [
            "headers" => ["Accept" => "application/json"],
            "json" => [
                "title" => "Invalid song",
                "duration" => "600",
                "album" => 1
            ]
        ]);

        $invalidData = $invalidResponse->toArray()["response"]["data"];
        $this->assertSame("Invalid song", $invalidData["title"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }


}