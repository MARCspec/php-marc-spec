<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\Subfield;
use CK\MARCspec\Field;

class SubfieldSingleTest extends \PHPUnit_Framework_TestCase
{
    
    public function subfieldspec($arg,$field)
    {
        return new Subfield($arg,$field);
    }

    /**
     * assert same properties
     */
    public function testValidSubfieldSpec01()
    {
        $field = new Field('...');
        $subfieldSpec = $this->subfieldspec('$a{245$b}',$field);
        $this->assertSame('a', $subfieldSpec->getTag());
        $this->assertSame(1, count($subfieldSpec->getSubspecs()));
        $this->assertSame('245', $subfieldSpec->getSubspecs()[0]->getRightSubTerm()->getField()->getTag());
    }
    
}