<?php

namespace App\DataFixtures;

use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 25; $i++) {
            $album = new Album();
            $album
                ->setTitle($faker->sentence(3))
                ->setReleasedAt($faker->dateTimeThisDecade())
                ->setCover($faker->imageUrl())
            ;

            $manager->persist($album);
            $this->addReference('album_' . ($i + 1), $album);
        }

        $manager->flush();
    }
}
