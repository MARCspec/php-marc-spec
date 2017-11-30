<?php
/**
 * MARCspec is the specification of a reference, encoded as string, to a set of data
 * from within a MARC record.
 *
 * @author Carsten Klee <mailme.klee@yahoo.de>
 * @copyright For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CK\MARCspec;

/**
 * MARCspec subfield interface.
 */
interface MARCspecInterface
{
    /**
     * Constructor for a MARCspec.
     *
     * For a minimal MARCspec a field tag must be provided by default
     *
     * @api
     *
     * @param string|CK\MARCspec\FieldInterface $spec The MARCspec as string or an instance of
     * FieldInterface
     */
    public function __construct($spec);

    /**
     * Set the field.
     *
     * @api
     *
     * @param CK\MARCspec\FieldInterface $field Instance of FieldInterface
     *
     * @return CK\MARCspec\MARCspecInterface Instance of MARCspecInterface
     */
    public static function setField(FieldInterface $field);

    /**
     * Get the field.
     *
     * @api
     *
     * @return CK\MARCspec\FieldInterface An instance of FieldInterface
     */
    public function getField();

    /**
     * Add subfields.
     *
     * @api
     *
     * @param string|CK\MARCspec\SubfieldInterface $subfields The subfield spec or instance of
     *                                                        SubfieldInterface
     */
    public function addSubfields($subfields);

    /**
     * Get array of subfields.
     *
     * @api
     *
     * @return null|CK\MARCspec\SubfieldInterface[] The array of instances of
     *                                              SubfieldInterface
     */
    public function getSubfields();

    /**
     * Get array of subfields with a specific tag.
     *
     * @api
     *
     * @param string $subfieldTag The subfield tag
     *
     * @return null|CK\MARCspec\SubfieldInterface[] $subfields The array of
     *                                              instances of SubfieldInterface with a specific tag
     */
    public function getSubfield($subfieldTag);

    /**
     * Get the Indicator.
     *
     * @api
     *
     * @return null|CK\MARCspec\IndicatorInterface An instance of IndicatorInterface
     */
    public function getIndicator();

    /**
     * Set the Indicator.
     *
     * @api
     *
     * @param CK\MARCspec\IndicatorInterface $indicator An instance of IndicatorInterface
     */
    public function setIndicator(IndicatorInterface $indicator);

    /**
     * Encodes MARCspec as string.
     *
     * @api
     *
     * @return string The MARCspec as string
     */
    public function __toString();

    /**
     * Serialize MARCspec as JSON.
     *
     * @api
     *
     * @return string The MARCspec as JSON encoded
     */
    public function jsonSerialize();
} // EOI
