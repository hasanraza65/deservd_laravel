<?php

namespace App\Exceptions;

use Exception;

/** Generic "this checkout cannot proceed" error — unavailable product, invalid shipping method, etc. */
class CheckoutValidationException extends Exception
{
    //
}
