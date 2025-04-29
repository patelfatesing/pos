<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use App\Models\Product; // your model
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    public function showUploadForm()
    {

        return view('products_import.import');
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $path = $request->file('file')->store('temp');

        $data = Excel::toCollection(null, storage_path('app/' . $path));

        $headings = $data[0][0]->keys()->toArray();

        Session::put('import_file', $path);
        Session::put('import_data', $data[0]);

        return view('products.mapping', compact('headings'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'mapping' => 'required|array'
        ]);

        $path = Session::get('import_file');
        $data = Session::get('import_data');

        if (!$data) {
            return redirect()->route('products.import')->with('error', 'No data found. Please upload again.');
        }

        foreach ($data as $row) {
            Product::create([
                'name' => $row[$request->mapping['name']] ?? null,
                'sku' => $row[$request->mapping['sku']] ?? null,
                'price' => $row[$request->mapping['price']] ?? 0,
                'stock' => $row[$request->mapping['stock']] ?? 0,
            ]);
        }

        Session::forget('import_file');
        Session::forget('import_data');

        return redirect()->route('products.import')->with('success', 'Products imported successfully.');
    }
}
