<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{

    public function testShowPost ()
    {
        $client = static::createClient();

        $client->request( 'GET', '/basket' );

        $this->assertEquals( 200, $client->getResponse()->getStatusCode() );
    }

    public function testCheckFormSend(){
        $client = static::createClient();

        $crawler = $client->request( 'GET', '/basket' );


        $form = $crawler->selectButton('submit')->form();


        $form['first_name'] = 'Lucas';
        $form['last_name'] = 'Hey there!';
        $form['second_name'] = 'Hey there!';
        $form['email'] = 'Hey there!';
        $form['phone'] = 'Hey there!';
        $form['address'] = 'Hey there!';


        $crawler = $client->submit($form);
    }
}