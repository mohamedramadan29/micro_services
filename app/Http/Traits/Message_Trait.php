<?php


namespace App\Http\Traits;

trait Message_Trait
{
    public function success_message($msg)
    {
        return redirect()->back()->with(['Success_message' => $msg]);
    }
    public function Error_message($msg)
    {
        return redirect()->back()->with(['Error_Message' => $msg]);
    }
    public function exception_message($e){
        return redirect()->back()->withErrors(['error'=>$e->getMessage()]);
    }

    /** A payment could not start: our own messages are shown, anything else is logged. */
    public function payment_error(\Throwable $e)
    {
        \Illuminate\Support\Facades\Log::warning('Checkout failed', ['error' => $e->getMessage()]);
        $message = $e instanceof \RuntimeException ? $e->getMessage() : 'تعذّر بدء الدفع الآن، حاول مرة أخرى بعد قليل';

        return redirect()->back()->withInput()->withErrors(['error' => $message]);
    }
}
