<?php

namespace App\Http\Controllers;

use App\Http\Traits\Message_Trait;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    use Message_Trait;
    public function index()
    {
        return view('website.checkout');
    }
    public function order(Request $request)
    {
        try {
            $data = $request->all();
            dd($data);

        }catch (\Exception $e){
            return $this->exception_message($e);
        }
    }
}
