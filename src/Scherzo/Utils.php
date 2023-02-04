<?php

/**
 * Utilities.
 *
 * @package     Scherzo
 * @link        https://github.com/scherzo-framework/scherzo
 * @copyright   Copyright (c) 2022 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license     [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

class Utils
{
    /**
     * Get the class name of an object without the namespace.
     */
    public static function getClass(object $obj): string
    {
        $className = get_class($obj);
        $pos = strrpos($className, '\\');
        if ($pos === false) {
            return $className;
        }
        return substr($className, $pos + 1);
    }

    /**
     * Generate a unique ID.
     */
    public static function getUuid()
    {
        // Impement RFC 4122 4.4.

        // Generate 128 bits of pseudo-random data.
        $data = random_bytes(16);

        // Set the two most significant bits (bits 6 and 7) of the
        // clock_seq_hi_and_reserved to zero and one, respectively.
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Set the four most significant bits (bits 12 through 15) of the
        // time_hi_and_version field to the 4-bit version number from
        // Section 4.1.3 (i.e. 4).
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // Convert the binary data to a string.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
