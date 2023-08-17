<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemoRequest;

class IndexController extends Controller
{

    public function index(DemoRequest $request): array
    {
        dd($request->getId(),$request->getName());
    }
}
