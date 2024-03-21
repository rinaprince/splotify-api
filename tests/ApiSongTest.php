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
    public function testIndex(): void
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

    public function testCreateNewSongWithValidDataSucceed(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept: application/json"],
            "json" => [
                "title" => "Eligendi et ut.",
                "duration" => 121,
                "album" => 1
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testCreateNewSongWithNoDataFails(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept" => "application/json"],
            "json" => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateNewSongWithIncompleteDataFails(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept" => "application/json"],
            "json" => [
                "title" => "Incomplete Song"
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateNewSongWithInvalidDataFails(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept" => "application/json"],
            "json" => [
                "title" => "Invalid Song",
                "duration" => "Invalid duration",
                "album" => "Invalid album"
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testEditSongWithSuccess(): void
    {
        $client = static::createClient();

        $response = $client->request('PUT', '/songs/6', [
            "headers" => ["Accept: application/json"],
            "json" => [
                "title" => "Incidunt voluptatibus non.",
                "duration" => 489,
                "album" => 1
            ]
        ]);

        $responseData = $response->toArray();

        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('success', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame("La cançò s'ha actualitzat correctament!", $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testEditSongWithError(): void
    {
        $client = static::createClient();

        $response = $client->request('PUT', '/songs/999', [
            "headers" => ["Accept: application/json"],
            "json" => [
                // Dades incorrectes per provocar un error
                "title" => "Miau",
                "duration" => 999,
                "album" => 999
            ]
        ]);

        $responseData = $response->toArray();

        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('error', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame("Error al editar la cançò: Dades invàlides o incompletes.", $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testEditSongWithNotFound(): void
    {
        $client = static::createClient();

        $response = $client->request('PUT', '/songs', [
            "headers" => ["Accept: application/json"],
            "json" => [
                // ID inexistent
            ]
        ]);

        $responseData = $response->toArray();

        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('error', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame('ID de la cançó no trobat.', $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }


    /*public function testEditSongWithIncompleteDataFails(): void
    {
        $client = static::createClient();

        $response = $client->request('PUT', '/songs/6', [
            "headers" => ["Accept: application/json"],
            "json" => [
                // Falta de dades per provocar un error
                "title" => "Incidunt voluptatibus non."
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }*/


    public function testDeleteExistingSong(): void
    {
        $client = static::createClient();

        // Prova d'eliminar una cançó existent
        $response = $client->request('DELETE', '/songs/3', [
            "headers" => ["Accept: application/json"],
            "json" => [
                "title" => "Nobis eos non debitis.",
                "duration" => 139,
                "album" => 1
            ]
        ]);

        $responseData = $response->toArray();
        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('success', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame("La cançó s'ha eliminat!", $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteNonExistingSong(): void
    {
        $client = static::createClient();

        // Prova d'eliminar una cançó que no existeix
        $response = $client->request('DELETE', '/songs/999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}