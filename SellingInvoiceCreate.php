<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\admin\Safe;
use App\Models\admin\Client;
use App\Models\admin\Supplier;
use App\Models\admin\Category;
use App\Models\admin\PurcheInvoice;

class SellingInvoiceCreate extends Component
{

    public $suppliers,$safes,$categories,$clients,$client_id,$client_mobile,$client_whatsapp;
    public $client_email,$client_address,$supplier_id,$supplier_mobile,$supplier_whatsapp;
    public $supplier_email,$supplier_address,$qyt = 1,$selling_price = 0;
    public $total_price = 0,$paid = 0,$remaining = 0;
    public $payment_method,$safe_id,$invoice,$referance_number,$referance_error,$bayan_txt,$category_id,$selling_invoice;


    public $purchesInvoices;
    public function mount($selling_invoice = null)
    {
        $this->purchesInvoices = PurcheInvoice::where('status','available')->where('return_status','not_returned')->get();
        $this->selling_invoice = $selling_invoice;

        // جلب بيانات الموردين والخزائن
        $this->suppliers = Supplier::active()->get();
        $this->safes = Safe::active()->get();
        // إذا كانت هناك فاتورة (وضع التعديل)
        if ($this->selling_invoice) {
            $this->referance_number = $this->selling_invoice->referance_number;
            $this->bayan_txt = $this->selling_invoice->bayan_txt;
            $this->category_id = $this->selling_invoice->category_id;
            $this->qyt = $this->selling_invoice->qyt;
            $this->selling_price = $this->selling_invoice->selling_price;
            $this->supplier_id = $this->selling_invoice->supplier_id;
            $this->client_id = $this->selling_invoice->client_id;
            $this->paid = $this->selling_invoice->paid;
            $this->total_price = $this->selling_invoice->total_price;
            $this->remaining = $this->selling_invoice->remaining;
            $this->payment_method = $this->selling_invoice->payment_method;
            $this->safe_id = $this->selling_invoice->safe_id;
        } else {
            // إذا لم تكن هناك فاتورة (وضع الإضافة)، استخدم القيم القديمة أو الافتراضية
            $this->referance_number = old('referance_number');
            $this->bayan_txt = old('bayan_txt');
            $this->category_id = old('category_id');
            $this->qyt = old('qyt', 1);
            $this->selling_price = old('selling_price', 0);
            $this->supplier_id = old('supplier_id');
            $this->client_id = old('client_id');
            $this->paid = old('paid', 0);
            $this->total_price = old('total_price', 0);
            $this->remaining = old('remaining', 0);
            $this->payment_method = old('payment_method');
            $this->safe_id = old('safe_id');
        }

        // تحميل بيانات المورد إذا كان هناك supplier_id
        if ($this->supplier_id) {
            $this->getSupplierInfo();
        }
        if($this->client_id){
            $this->getClientInfo();
        }
        // حساب السعر الكلي والباقي عند التحميل
        $this->calculateTotalPrice();
        $this->calculateRemaining();
    }






    ##########3 Start Check Referance Number

    public function updatedReferanceNumber()
    {
      //  dd('test');
        $this->checkReferanceNumber($this->referance_number);

    }

    public function checkReferanceNumber($referance_number)
    {
       // dd($referance_number);
        $invoice = PurcheInvoice::where('referance_number', $referance_number)->first();

        if (!$invoice) {
            $this->referance_error = 'رقم الفاتورة غير موجود';
        }
        elseif($invoice->client_id){
            $this->referance_error = 'رقم الفاتورة مرتبط بالعميل';
        }
        // elseif($invoice->type == 'فاتورة مؤقتة'){
        //  //   $this->referance_error = ' الفاتورة مؤقتة وغير رسمية الي الان  ';
        // }

        else {
            $this->referance_error = '';
            $this->invoice = $invoice;
            $this->bayan_txt = $invoice->bayan_txt;
            $this->category_id = $invoice->category_id;
            $this->supplier_id = $invoice->supplier_id;
            $this->supplier_mobile = $invoice->supplier->mobile;
            $this->supplier_whatsapp = $invoice->supplier->whatsapp;
            $this->supplier_email = $invoice->supplier->email;
            $this->supplier_address = $invoice->supplier->address;
            $this->qyt = $invoice->qyt;
        }
    }


    public function updated($propertyName)
    {
        // تحديث السعر الكلي والباقي عند تغيير qyt أو purches_price
        if (in_array($propertyName, ['qyt', 'selling_price'])) {
            $this->calculateTotalPrice();
            $this->calculateRemaining();
        }

        // تحديث الباقي عند تغيير paid
        if ($propertyName === 'paid') {
            $this->calculateRemaining();
        }

        // تحديث بيانات المورد عند تغيير supplier_id
        if ($propertyName === 'supplier_id') {
            $this->getSupplierInfo();
        }
    }

    // public function calculateTotalPrice()
    // {
    //     $this->total_price = ($this->qyt && $this->selling_price) ? $this->qyt * $this->selling_price : 0;
    // }

    // public function calculateRemaining()
    // {
    //     $this->paid = (int) ($this->paid ?? 0); // التأكد من أن paid ليس null
    //     $this->remaining = ($this->paid > $this->total_price) ? 0 : $this->total_price - $this->paid;
    // }

    public function calculateTotalPrice()
{
    // التأكد من أن qyt و selling_price أرقام
    $qyt = is_numeric($this->qyt) ? (float)$this->qyt : 0;
    $selling_price = is_numeric($this->selling_price) ? (float)$this->selling_price : 0;

    $this->total_price = ($qyt && $selling_price) ? $qyt * $selling_price : 0;
}

public function calculateRemaining()
{
    // التأكد من أن paid ليس null وهو رقم
    $this->paid = is_numeric($this->paid) ? (float)$this->paid : 0;

    // التأكد من أن total_price رقم
    $total_price = is_numeric($this->total_price) ? (float)$this->total_price : 0;

    // حساب المتبقي
    $this->remaining = ($this->paid > $total_price) ? 0 : $total_price - $this->paid;
}


    public function getClientInfo()
    {
        if (!$this->client_id) {
            $this->resetClientInfo();
            return;
        }

        $client = Client::find($this->client_id);
        if ($client) {
            $this->client_mobile = $client->mobile ?? '';
            $this->client_whatsapp = $client->whatsapp ?? '';
            $this->client_email = $client->email ?? '';
            $this->client_address = $client->address ?? '';
        } else {
            $this->resetClientInfo();
        }
    }

    private function resetClientInfo()
    {
        $this->client_mobile = '';
        $this->client_whatsapp = '';
        $this->client_email = '';
        $this->client_address = '';
    }

    public function getSupplierInfo()
    {
        if (!$this->supplier_id) {
            $this->resetSupplierInfo();
            return;
        }

        $supplier = Supplier::find($this->supplier_id);
        if ($supplier) {
            $this->supplier_mobile = $supplier->mobile ?? '';
            $this->supplier_whatsapp = $supplier->whatsapp ?? '';
            $this->supplier_email = $supplier->email ?? '';
            $this->supplier_address = $supplier->address ?? '';
        } else {
            $this->resetSupplierInfo();
        }
    }

    private function resetSupplierInfo()
    {
        $this->supplier_mobile = '';
        $this->supplier_whatsapp = '';
        $this->supplier_email = '';
        $this->supplier_address = '';
    }

    public function refreshClients()
    {
        $this->clients = Client::active()->get();
    }

    public function refreshSuppliers()
    {
        $this->suppliers = Supplier::active()->get();
    }

    public function render()
    {
        $this->clients = Client::active()->get();
        $this->categories = Category::active()->get();
        return view('livewire.dashboard.selling-invoice-create');
    }
}
