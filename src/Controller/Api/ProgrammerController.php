<?php
/**
 * Created by PhpStorm.
 * User: petrero
 * Date: 27.09.2018
 * Time: 07:44
 */

namespace App\Controller\Api;


use App\API\ApiProblem;
use App\Controller\APIBaseController;
use App\Entity\Programmer;
use App\Form\ProgrammerType;
use App\Form\UpdateProgrammerType;
use App\Repository\ProgrammerRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;

class ProgrammerController extends APIBaseController {
	
	/**
	 * @Route("/api/programmers", methods="POST")
	 */
  public function newAction(
    Request $request,
    UserRepository $userRepository
  ){
    $programmer = new Programmer();
    $form = $this->createForm(ProgrammerType::class, $programmer);
    $this->processForm($request, $form);
	
	  if(!$form->isValid()){
		  return $this->createValidationErrorResponse($form);
	  }

    $programmer->setUser($userRepository->findOneBy(['username' => 'weaverryan']));
    $em = $this->getDoctrine()->getManager();
    $em->persist($programmer);
    $em->flush();

    $response = $this->createAPIResponse($programmer, 201);
    $location = $this->generateUrl("api_programmers_show", [
      'nickname' => $programmer->getNickname()]);
    $response->headers->set('Location',  $location);
    return $response;
  }

  /**
   * @Route("/api/programmers/{nickname}", name="api_programmers_show", methods="GET")
   */
  public function showAction(Programmer $programmer){
    if(!$programmer){
      throw $this->createNotFoundException('No programmer found for username ' . $nickname);
    }

    return $this->createAPIResponse($programmer);
  }

  /**
   * @Route("/api/programmers", name="api_programmers_list", methods="GET")
   */
  public function listAction(ProgrammerRepository $programmerRepository){
    $programmers  = $programmerRepository->findAll();

    $data = ['programmers' => $programmers];
    
    return $this->createAPIResponse($data);
  }

    
	/**
	 * @Route("/api/programmers/{nickname}", name="api_programmers_update", methods="PATCH|PUT")
	 */
	public function updateAction($nickname, Request $request,
    ProgrammerRepository $programmerRepository
	){
		$programmer = $programmerRepository->findOneBy(['nickname' => $nickname]);
		if(!$programmer){
			throw $this->createNotFoundException('No programmer found for username ' . $nickname);
		}
		
		$form = $this->createForm(UpdateProgrammerType::class, $programmer);
		$this->processForm($request, $form);
		
		if(!$form->isValid()){
			return $this->createValidationErrorResponse($form);
		}
		
		$em = $this->getDoctrine()->getManager();
		$em->persist($programmer);
		$em->flush();
		
		return $this->createAPIResponse($programmer);
	}
	
	/**
	 * @Route("/api/programmers/{nickname}", methods="DELETE")
	 */
	public function deleteAction($nickname, ProgrammerRepository $programmerRepository){
		$programmer = $programmerRepository->findOneBy(['nickname' => $nickname]);
		if($programmer){
			$em = $this->getDoctrine()->getManager();
			$em->remove($programmer);
			$em->flush();
		}
		return new Response(null, 204);
	}
	
	private function serializeProgrammer(Programmer $programmer){
		return [
			'nickname' => $programmer->getNickname(),
			'avatarNumber' => $programmer->getAvatarNumber(),
			'powerLevel' => $programmer->getPowerLevel(),
			'tagLine' => $programmer->getTagLine()
		];
	}
	
	private function processForm(Request $request, FormInterface $form){
		$body = $request->getContent();
		$data = json_decode($body, true);
		$clearMissing = $request->getMethod() !== 'PATCH';
		$form->submit($data, $clearMissing);
	}
	
	private function getErrorsFromForm(FormInterface $form){
		$errors = array();
		foreach ($form->getErrors() as $error) {
			$errors[] = $error->getMessage();
		}
		foreach ($form->all() as $childForm) {
			if ($childForm instanceof FormInterface) {
				if ($childErrors = $this->getErrorsFromForm($childForm)) {
					$errors[$childForm->getName()] = $childErrors;
				}
			}
		}
		return $errors;
	}
	
	private function createValidationErrorResponse(FormInterface $form){
		$errors = $this->getErrorsFromForm($form);
		
		$apiProblem = new ApiProblem(400, 'validation_error', 'There was a validation error');
		$apiProblem->set('errors', $errors);
		
		$response =  new JsonResponse($apiProblem->toArray(), $apiProblem->getStatusCode());
		$response->headers->set('Content-Type', 'application/problem+json');
		
		return $response;
	}
}