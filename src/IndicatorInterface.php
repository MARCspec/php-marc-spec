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
 * MARCspec indicator interface.
 */
interface IndicatorInterface
{
    /**
     * Constructor for MARCspec indicator.
     *
     * @api
     *
     * @param string $indicatorpos The indicator position
     */
    public function __construct($indicatorpos);

    /**
     * Get the indicator position.
     *
     * @api
     *
     * @return string $position The indicator position
     */
    public function getPos();

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
     * @param CK\MARCspec\SubSpecInterface|CK\MARCspec\SubSpecInterface[]
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
