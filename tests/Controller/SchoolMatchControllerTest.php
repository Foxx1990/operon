<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SchoolMatchControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testMatchExactName(): void
    {
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => 'I Liceum Ogólnokształcące im. Adama Mickiewicza'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($response['matched']);
        $this->assertEquals('I Liceum Ogólnokształcące im. Adama Mickiewicza', $response['school']['name']);
        $this->assertEquals('Warszawa', $response['school']['city']);
    }

    public function testMatchByAlias(): void
    {
        // Testing "V LO" for "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego" (Kraków)
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => 'V LO'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($response['matched']);
        $this->assertEquals('Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego', $response['school']['name']);
        $this->assertEquals('Kraków', $response['school']['city']);
    }

    public function testMatchByTypoInAlias(): void
    {
        // Testing "Elektonik" (missing r) -> "Zespół Szkół Elektronicznych i Informatycznych" (Warszawa)
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => 'Elektonik' 
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($response['matched']);
        $this->assertEquals('Zespół Szkół Elektronicznych i Informatycznych', $response['school']['name']);
    }

    public function testMatchByCommonPart(): void
    {
        // "Zeromski" -> "Liceum Ogólnokształcące im. Stefana Żeromskiego" (Szczecin)
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => 'Zeromski'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($response['matched']);
        $this->assertEquals('Liceum Ogólnokształcące im. Stefana Żeromskiego', $response['school']['name']);
        $this->assertEquals('Szczecin', $response['school']['city']);
    }

    public function testNoMatch(): void
    {
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => 'Szkoła Magii i Czarodziejstwa'
        ]));

        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($response['matched']);
    }

    public function testEmptyInput(): void
    {
        $this->client->request('POST', '/api/schools/match', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'schoolName' => ''
        ]));

        // Validation should fail (422 Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);
    }
}
