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
	private $debug;
	public function __construct($debug){
		$this->debug = $debug;
	}
	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event){
		$e = $event->getException();
		
		$statusCode = $e instanceof  HttpExceptionInterface ? $e->getStatusCode() : 500;
		
		if($statusCode == 500 && $this->debug){
			return;
		}
		
		if($e instanceof ApiProblemException){
			$apiProblem = $e->getApiProblem();
		} else {
			
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