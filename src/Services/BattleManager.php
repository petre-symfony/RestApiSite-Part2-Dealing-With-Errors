<?php
/**
 * Created by PhpStorm.
 * User: petrero
 * Date: 24.09.2018
 * Time: 21:34
 */

namespace App\Services;

use App\Entity\Battle;
use App\Entity\Programmer;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class BattleManager{
	private $em;

	public function __construct(EntityManagerInterface $em){
		$this->em = $em;
	}

	/**
	 * Creates and wages an epic battle
	 *
	 * @param Programmer $programmer
	 * @param Project $project
	 * @return Battle
	 */
	public function battle(Programmer $programmer, Project $project){
		$battle = new Battle($programmer, $project);

		if ($programmer->getPowerLevel() < $project->getDifficultyLevel()) {
			// not enough energy
			$battle->setBattleLostByProgrammer(
				'You don\'t have the skills to even start this project. Read the documentation (i.e. power up) and try again!'
			);
		} else {
			if (rand(0, 2) != 2) {
				$battle->setBattleWonByProgrammer(
					'You battled heroically, asked great questions, worked pragmatically and finished on time. You\'re a hero!'
				);
			} else {
				$battle->setBattleLostByProgrammer(
					'Requirements kept changing, too many meetings, project failed :('
				);
			}

			$programmer->setPowerLevel($programmer->getPowerLevel() - $project->getDifficultyLevel());
		}

		$this->em->persist($battle);
		$this->em->persist($programmer);
		$this->em->flush();

		return $battle;
	}
}