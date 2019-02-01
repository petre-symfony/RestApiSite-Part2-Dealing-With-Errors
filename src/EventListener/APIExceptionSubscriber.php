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
			
			if($e instanceof HttpExceptionInterface){
				if (
					strpos(
						$e->getMessage(),
			'object not found by the @ParamConverter annotation'
					) !== -1
				) {
					$lengthStringToChopFromEnd = -(strlen('object not found by the @ParamConverter annotation')) - 1;
					$message = substr($e->getMessage(),0, $lengthStringToChopFromEnd);
					$message = strtolower(substr($message, 11));
					$message = 'No ' . $message . 'found';
				} else {
					$message = $e->getMessage();
				}
				
				$apiProblem->set('detail', $message);
			}
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