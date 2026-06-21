<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileConvertController extends Controller
{
    public function index()
    {
        return view('converter.index');
    }

    public function convert(Request $request)
{
    $request->validate([
        'file' => 'required|file|max:5120',
    ]);

    $file = $request->file('file');
    $path = $file->getPathname();

    try {
        // 🔥 AUTO DETECT FORMAT
        $reader = IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);

    } catch (\Exception $e) {
        return back()->withErrors([
            'file' => 'File tidak dapat dibaca. Pastikan file benar-benar XLS atau CSV.'
        ]);
    }

    $writer = new Xlsx($spreadsheet);

    $fileName = 'converted_' . time() . '.xlsx';
    $tempPath = storage_path('app/' . $fileName);

    $writer->save($tempPath);

    return response()->download($tempPath)->deleteFileAfterSend(true);
}
}