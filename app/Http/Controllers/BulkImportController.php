<?php

namespace App\Http\Controllers;

use App\Models\BulkImportLog;
use App\Services\BulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    public function __construct(protected BulkImportService $importService)
    {
    }

    /**
     * Display Bulk Import Control Center.
     */
    public function index()
    {
        $logs = BulkImportLog::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.import.index', compact('logs'));
    }

    /**
     * Handle CSV File Upload & Import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_type' => ['required', 'string', 'in:customers,services,products,staff'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // up to 10MB
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $type = $request->input('import_type');
        $file = $request->file('csv_file');
        $updateExisting = $request->boolean('update_existing', true);

        try {
            $result = $this->importService->import($type, $file, Auth::id(), $updateExisting);

            if ($result['status'] === 'completed') {
                return back()->with('success', "Import completed successfully! {$result['success']} of {$result['total']} rows processed without errors.");
            } elseif ($result['status'] === 'partial') {
                $errorMessages = collect($result['errors'])->take(5)->map(fn($e) => "Line {$e['line']}: {$e['error']}")->join('; ');
                return back()->with('warning', "Import completed with partial success. {$result['success']} rows imported, {$result['failed']} failed. Sample errors: {$errorMessages}");
            } else {
                $errorMessages = collect($result['errors'])->take(3)->map(fn($e) => "Line {$e['line']}: {$e['error']}")->join('; ');
                return back()->with('error', "Import failed. {$result['failed']} rows encountered errors. Details: {$errorMessages}");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Import encountered an unrecoverable error: ' . $e->getMessage());
        }
    }

    /**
     * Download Sample CSV Template.
     */
    public function downloadSample(string $type): StreamedResponse
    {
        $validTypes = ['customers', 'services', 'products', 'staff'];
        if (!in_array($type, $validTypes)) {
            abort(404, "Invalid template type requested.");
        }

        $csvContent = $this->importService->getSampleCsv($type);
        $fileName = "sample_{$type}_template.csv";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
