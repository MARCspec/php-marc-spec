<?php
/**
* MARCspec is the specification of a reference, encoded as string, to a set of data from within a MARC record.
*
* @author Carsten Klee <mailme.klee@yahoo.de>
* @copyright For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec;

/**
 * MARCspec field interface.
 */
interface FieldInterface
{
    /**
     * Constructor for MARCspec field.
     *
     * @api
     *
     * @param string $fieldspec The field spec
     */
     public function __construct($fieldspec);
    
    /**
     * Get the field tag.
     *
     * @api
     *
     * @return string $fieldTag The field tag
     */
    public function getTag();
    
    /**
     * get array of subspecs.
     *
     * @api
     * 
     * @return null|CK\MARCspec\SubSpecInterface[] An array of SubSpecInferace instances
     */
    public function getSubSpecs();

    /**
     * add a subspec to the array of subspecs.
     *
     * @api
     * 
     * @param SubSpecInterface|CK\MARCspec\SubSpecInterface[]
     * 
     * @return null|array
     */
    public function addSubSpec($SubSpec);

    /**
     * Get the basic spec without subspecs.
     *
     * @api
     *
     * @return string
     */
    public function getBaseSpec();

    /**
     * encodes Field as string.
     *
     * @api
     *
     * @return string
     */
    public function __toString();

    /**
     * Serialize Field as JSON.
     *
     * @api
     *
     * @return array
     */
    public function jsonSerialize();
} // EOI
