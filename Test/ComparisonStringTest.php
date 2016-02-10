<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace CK\MARCspec\Test;

use CK\MARCspec\ComparisonString;

/**
 * @covers CK\MARCspec\ComparisonString
 */
class ComparisonStringTest extends \PHPUnit_Framework_TestCase
{
    protected $validTests;
    protected $invalidTests;

    protected function setUp()
    {
        if (0 < count($this->validTests)) {
            return;
        }
        $validTestsJson = file_get_contents(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/valid/validComparisonString.json');
        $invalidTestsJson = file_get_contents(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/invalid/invalidComparisonString.json');
        $this->validTests = json_decode($validTestsJson);
        $this->invalidTests = json_decode($invalidTestsJson);
    }

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
        $this->compare(['a']);
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument2Decode()
    {
        $this->compare('.{.');
    }

    /**
     * assert same string.
     */
    public function testValidComparisonString1()
    {
        $compare = $this->compare('.');
        $this->assertSame('.', $compare->getRaw());
        $this->assertSame('.', $compare->getComparable());
        $this->assertSame('\.', $compare->__toString());
    }

    /**
     * assert same string.
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

    public function testInvalidFromTestSuite()
    {
        foreach ($this->invalidTests->{'tests'} as $test) {
            try {
                new ComparisonString($test->{'data'});
            } catch (\Exception $e) {
                continue;
            }
            $this->fail('An expected exception has not been raised for '.$test->{'data'});
        }
    }

    public function testValidFromTestSuite()
    {
        foreach ($this->validTests->{'tests'} as $test) {
            new ComparisonString($test->{'data'});
            $this->assertSame(1, preg_match('/'.$this->validTests->{'schema'}->{'pattern'}.'/', $test->{'data'}));
        }
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        $escaped_string = '\\.';
        $compare = $this->compare($escaped_string);
        unset($compare['raw']);
    }
}
