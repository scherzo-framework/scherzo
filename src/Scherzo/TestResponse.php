<?php declare(strict_types=1);

/**
 * A response for use in end-to-end testing - does not output anything.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Scherzo\Response;

class TestResponse extends Response {
    public function send() {
        // Don't do anything!
    }
}
