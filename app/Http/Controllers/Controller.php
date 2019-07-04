<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Transformers\Json;

class Controller extends BaseController
{
    protected function buildFailedValidationResponse(Request $request, array $errors) {
	    return Json::exception('Oops, Something Error', $errors);
	}
}
