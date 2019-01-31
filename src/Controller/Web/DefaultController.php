<?php
namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController {
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(){
        return $this->render('homepage.html.twig');
    }
}