<?php
namespace App\API;


use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException {
	private $apiProblem;

	public function __construct(ApiProblem $apiProblem, Throwable $previous = null){
		$this->apiProblem = $apiProblem;
		$statusCode = $apiProblem->getStatusCode();
		$message = $apiProblem->getTitle();


		parent::__construct($message, $statusCode, $previous);
	}

}