<?php

namespace App\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Song;
use App\Repository\SongRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/songs')]
class SongsController extends AbstractController
{
    #[Route('', name: 'app_api_songs', methods: ['GET'])]
    public function index(Request $request, SongRepository $songRepository, PaginatorInterface $paginator): JsonResponse
    {
        $q = $request->query->get('q', '');

        if (empty($q)) {
            $query = $songRepository->findAllQuery();
        } else {
            $query = $songRepository->findByTextQuery($q);
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );

        $songs = $pagination->getItems();
        $titles = [];


        if (empty($songs)) {
            $songsJson = [
                "response" => [
                    "status" => "fail",
                    "data" => null,
                    "message" => "No hi ha cançons."
                ]
            ];
        } else {
            $songsJson = [
                "response" => [
                    "status" => "success",
                    "data" => $songs,
                    "message" => null
                ]];
        }

        return new JsonResponse($songsJson, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_api_songs_show', methods: ['GET'])]
    public function show(?Song $song): JsonResponse
    {
        if (!empty($song)) {
            $songsJson = [
                "response" => [
                    "status" => "success",
                    "data" => $song,
                    "message" => null
                ]
            ];
            $status = Response::HTTP_OK;
        } else {
            $songsJson = [
                "response" => [
                    "status" => "error",
                    "data" => null,
                    "message" => "No s'ha pogut trobar la cançò"
                ]
            ];
            $status = Response::HTTP_NOT_FOUND;
        }
        return new JsonResponse($songsJson, $status);
    }

    #[Route('', name: 'api_songs_new', methods: ['POST'])]
    function create(Request $request, EntityManagerInterface $entityManager, AlbumRepository $albumRepository, ValidatorInterface $validator): JsonResponse
    {
        try {
            $data = $request->toArray();

            // Comprovar si l'àlbum existeix
            $album = $albumRepository->find($data["album"]);
            if ($album === null) {
                $albumJson = ["response" => [
                    "status" => "error",
                    "data" => null,
                    "message" => "No s'ha trobat l'àlbum."
                ]];
                return new JsonResponse($albumJson, Response::HTTP_BAD_REQUEST);
            }

            // Crear una cançó
            $song = new Song();
            $song->setTitle($data["title"]);
            $song->setAlbum($album);
            $song->setDuration($data["duration"]);

            // Validar la cançó
            $violations = $validator->validate($song);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                $songsJson = ["response" => [
                    "status" => "error",
                    "data" => null,
                    "message" => implode(', ', $errors)
                ]];
                return new JsonResponse($songsJson, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($song);
            $entityManager->persist($album);
            $entityManager->flush();

            $responseData = ["response" => [
                "status" => "success",
                "data" => $data,
                "message" => "La cançó s'ha creat correctament!"
            ]];
            $statusCode = Response::HTTP_CREATED;
            return new JsonResponse($responseData, $statusCode);
        } catch (\Exception $e) {
            $responseData = ["response" => [
                "status" => "error",
                "data" => null,
                "message" => 'Error en crear la cançó: ' . $e->getMessage()
            ]];
            $statusCode = Response::HTTP_BAD_REQUEST;
            return new JsonResponse($responseData, $statusCode);
        }
    }

    #[Route('/{id}', name: 'app_api_songs_delete', methods: ['DELETE'])]
    public function delete(Song $song, EntityManagerInterface $entityManager): JsonResponse
    {

        try {
            $response = [ "response" => [
                "status" => "success",
                "data" => $song,
                "message" => "La cançó s'ha eliminat!"
                ]
            ];

            return new JsonResponse($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = [ "response" => [
                "status" => "error",
                "data" => null,
                "message" => "Error al borrar una cançò: " . $e->getMessage()
                ]
            ];
            $entityManager->remove($song);
            $entityManager->flush();

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
    }

    public function edit(Song $song, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if (isset($data["title"])) {
                $song->setTitle($data["title"]);
            }
            if (isset($data["album"])) {
                $album = $entityManager->find(Album::class, $data["album"]);
                if (!$album) {
                    throw new \Exception("L'àlbum especificat no existeix.");
                }
                $song->setAlbum($album);
            }
            if (isset($data["duration"])) {
                $song->setDuration($data["duration"]);
            }

            $entityManager->persist($song);
            $entityManager->flush();

            $response = [
                "response" => [
                    "status" => "success",
                    "data" => [
                        "id" => $song->getId(),
                        "title" => $song->getTitle(),
                        "album" => $song->getAlbum()->getId(),
                        "duration" => $song->getDuration()
                    ],
                    "message" => "La cançò s'ha actualitzat correctament!."
                ]
            ];

            return new JsonResponse($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = [
                "response" => [
                    "status" => "error",
                    "data" => null,
                    "message" => "Error al editar la cançò: " . $e->getMessage()
                ]
            ];
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }
    }
}
