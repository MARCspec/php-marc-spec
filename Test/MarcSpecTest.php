<?php

/*
* (c) Carsten Klee <>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MarcSpec\Test;



ini_set('include_path', '/usr/share/pear');
require_once "autoload.php";
require_once "/home/sbb-zdb2/htdocs/php/phpunit-3.7/PHPUnit/Autoload.php";

use CK\MarcSpec\MarcSpec;
use CK\MarcSpec\InvalidMarcSpecException;


class MarcSpecTest extends \PHPUnit_Framework_TestCase
{
    public function decoder($arg)
    {
        $ms = new MarcSpec;
        return $ms->decode($arg);
    }
    
    public function marcspec()
    {
        return new MarcSpec;
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
            $this->decoder((int)'245a');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
            $this->decoder(array('245a'));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec1()
    {
            $this->decoder(' 24 ');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec2()
    {
            $this->decoder('24/');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec3()
    {
            $this->decoder('24x');
    }
    
    /**
     * assert same field tag
     */
    public function testValidMarcSpec1()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('LDR');
            $this->assertSame('LDR', $marcSpec->fieldTag);
            
            $marcSpec->decode('245');
            $this->assertSame('245', $marcSpec->fieldTag);
            
            $marcSpec->decode('XXX');
            $this->assertSame('XXX', $marcSpec->fieldTag);
            
            $marcSpec->decode('245abc');
            $this->assertSame('245', $marcSpec->fieldTag);
            $this->assertSame(array('a','b','c'), $marcSpec->subfields);
            
            $marcSpec->decode('245!"#$%&\'()*+-./:;<=>?');
            $this->assertSame(array("!","\"","#","$","%","&","'","(",")","*","+","-",".","/",":",";","<","=",">","?"), $marcSpec->subfields);
            
            $marcSpec->decode('245ab_1a');
            $this->assertSame('1', $marcSpec->indicator1);
            $this->assertSame('a', $marcSpec->indicator2);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec4()
    {
            $this->decoder('007~');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec5()
    {
            $this->decoder('007~1-2-');
    }
    
    /**
     * test character position and range
     */
    public function testValidMarcSpec2()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('LDR~0-3');
            $this->assertSame('LDR', $marcSpec->fieldTag);
            $this->assertSame(0, $marcSpec->charStart);
            $this->assertSame(3, $marcSpec->charEnd);
            $this->assertSame(4, $marcSpec->charLength);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec6()
    {
            $this->decoder('245aX');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec7()
    {
            $this->decoder('245ab_1+');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec8()
    {
            $this->decoder('245ab_123');
    }

}