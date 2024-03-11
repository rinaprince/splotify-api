<?php

namespace App\DataFixtures;

use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SongsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 25; $i++) {
            $albumSongsCount = rand(5, 15);

            for ($j = 0; $j < $albumSongsCount; $j++) {
                $song = new Song();
                $song
                    ->setTitle($faker->unique()->sentence(3))
                    ->setDuration(rand(120, 600))
                    ->setAlbum($this->getReference('album_' . $i));

                $manager->persist($song);
            }
        }

        $manager->flush();
    }
}
