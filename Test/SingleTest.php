<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\MARCspec;
use CK\MARCspec\Field;
use CK\MARCspec\Exception\InvalidMARCspecException;
#use CK\MARCspec\Subfield;
#use CK\MARCspec\SubSpec;

class SingleTest extends \PHPUnit_Framework_TestCase
{

    
    public function marcspec($arg)
    {
        return new MARCspec($arg);
    }
    

    public function testSingle()
    {
     $marcSpec = $this->marcspec('245_1{[1]}');
     $marcSpec = $this->marcspec('245_1$[{[1]}');
     $this->assertSame(1, $marcSpec['subfields'][0]['subSpecs'][0]['rightSubTerm']['subfields'][0]['indexStart']);
     $marcSpec = $this->marcspec('245{/0}');
     $this->assertSame(0, $marcSpec['field']['subSpecs'][0]['rightSubTerm']['field']['charStart']);
    }

}
