<?php
namespace App\EventListener;


use App\API\ApiProblem;
use App\API\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class APIExceptionSubscriber implements EventSubscriberInterface {
	public function onKernelException(GetResponseForExceptionEvent $event){
		$e = $event->getException();

		if($e instanceof ApiProblemException){
			$apiProblem = $e->getApiProblem();
		} else {
			$statusCode = $e instanceof  HttpExceptionInterface ? $e->getStatusCode() : 500;
			$apiProblem = new ApiProblem($statusCode);
		}

		$response =  new JsonResponse(
			$apiProblem->toArray(),
			$apiProblem->getStatusCode()
		);

		$response->headers->set('Content-Type', 'application/problem+json');

		$event->setResponse($response);

	}

	public static function getSubscribedEvents(){
		return [
			KernelEvents::EXCEPTION => 'onKernelException'
		];
	}



}