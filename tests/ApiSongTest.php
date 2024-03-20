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
    function testCreateNewSong() : void
    {
        $client = static::createClient();

        //Quan les dades són valides 201
        $response = $client->request('POST', '/songs', [
            "headers" => ["Accept: application/json"],
            "json" => [
                "title" => "Eligendi et ut.",
                "duration" => 121,
                "album" => 1
                ]
            ]
        );

        $responseData = $response->toArray();
        $this->assertArrayHasKey("response", $responseData);

        $this->assertArrayHasKey("status", $responseData["response"]);
        $this->assertSame("success", $responseData["response"]["status"]);

        $this->assertArrayHasKey("data", $responseData["response"]);

        $this->assertArrayHasKey("message", $responseData["response"]);
        $this->assertSame("La cançó s'ha creat correctament!", $responseData["response"]["message"]);
        //$this->assertSame("Eligendi et ut.", $responseData["title"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        //Quan les dades no són correectes 400
        $invalidResponse = $client->request('POST', '/songs', [
            "headers" => ["Accept" => "application/json"],
            "json" => [
                "title" => "Invalid song",
                "duration" => 999,
                "album" => 1
            ]
        ]);

        $invalidData = $invalidResponse->toArray();

        $this->assertArrayHasKey("response", $invalidData);

        $this->assertArrayHasKey("status", $invalidData["response"]);
        $this->assertSame("error", $invalidData["response"]["status"]);

        $this->assertArrayHasKey("data", $invalidData["response"]);

        $this->assertArrayHasKey("message", $invalidData["response"]);
        $this->assertSame("Error al crear la cançò:", $invalidData["response"]["message"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testEditSong() :void
    {
        $client = static::createClient();

        //Torna codi 200 si tot és correcte
        $response = $client->request('PUT', '/songs/5', [
                "headers" => ["Accept: application/json"],
                "json" => [
                    "title" => "Incidunt voluptatibus non.",
                    "duration" => 489,
                    "album" => 1
                ]
            ]
        );

        $responseData = $response->toArray();

        if ($response->getStatusCode() === 200) {
            // 200 (èxit)
            $this->assertArrayHasKey('response', $responseData);
            $this->assertArrayHasKey('status', $responseData['response']);
            $this->assertSame('success', $responseData['response']['status']);
            $this->assertArrayHasKey('data', $responseData['response']);
            $this->assertArrayHasKey('message', $responseData['response']);
            $this->assertSame("La cançò s'ha actualitzat correctament!", $responseData['response']['message']);
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        } elseif ($response->getStatusCode() === 400) {
            // 400 (error)
            $this->assertArrayHasKey('response', $responseData);
            $this->assertArrayHasKey('status', $responseData['response']);
            $this->assertSame('error', $responseData['response']['status']);
            $this->assertArrayHasKey('data', $responseData['response']);
            $this->assertArrayHasKey('message', $responseData['response']);
            $this->assertSame("Error al editar la cançò: ", $responseData['response']['message']);
            $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        } elseif ($response->getStatusCode() === 404) {
            //404 (no trobat)
            $this->assertArrayHasKey('response', $responseData);
            $this->assertArrayHasKey('status', $responseData['response']);
            $this->assertSame('error', $responseData['response']['status']);
            $this->assertArrayHasKey('data', $responseData['response']);
            $this->assertArrayHasKey('message', $responseData['response']);
            $this->assertSame('ID de la cançó no trobat.', $responseData['response']['message']);
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        } else {
            $this->fail("Codi d'estat no esperat" . $response->getStatusCode());
        }
    }

    public function testDeleteSong() :void
    {
        $client = static::createClient();

        // Prova d'eliminar una cançó existent
        $response = $client->request('GET', '/songs/3', ["headers" => ["Accept: application/json"]]);

        $responseData = $response->toArray();
        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('success', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame("La cançó s'ha eliminat!", $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);


        // Prova d'eliminar una cançó que no existeix
        $client->request('DELETE', '/songs/999');
        $this->assertArrayHasKey('response', $responseData);
        $this->assertArrayHasKey('status', $responseData['response']);
        $this->assertSame('error', $responseData['response']['status']);
        $this->assertArrayHasKey('data', $responseData['response']);
        $this->assertArrayHasKey('message', $responseData['response']);
        $this->assertSame("Error al borrar una cançò:", $responseData['response']['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

    }
}