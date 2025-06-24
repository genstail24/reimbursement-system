<?php

namespace App\Http\Controllers;

use App\Helpers\HTTPResponseHelper;

abstract class Controller
{
    /**
     * @return HTTPResponseHelper
     */
    public function response(): HTTPResponseHelper
    {
        return new HTTPResponseHelper();
    }
}
