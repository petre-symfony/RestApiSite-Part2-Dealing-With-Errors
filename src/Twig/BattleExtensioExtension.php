<?php

namespace App\Twig;

use App\Entity\Programmer;
use Twig\Extension\AbstractExtension;

class BattleExtensioExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new \Twig_SimpleFilter('powerLevelClass', [$this, 'getPowerLevelClass']),
            new \Twig_SimpleFilter('avatar_path', [$this, 'getAvatarPath']),
        ];
    }

    public function getAvatarPath($number)
    {
        return sprintf('img/avatar%s.png', $number);
    }

    public function getPowerLevelClass(Programmer $programmer) {
        $powerLevel = $programmer->getPowerLevel();
        switch (true) {
            case ($powerLevel <= 3):
                return 'danger';
                break;
            case ($powerLevel <= 7):
                return 'warning';
                break;
            default:
                return 'success';
        }
    }

    public function getName(){
        return 'code_battle';
    }
}
