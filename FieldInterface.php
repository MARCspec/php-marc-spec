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
     * Set the field tag.
     *
     * Provided param gets validated
     *
     * @api
     *
     * @param string $arg The field tag
     */
    public function setTag($arg);

    /**
     * Get the field tag.
     *
     * @api
     *
     * @return string $fieldTag The field tag
     */
    public function getTag();

    /**
     * Set indicators.
     *
     * @api
     *
     * @param string $arg
     */
    public function setIndicators($arg);

    /**
     * Set indicator 1.
     *
     * @api
     *
     * @param string $arg
     */
    public function setIndicator1($arg);

    /**
     * Get indicator 1.
     *
     * @api
     *
     * @return null|string $indicator1 The indicator 1
     */
    public function getIndicator1();

    /**
     * Set indicator 2.
     *
     * @api
     *
     * @param string $arg
     */
    public function setIndicator2($arg);

    /**
     * Get indicator 2.
     *
     * @api
     *
     * @return null|string $indicator2 The indicator 2
     */
    public function getIndicator2();

    /**
     * get array of subspecs.
     *
     * @api
     *
     * @return null|array
     */
    public function getSubSpecs();

    /**
     * add a subspec to the array of subspecs.
     *
     * @api
     *
     * @param SubSpecInterface|array[SubSpecInterface]
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
