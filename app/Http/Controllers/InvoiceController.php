<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generate()
    {
        $products = Product::all();

        $pdf = Pdf::loadView('invoices.invoice', compact('products'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-produk.pdf');
    }
}
