<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace CK\MARCspec\Test;

use CK\MARCspec\ComparisonString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ComparisonStringTest extends TestCase
{
    #[DataProvider('invalidFromTestSuiteProvider')]
    public function testInvalidFromTestSuite($test)
    {
        $this->expectException(\Exception::class);
        new ComparisonString($test);
    }

    public static function invalidFromTestSuiteProvider()
    {
        $invalidTests = json_decode(file_get_contents(__DIR__ . '/../' . 'vendor/ck/marcspec-test-suite/invalid/invalidComparisonString.json'));
        $data = [];
        foreach ($invalidTests->{'tests'} as $test) {
            $data[0][] = $test->{'data'};
        }

        return $data;
    }

    public function testValidFromTestSuite()
    {
        $validTests = json_decode(file_get_contents(__DIR__ . '/../' . 'vendor/ck/marcspec-test-suite/valid/validComparisonString.json'));
        foreach ($validTests->{'tests'} as $test) {
            new ComparisonString($test->{'data'});
            $this->assertSame(1, preg_match('/' . $validTests->{'schema'}->{'pattern'} . '/', $test->{'data'}));
        }
    }

    public function compare($arg)
    {
        return new ComparisonString($arg);
    }

    /****
     * invalid data types
     ***/

    /**
     */
    public function testInvalidArgument1Decode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->compare(['a']);
    }

    /**
     */
    public function testInvalidArgument2Decode()
    {
        $this->expectException(\CK\MARCspec\Exception\InvalidMARCspecException::class);
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

    /**
     */
    public function testOffsetUnset()
    {
        $this->expectException(\BadMethodCallException::class);
        $escaped_string = '\\.';
        $compare = $this->compare($escaped_string);
        unset($compare['raw']);
    }
}
