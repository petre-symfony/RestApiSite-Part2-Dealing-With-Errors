<?php

namespace App\Controller\Web;

use App\Entity\Battle;
use App\Form\BattleType;
use App\Repository\BattleRepository;
use App\Repository\ProgrammerRepository;
use App\Repository\ProjectRepository;
use App\Services\BattleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/battle")
 */
class BattleController extends AbstractController
{
    /**
     * @Route("/", name="battle_index", methods={"GET"})
     */
    public function index(BattleRepository $battleRepository): Response
    {
        return $this->render('battle/index.html.twig', [
            'battles' => $battleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="battle_new", methods="POST")
     */
    public function new(Request $request,
        ProgrammerRepository $programmerRepository,
        ProjectRepository $projectRepository,
        BattleManager $battleManager
    ): Response {
        $programmerId = $request->request->get('programmer_id');
        $projectId = $request->request->get('project_id');
        $project = $projectRepository->findOneBy(['id' => $projectId]);
        $programmer = $programmerRepository->findOneBy(['id' => $programmerId]);
        if ($programmer->getUser() != $this->getUser()){
            throw new AccessDeniedException();
        }
        $battle = $battleManager->battle($programmer, $project);
        return $this->redirectToRoute('battle_show', [
            'id' => $battle->getId()
        ]);
    }
    /**
     * @Route("/{id}", name="battle_show", methods={"GET"})
     */
    public function show(Battle $battle): Response
    {
        return $this->render('battle/show.html.twig', [
            'battle' => $battle,
        ]);
    }
}
