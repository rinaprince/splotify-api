<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Song;
use App\Repository\SongRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/songs')]
class SongsController extends AbstractController
{
    #[Route('', name: 'app_api_songs', methods: ['GET'])]
    public function index(SongRepository $songRepository): JsonResponse
    {
        $songs = $songRepository->findAll();
        if ($songs == "" || $songs == null) {
            $songsJson = [
                "status" => "fail",
                "data" => $songs,
                "message" => "No hi ha cançons."
            ];
        } else {
            $songsJson = [
                "status" => "success",
                "data" => $songs,
                "message" => null
            ];
        }

        return new JsonResponse($songsJson, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_api_songs_show', methods: ['GET'])]
    public function show(?Song $songs): JsonResponse
    {
        if (!empty($songs)) {
            $songsJson = [
                "status" => "success",
                "data" => $songs,
                "message" => null
            ];
            $status = Response::HTTP_OK;
        } else {
            $songsJson = [
                "status" => "error",
                "data" => $songs,
                "message" => "No s'ha trobat ninguna cançò"
            ];
            $status = Response::HTTP_NOT_FOUND;
        }
        return new JsonResponse($songsJson, $status);
    }

    #[Route('/new', name: 'api_songs_new', methods: ['POST'])]
    function create(Request $request, EntityManagerInterface $e): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data["id"]) || !isset($data["title"]) || !isset($data["album"]) || !isset($data["duration"])) {
            $errorMessage = "Falten dades per crear la cançò.";
            return new JsonResponse(["status" => "error", "message" => $errorMessage], Response::HTTP_BAD_REQUEST);
        }

        try {
            $song = new Song();
            $song->setId($data["id"]);
            $song->setTitle($data["title"]);
            $song->setAlbum($data["album"]);
            $song->setDuration($data["duration"]);

            $e->persist($song);
            $e->flush();

            $responseData = [
                "status" => "success",
                "data" => $data,
                "message" => "La cançò s'ha creat correctament!."
            ];
            $statusCode = Response::HTTP_CREATED;
        } catch (\Exception $e) {
            $errorMessage = 'Error al crear la cançò: ' . $e->getMessage();
            $responseData = [
                "status" => "error",
                "data" => $data,
                "message" => $errorMessage
            ];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse($responseData, $statusCode);
    }

    #[Route('/{id}/delete', name: 'app_api_songs_delete', methods: ['DELETE'])]
    public function delete(Song $song, EntityManagerInterface $entityManager): JsonResponse
    {

        try {
            $entityManager->remove($song);
            $entityManager->flush();

            $response = [
                "status" => "success",
                "data" => $song,
                "message" => "La cançò s'ha eliminat!"
            ];

            return new JsonResponse($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = [
                "status" => "error",
                "data" => $song,
                "message" => 'Error al borrar una cançò: ' . $e->getMessage()
            ];
            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/edit', name: 'app_api_songs_edit', methods: ['PUT'])]
    public function edit(Song $song, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if (isset($data["title"])) {
                $song->setTitle($data["title"]);
            }
            if (isset($data["album"])) {
                $song->setAlbum($data["album"]);
            }
            if (isset($data["duration"])) {
                $song->setDuration($data["duration"]);
            }

            $entityManager->flush();

            $response = [
                "status" => "success",
                "data" => $song,
                "message" => "La cançò s'ha actualitzat correctament!."
            ];

            return new JsonResponse($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            $errorMessage = 'Error al editar la cançò: ' . $e->getMessage();
            $response = [
                "status" => "error",
                "data" => $song,
                "message" => $errorMessage
            ];
            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
