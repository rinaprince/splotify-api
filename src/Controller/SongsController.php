<?php

namespace App\Controller;

use App\Entity\Album;
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
        $titles = [];

        foreach ($songs as $song) {
            $titles[] = $song->getTitle();
        }

        if ($songs == "" || $songs == null) {
            $songsJson = [
                "status" => "fail",
                "data" => $titles,
                "message" => "No hi ha cançons."
            ];
        } else {
            $songsJson = [
                "status" => "success",
                "data" => $titles,
                "message" => null
            ];
        }

        return new JsonResponse($songsJson, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_api_songs_show', methods: ['GET'])]
    public function show(?Song $songs): JsonResponse
    {
        if(!empty($songs)){
            $songsJson = [
                "status" => "succes",
                "data" => $songs->getTitle(),
                "message" => null
            ];
            $status = Response::HTTP_OK;
        }
        else{
            $songsJson = [
                "status" => "error",
                "data" => $songs,
                "message" => "No s'ha pogut trobar la cançò"
            ];
            $status = Response::HTTP_NOT_FOUND;
        }
        return new JsonResponse($songsJson, $status);
    }

    #[Route('/new', name: 'api_songs_new', methods: ['POST'])]
    function create(Request $request, EntityManagerInterface $e): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data["id"], $data["title"], $data["album_id"], $data["duration"])) {
            return new JsonResponse(["status" => "error", "message" => "Falten dades per crear la cançò."], Response::HTTP_BAD_REQUEST);
        }

        try {
            $album = $e->find(Album::class, $data["album_id"]);

            $song = new Song();
            $song->setId($data["id"]);
            $song->setTitle($data["title"]);
            $song->setAlbum($album);
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
            $responseData = [
                "status" => "error",
                "data" => $data,
                "message" => 'Error al crear la cançò: ' . $e->getMessage()
            ];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse($responseData, $statusCode);
    }

    #[Route('/{id}/delete', name: 'app_api_songs_delete', methods: ['POST'])]
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
                "data" => null,
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
            if (isset($data["album_id"])) {
                $album = $entityManager->find(Album::class, $data["album_id"]);
                $song->setAlbum($album);
            }
            if (isset($data["duration"])) {
                $song->setDuration($data["duration"]);
            }

            $entityManager->flush();

            $response = [
                "status" => "success",
                "data" => [
                    "id" => $song->getId(),
                    "title" => $song->getTitle(),
                    "album_id" => $song->getAlbum()->getId(),
                    "duration" => $song->getDuration()
                ],
                "message" => "La cançò s'ha actualitzat correctament!."
            ];

            return new JsonResponse($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = [
                "status" => "error",
                "data" => null,
                "message" => 'Error al editar la cançò: ' . $e->getMessage()
            ];
            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
