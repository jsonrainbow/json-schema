<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints\Formats;

use InvalidArgumentException;

class PhoneValidator
{
  public function __invoke($element)
  {
    if(!preg_match('/^\+?(\(\d{3}\)|\d{3}) \d{3} \d{4}$/', $element)) {
      throw new InvalidArgumentException('Invalid phone number');
    }
  }
}