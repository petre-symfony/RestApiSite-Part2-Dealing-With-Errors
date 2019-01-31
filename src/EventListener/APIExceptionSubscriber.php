<?php
namespace App\EventListener;


use App\API\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class APIExceptionSubscriber implements EventSubscriberInterface {
	public function onKernelException(GetResponseForExceptionEvent $event){
		$e = $event->getException();

		if(!$e instanceof ApiProblemException){
			return;
		}

		$response =  new JsonResponse(
			$e->getApiProblem()->toArray(),
			$e->getApiProblem()->getStatusCode()
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