<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;

class ProgrammerControllerTest extends ApiTestCase {
	
	protected function setUp(){
		parent::setUp();
		$this->createUser('weaverryan');
	}
	
	public function testPOST(){
		$data = array(
			'nickname' => 'ObjectOrienter',
			'avatarNumber' => 5,
			'tagLine' => '<?php'
		);

		//1) Post to create the programmer

		$response = $this->client->post('/api/programmers', [
			'body' => json_encode($data)
		]);

    $this->assertEquals(201,  $response->getStatusCode());
    $this->assertEquals('/api/programmers/ObjectOrienter', $response->getHeader('Location')[0]);
    $finishedData = json_decode($response->getBody(), true);
    $this->assertArrayHasKey('nickname', $finishedData);
		$this->debugResponse($response);
	}
	
	public function testGetProgrammer(){
		$this->createProgrammer([
			'nickname' => 'UnitTester',
			'avatarNumber' => '3'
		]);
		$response = $this->client->get('/api/programmers/UnitTester');
		$this->assertEquals(200, $response->getStatusCode());
		$data = json_decode($response->getBody(), true);
		$this->asserter()->assertResponsePropertiesExist($response, [
			'nickname',
			'avatarNumber',
			'powerLevel',
			'tagLine'
		]);
		$this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
	}
	
	public function testGETProgrammersCollection(){
		$this->createProgrammer([
			'nickname' => 'UnitTester',
			'avatarNumber' => '3'
		]);
		$this->createProgrammer([
			'nickname' => 'CowboyCoder',
			'avatarNumber' => '10'
		]);
		$response = $this->client->get('/api/programmers');
		$this->assertEquals(200, $response->getStatusCode());
		$this->asserter()->assertResponsePropertyIsArray($response, 'programmers');
		$this->asserter()->assertResponsePropertyCount($response, 'programmers', 2);
		$this->asserter()->assertResponsePropertyEquals($response, 'programmers[1].nickname', 'CowboyCoder');
	}
	
	public function testPUTProgrammer(){
		$data = array(
			'nickname' => 'CowgirlCoder',
			'avatarNumber' => 2,
			'tagLine' => 'foo'
		);
		$this->createProgrammer([
			'nickname' => 'CowboyCoder',
			'avatarNumber' => '10'
		]);
		$response = $this->client->put('/api/programmers/CowboyCoder', [
			'body' => json_encode($data)
		]);
		$this->assertEquals(200, $response->getStatusCode());
		$this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 2);
		$this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'CowboyCoder');
	}
	
	public function testDELETEProgrammer(){
		$this->createProgrammer([
			'nickname' => 'UnitTester',
			'avatarNumber' => '3'
		]);
		$response = $this->client->delete('/api/programmers/UnitTester');
		$this->assertEquals(204, $response->getStatusCode());
	}
	
	public function testPATCHProgrammer(){
		$data = array(
			'tagLine' => 'bar'
		);
		$this->createProgrammer([
			'nickname' => 'CowboyCoder',
			'avatarNumber' => '10'
		]);
		$response = $this->client->patch('/api/programmers/CowboyCoder', [
			'body' => json_encode($data)
		]);
		$this->assertEquals(200, $response->getStatusCode());
		$data = json_decode($response->getBody(), true);
		$this->asserter()->assertResponsePropertyEquals($response, 'tagLine', 'bar');
		$this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 10);
	}
	
	public function testValidationErrors(){
		$data = array(
			'avatarNumber' => 5,
			'tagLine' => '<?php'
		);
		//1) Post to create the programmer
		$response = $this->client->post('/api/programmers', [
				'body' => json_encode($data)
		]);
		$this->assertEquals(400,  $response->getStatusCode());
		$this->asserter()->assertResponsePropertiesExist($response, [
				'type',
				'title',
				'errors'
		]);
		$this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
		$this->asserter()->assertResponsePropertyEquals(
				$response,
				'errors.nickname[0]',
				'Please enter a clever nickname!'
		);
		$this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
		$this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
	}
	
	public function testInvalidJson(){
		$invalidJson = <<<EOF
{
    "nickname": "JohnnyRobot",
    "avatarNumber" : "2
    "tagLine": "I'm from a test!"
}
EOF;
		//1) Post to create the programmer
		$response = $this->client->post('/api/programmers', [
			'body' => $invalidJson
		]);
		
		
		$this->assertEquals(400,  $response->getStatusCode());
		$this->asserter()->assertResponsePropertyEquals(
			$response,
			'type',
			'invalid_body_format'
		);
		
		$this->asserter()->assertResponsePropertyEquals(
			$response,
			'title',
			'Invalid JSON format sent'
		);
	}
	
	public function test404Exception(){
		$response = $this->client->get('/api/programmers/fake');
		$this->assertEquals(404,  $response->getStatusCode());
		$this->assertEquals(
			'application/problem+json',
			$response->getHeader('Content-Type')[0]
		);
		$this->asserter()->assertResponsePropertyEquals(
			$response,
			'type',
			'about:blank'
		);
		$this->asserter()->assertResponsePropertyEquals(
			$response,
			'title',
			'Not Found'
		);
		
		$this->asserter()->assertResponsePropertyEquals(
				$response,
				'detail',
				'No programmer found'
		);
		
		
	}
}
