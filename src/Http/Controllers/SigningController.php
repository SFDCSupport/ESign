<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Http\Requests\SigningRequest;

class SigningController extends Controller
{
    public function index(Request $request)
    {
        return view('esign::signer.index');
    }

    public function store(SigningRequest $request)
    {
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
