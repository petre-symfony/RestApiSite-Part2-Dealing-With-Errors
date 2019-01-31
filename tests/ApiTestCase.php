<?php
namespace App\Tests;

use App\Entity\Programmer;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;


class ApiTestCase extends KernelTestCase {
	private static $staticClient;

	/** @var  Client*/
	protected $client;
	
	/**
	 * @var History
	 */
	private static $history = array();
	/**
	 * @var ConsoleOutput
	 */
	private $output;
	/**
	 * @var FormatterHelper
	 */
	private $formatterHelper;
	
	private $responseAsserter;

	public static function setUpBeforeClass(){
		$handler = HandlerStack::create();
		$handler->push(Middleware::history(self::$history));
		
		self::$staticClient = new Client([
			'base_uri' => 'http://localhost:8000',
			'http_errors' => false,
			'handler' => $handler
		]);
		
		self::bootKernel();
	}

	protected function setUp(){
		$this->client = self::$staticClient;
		
		// reset the history
		self::$history = array();
		
		$this->purgeDatabase();
	}
	
	/**
	 * Clean up Kernel usage in this test.
	 */
	public function tearDown(){
		//purposefully overriding so Symfony's kernel isn't shut down
	}
	
	protected function onNotSuccessfulTest(\Throwable $t){
		if ($lastResponse = $this->getLastResponse()) {
			$this->printDebug('');
			$this->printDebug('<error>Failure!</error> when making the following request:');
			$this->printLastRequestUrl();
			$this->printDebug('');
			$this->debugResponse($lastResponse);
		}
		throw $t;
	}
	
	private function purgeDatabase(){
		$purger = new ORMPurger($this->getService('doctrine.orm.default_entity_manager'));
		$purger->purge();
	}
	
	protected function getService($id){
		return self::$kernel->getContainer()->get($id);
	}
	
	protected function printLastRequestUrl(){
		$lastRequest = $this->getLastRequest();
		if ($lastRequest) {
			$this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUri()));
		} else {
			$this->printDebug('No request was made.');
		}
	}
	
	protected function debugResponse(ResponseInterface $response){
		foreach ($response->getHeaders() as $name => $values) {
			$this->printDebug(sprintf('%s: %s', $name, implode(', ', $values)));
		}
		$body = (string) $response->getBody();
		$contentType = $response->getHeader('Content-Type');
		$contentType = $contentType[0];
		if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
			$data = json_decode($body);
			if ($data === null) {
				// invalid JSON!
				$this->printDebug($body);
			} else {
				// valid JSON, print it pretty
				$this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
			}
		} else {
			// the response is HTML - see if we should print all of it or some of it
			$isValidHtml = strpos($body, '</body>') !== false;
			if ($isValidHtml) {
				$this->printDebug('');
				$crawler = new Crawler($body);
				// very specific to Symfony's error page
				$isError = $crawler->filter('.trace-line ')->count() > 0
						|| strpos($body, 'Symfony Exception') !== false;
				if ($isError) {
					$this->printDebug('There was an Error!!!!');
					$this->printDebug('');
				} else {
					$this->printDebug('HTML Summary (h1 and h2):');
				}
				foreach ($crawler->filter('.exception-message-wrapper h1')->extract(array('_text')) as $text) {
					$text = $this->removeLineBreaks($text);
					if ($isError) {
						$this->printErrorBlock($text);
					} else {
						$this->printDebug($text);
					}
				}
				foreach ($crawler
          ->filter('.trace-line')
          ->first()
          ->extract(array('_text')) as $text
				){
					$text = $this->removeLineBreaks($text);
					if ($isError) {
						$this->printErrorBlock($text);
					} else {
						$this->printDebug($text);
					}
				}
				/*
				 * When using the test environment, the profiler is not active
				 * for performance. To help debug, turn it on temporarily in
				 * the config_test.yml file (framework.profiler.collect)
				 */
				$profilerUrl = $response->getHeader('X-Debug-Token-Link');
				if ($profilerUrl) {
					$fullProfilerUrl = $response->getHeader('Host')[0].$profilerUrl[0];
					$this->printDebug('');
					$this->printDebug(sprintf(
							'Profiler URL: <comment>%s</comment>',
							$fullProfilerUrl
					));
				}
				// an extra line for spacing
				$this->printDebug('');
			} else {
				$this->printDebug($body);
			}
		}
	}
	
	protected function removeLineBreaks($text){
		// remove line breaks so the message looks nice
		$text = str_replace("\n", ' ', trim($text));
		// trim any excess whitespace "foo   bar" => "foo bar"
		$text = preg_replace('/(\s)+/', ' ', $text);
		return $text;
	}
	
	/**
	 * Print a message out - useful for debugging
	 *
	 * @param $string
	 */
	protected function printDebug($string){
		if ($this->output === null) {
			$this->output = new ConsoleOutput();
		}
		$this->output->writeln($string);
	}
	
	/**
	 * Print a debugging message out in a big red block
	 *
	 * @param $string
	 */
	protected function printErrorBlock($string){
		if ($this->formatterHelper === null) {
			$this->formatterHelper = new FormatterHelper();
		}
		$output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);
		$this->printDebug($output);
	}
	
	/**
	 * @return RequestInterface
	 */
	private function getLastRequest(){
		if (!self::$history || empty(self::$history)) {
			return null;
		}
		$history = self::$history;
		$last = array_pop($history);
		return $last['request'];
	}
	
	/**
	 * @return ResponseInterface
	 */
	private function getLastResponse(){
		if (!self::$history || empty(self::$history)) {
			return null;
		}
		$history = self::$history;
		$last = array_pop($history);
		return $last['response'];
	}
	
	protected function createUser($username, $plainPassword = 'foo'){
		$user = new User();
		$user->setUsername($username);
		$user->setEmail($username.'@foo.com');
		$password = $this->getService('security.password_encoder')
				->encodePassword($user, $plainPassword);
		$user->setPassword($password);
		$em = $this->getEntityManager();
		$em->persist($user);
		$em->flush();
		return $user;
	}
	/**
	 * @return EntityManagerInterface
	 */
	protected function getEntityManager(){
		return $this->getService('doctrine.orm.default_entity_manager');
	}
	
	protected function createProgrammer(array $data){
		$data = array_merge([
			'powerLevel' => rand(0, 10),
			'user' => $this->getEntityManager()->getRepository(User::class)
				->findAny()
		], $data);
		$accessor = PropertyAccess::createPropertyAccessor();
		$programmer = new Programmer();
		foreach ($data as $key => $value){
			$accessor->setValue($programmer, $key, $value);
		}
		$em = $this->getEntityManager();
		$em->persist($programmer);
		$em->flush();
		return $programmer;
	}
	
	protected function asserter(){
		if ($this->responseAsserter === null){
			$this->responseAsserter = new ResponseAsserter();
		}
		return $this->responseAsserter;
	}
}