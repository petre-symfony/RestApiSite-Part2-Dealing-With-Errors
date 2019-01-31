<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class ProjectFixtures extends Fixture {
  protected $faker;

  public function load(ObjectManager $manager) {
    $this->faker = Factory::create();
    $project = new Project();
    $project->setName('BurningBot');
    $project->setDifficultyLevel($this->faker->numberBetween(1, 10));
    $manager->persist($project);
    $project = new Project();
    $project->setName('InstaFaceTweet');
    $project->setDifficultyLevel($this->faker->numberBetween(1, 10));
    $manager->persist($project);
    $project = new Project();
    $project->setName('MountBox');
    $project->setDifficultyLevel($this->faker->numberBetween(1, 10));
    $manager->persist($project);
    $project = new Project();
    $project->setName('Video Game');
    $project->setDifficultyLevel($this->faker->numberBetween(1, 10));
    $manager->persist($project);
    $project = new Project();
    $project->setName('Bike Shop Project');
    $project->setDifficultyLevel($this->faker->numberBetween(1, 10));
    $manager->persist($project);


    $manager->flush();
  }
}
