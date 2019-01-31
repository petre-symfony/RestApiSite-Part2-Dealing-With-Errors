<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="battle_battle")
 * @ORM\Entity(repositoryClass="App\Repository\BattleRepository")
 */
class Battle {
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Programmer")
   * @ORM\JoinColumn(nullable=false)
   */
  private $programmer;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Project")
   * @ORM\JoinColumn(nullable=false)
   */
  private $project;

  /**
   * @ORM\Column(type="datetime")
   */
  private $foughtAt;

  /**
   * @ORM\Column(type="boolean")
   */
  private $didProgrammerWin;

  /**
   * @ORM\Column(type="text")
   */
  private $notes;

  /**
   * Battle constructor.
   * @param $programmer
   * @param $project
   */
  public function __construct(Programmer $programmer, Project $project){
    $this->programmer = $programmer;
    $this->project = $project;
    $this->foughtAt = new \DateTime();
  }

  public function setBattleWonByProgrammer($notes){
    $this->didProgrammerWin = true;
    $this->notes = $notes;
  }
  
  public function setBattleLostByProgrammer($notes){
    $this->didProgrammerWin = false;
    $this->notes = $notes;
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getProgrammer(): ?Programmer {
    return $this->programmer;
  }


  public function getProject(): ?Project {
    return $this->project;
  }

  public function getFoughtAt(): ?\DateTimeInterface {
    return $this->foughtAt;
  }

  public function getDidProgrammerWin(): ?bool {
    return $this->didProgrammerWin;
  }

  public function getNotes(): ?string {
    return $this->notes;
  }
}
