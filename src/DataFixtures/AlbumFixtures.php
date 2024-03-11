<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Song;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        //Generar àlbums
        for ($i = 0; $i <25; $i++) {
            $album = new Album();
            $album
                ->setTitle($faker->sentence(3))
                ->setReleasedAt($faker->dateTimeThisDecade())
                ->setCover($faker->imageUrl())
            ;

            $manager->persist($album);
            $this->addReference('album_' . ($i + 1), $album);

            //Generar cançons per àlbum
            $numSongs = rand(5, 15);
            for ($j = 0; $j < $numSongs; $j++) {
                $song = new Song();
                $song
                    ->setTitle($faker->unique()->sentence(3))
                    ->setDuration(rand(120, 600))
                    ->setAlbum($album);

                $manager->persist($song);
            }
        }

        // Asignar àlbums a users
        $admin = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $user = $manager->getRepository(User::class)->findOneBy(['username' => 'user']);
        $albums = $manager->getRepository(Album::class)->findAll();

        foreach ($albums as $album) {
            if (rand(0, 1)) {
                $admin->addLike($album);
            } else {
                $user->addLike($album);
            }
        }


        $manager->flush();
    }
}
