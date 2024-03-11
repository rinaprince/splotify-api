<?php

namespace App\DataFixtures;

use App\Entity\Band;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BandFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $band = new Band();
            $band
                ->setName($faker->unique()->sentence(2))
                ->setBio($faker->paragraph());

            $manager->persist($band);
        }

        $manager->flush();
    }
}
