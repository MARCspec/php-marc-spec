<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\ComparisonString;
use CK\MARCspec\Exception\InvalidMARCspecException;

class ComparisonStringTest extends \PHPUnit_Framework_TestCase
{
    
    public function compare($arg)
    {
        return new ComparisonString($arg);
    }
    
    /****
    * invalid data types
    ***/
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
        $comapare = $this->compare(array('a'));
    }    
    
    /**
     * @expectedException InvalidMARCspecException
     */
    public function testInvalidArgument2Decode()
    {
        $compare = $this->compare('.{.');
    }

    /**
     * assert same string
     */
    public function testValidComparisonString1()
    {
        $compare = $this->compare(".");
        $this->assertSame('.', $compare->getRaw());
        $this->assertSame('.', $compare->getComparable());
        
    }
    
    /**
     * assert same string
     */
    public function testValidComparisonString2()
    {
        $escaped_string = '\\.';
        $compare = $this->compare($escaped_string);
        $this->assertSame('\\.', $compare->getRaw());
        
        $escaped_string = 'this\sis\sa\sTest\s\\\{\}\!\=\~\?';
        $compare = $this->compare($escaped_string);
        $this->assertSame('this\sis\sa\sTest\s\\\{\}\!\=\~\?', $compare->getRaw());
        
        $unescaped_string = 'this is a Test \{}!=~?';
        $escaped_string = ComparisonString::escape($unescaped_string);
        $compare = $this->compare($escaped_string);
        $this->assertSame('this\sis\sa\sTest\s\\\{\}\!\=\~\?', $compare->getRaw());
        $this->assertSame('this is a Test \{}!=~?', $compare->getComparable());
        
    }
}
