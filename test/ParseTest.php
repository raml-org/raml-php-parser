<?php

class ParseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCorrectlyLoadASimpleRamlFile()
    {
        $parser = new \Raml\Parser();
        $raml = $parser->parse(__DIR__.'/fixture/simple.raml');

        $this->assertEquals('World Music API', $raml['title']);
    }


}