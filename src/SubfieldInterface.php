<?php
/**
 * MARCspec is the specification of a reference, encoded as string, to a set of data 
 * from within a MARC record.
 * 
 * @author Carsten Klee <mailme.klee@yahoo.de>
 * @package CK\MARCspec
 * @copyright For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace CK\MARCspec;

/**
 * MARCspec subfield interface
 */
interface SubfieldInterface
{

    /**
     * constructor for MARCspec field
     *
     * @api
     * 
     * @param string $subfield The subfield spec
     */
     public function __construct($subfield);

    /**
    *
    * Get the subfield tag
    *
    * @api
    * 
    * @return string The subfield tag
    */
    public function getTag();
    
    /**
    * get array of subspecs
    *
    * @api
    * 
    * @return null|CK\MARCspec\SubSpecInterface[]
    */
    public function getSubSpecs();
    
    /**
    * add a subspec to the array of subspecs
    *
    * @api
    * 
    * @param CK\MARCspec\SubSpecInterface|CK\MARCspec\SubSpecInterface[]
    */
    public function addSubSpec($SubSpec);
    
    /**
     * Get the basic spec without subspecs
     * 
     * @api
     * 
     * @return string
     */
    public function getBaseSpec();
    
    /**
     * encodes Field as string
     *
     * @api
     * 
     * @return string
     */
    public function __toString();
    
    /**
     * Serialize Field as JSON
     * 
     * @api
     * 
     * @return array
     */
    public function jsonSerialize();

} // EOI

