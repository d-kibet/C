<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'creator', 'approver'])
                        ->latest('expense_date');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        // Filter by approval status
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        $expenses = $query->paginate(15);
        $categories = ExpenseCategory::active()->ordered()->get();

        // Calculate summary stats
        $todayTotal = Expense::today()->approved()->sum('amount');
        $monthTotal = Expense::thisMonth()->approved()->sum('amount');
        $pendingCount = Expense::pending()->count();

        return view('backend.expenses.index', compact(
            'expenses', 
            'categories', 
            'todayTotal', 
            'monthTotal', 
            'pendingCount'
        ));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->ordered()->get();
        // Get unique vendor names (MySQL compatible)
        $recentVendors = Expense::distinct()
                               ->pluck('vendor_name')
                               ->filter()
                               ->take(10);

        return view('backend.expenses.create', compact('categories', 'recentVendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'required|string|max:200',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:Cash,M-Pesa,Bank Transfer,Cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,heic,heif,avif,bmp,gif|max:5120|dimensions:max_width=4000,max_height=4000',
            'notes' => 'nullable|string|max:500',
        ]);

        $category = ExpenseCategory::findOrFail($validated['category_id']);

        // Determine approval status
        $approvalStatus = 'Approved';
        if ($category->requires_approval && $validated['amount'] > 5000) {
            $approvalStatus = 'Pending';
        }

        // Remove receipt_image from validated data since we handle it separately
        unset($validated['receipt_image']);

        $expense = Expense::create(array_merge($validated, [
            'approval_status' => $approvalStatus,
            'created_by' => Auth::id(),
        ]));

        // Handle receipt upload - simple and reliable approach
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            
            // Basic validation
            if ($file->isValid()) {
                // Create receipts directory if it doesn't exist
                $receiptsPath = storage_path('app/public/receipts');
                if (!is_dir($receiptsPath)) {
                    mkdir($receiptsPath, 0755, true);
                }
                
                // Generate unique filename
                $fileName = 'receipt_' . time() . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                
                // Store the file
                $receiptPath = $file->storeAs('receipts', $fileName, 'public');
                
                if ($receiptPath) {
                    $expense->update(['receipt_image' => $receiptPath]);
                }
            }
        }

        $message = $approvalStatus === 'Pending' 
                 ? 'Expense recorded and sent for approval' 
                 : 'Expense recorded successfully';

        return redirect()->route('expenses.index')->with([
            'message' => $message,
            'alert-type' => 'success'
        ]);
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'creator', 'approver']);
        return view('backend.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->ordered()->get();
        // Get unique vendor names (MySQL compatible)
        $recentVendors = Expense::distinct()
                               ->pluck('vendor_name')
                               ->filter()
                               ->take(10);

        return view('backend.expenses.edit', compact('expense', 'categories', 'recentVendors'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'required|string|max:200',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:Cash,M-Pesa,Bank Transfer,Cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,heic,heif,avif,bmp,gif|max:5120|dimensions:max_width=4000,max_height=4000',
            'notes' => 'nullable|string|max:500',
        ]);

        // Handle receipt upload - simple and reliable approach
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            
            // Basic validation
            if ($file->isValid()) {
                // Delete old receipt if exists
                if ($expense->receipt_image && Storage::disk('public')->exists($expense->receipt_image)) {
                    Storage::disk('public')->delete($expense->receipt_image);
                }
                
                // Create receipts directory if it doesn't exist
                $receiptsPath = storage_path('app/public/receipts');
                if (!is_dir($receiptsPath)) {
                    mkdir($receiptsPath, 0755, true);
                }
                
                // Generate unique filename
                $fileName = 'receipt_' . time() . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                
                // Store the file
                $receiptPath = $file->storeAs('receipts', $fileName, 'public');
                
                if ($receiptPath) {
                    $validated['receipt_image'] = $receiptPath;
                }
            }
        }

        // Remove receipt_image from validated data if no file was uploaded
        if (!$request->hasFile('receipt_image')) {
            unset($validated['receipt_image']);
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')->with([
            'message' => 'Expense updated successfully',
            'alert-type' => 'success'
        ]);
    }

    public function destroy(Expense $expense)
    {
        // Clear all media (this will automatically delete files)
        $expense->clearMediaCollection('receipts');
        
        // Delete old receipt file if it exists (for backward compatibility)
        if ($expense->receipt_image && Storage::disk('public')->exists($expense->receipt_image)) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with([
            'message' => 'Expense deleted successfully',
            'alert-type' => 'success'
        ]);
    }

    public function serveReceipt(Expense $expense)
    {
        // Check if user has permission to view expense
        if (!$expense->receipt_image) {
            abort(404, 'Receipt not found');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($expense->receipt_image)) {
            abort(404, 'Receipt file not found');
        }

        // Get file path and info
        $filePath = Storage::disk('public')->path($expense->receipt_image);
        $mimeType = Storage::disk('public')->mimeType($expense->receipt_image);
        
        // Serve file with proper headers
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
        ]);
    }


    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:200',
            'vendor_name' => 'nullable|string|max:100',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'vendor_name' => $validated['vendor_name'] ?? 'Quick Entry',
            'expense_date' => today(),
            'payment_method' => 'Cash',
            'approval_status' => 'Approved',
            'created_by' => Auth::id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully',
            'expense' => $expense->load('category')
        ]);
    }

    public function approve(Expense $expense)
    {
        if ($expense->approval_status !== 'Pending') {
            return redirect()->back()->with([
                'message' => 'Expense is not pending approval',
                'alert-type' => 'error'
            ]);
        }

        $expense->update([
            'approval_status' => 'Approved',
            'approved_by' => Auth::id()
        ]);

        return redirect()->back()->with([
            'message' => 'Expense approved successfully',
            'alert-type' => 'success'
        ]);
    }

    public function reject(Expense $expense)
    {
        if ($expense->approval_status !== 'Pending') {
            return redirect()->back()->with([
                'message' => 'Expense is not pending approval',
                'alert-type' => 'error'
            ]);
        }

        $expense->update([
            'approval_status' => 'Rejected',
            'approved_by' => Auth::id()
        ]);

        return redirect()->back()->with([
            'message' => 'Expense rejected',
            'alert-type' => 'success'
        ]);
    }

    public function dashboard()
    {
        // Today's summary
        $todayExpenses = Expense::today()->approved();
        $todayTotal = $todayExpenses->sum('amount');
        $todayCount = $todayExpenses->count();

        // Month's summary
        $monthExpenses = Expense::thisMonth()->approved();
        $monthTotal = $monthExpenses->sum('amount');

        // Category breakdown (this month)
        $categoryBreakdown = ExpenseCategory::active()
            ->withSum(['expenses' => function($query) {
                $query->thisMonth()->approved();
            }], 'amount')
            ->get()
            ->map(function($category) {
                return [
                    'name' => $category->name,
                    'amount' => $category->expenses_sum_amount ?? 0,
                    'budget' => $category->budget_limit,
                    'color' => $category->color_code,
                    'icon' => $category->icon_class,
                ];
            });

        // Recent expenses
        $recentExpenses = Expense::with('category')
                                ->latest()
                                ->limit(5)
                                ->get();

        // Pending approvals
        $pendingCount = Expense::pending()->count();

        return view('backend.expenses.dashboard', compact(
            'todayTotal',
            'todayCount', 
            'monthTotal',
            'categoryBreakdown',
            'recentExpenses',
            'pendingCount'
        ));
    }

    public function viewExpensesByMonth(Request $request)
    {
        // Default to current month/year if none provided
        $month = (int) $request->input('month', Carbon::now()->format('m'));
        $year  = (int) $request->input('year', Carbon::now()->format('Y'));

        // Determine the start and end of that month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate   = $startDate->copy()->endOfMonth();

        // Fetch all expenses in that month
        $expenses = Expense::with(['category', 'creator'])
                          ->whereBetween('expense_date', [$startDate, $endDate])
                          ->approved()
                          ->orderBy('expense_date', 'desc')
                          ->get();

        // Calculate totals by category
        $categoryTotals = $expenses->groupBy('category_id')->map(function($categoryExpenses) {
            return [
                'category' => $categoryExpenses->first()->category,
                'total' => $categoryExpenses->sum('amount'),
                'count' => $categoryExpenses->count(),
                'expenses' => $categoryExpenses
            ];
        });

        // Calculate overall totals
        $totalExpenses = $expenses->sum('amount');
        $totalCount = $expenses->count();

        // Get top vendors
        $topVendors = $expenses->groupBy('vendor_name')
                              ->map(function($vendorExpenses) {
                                  return [
                                      'vendor' => $vendorExpenses->first()->vendor_name,
                                      'total' => $vendorExpenses->sum('amount'),
                                      'count' => $vendorExpenses->count()
                                  ];
                              })
                              ->sortByDesc('total')
                              ->take(5);

        // Daily breakdown
        $dailyBreakdown = $expenses->groupBy(function($expense) {
            return $expense->expense_date->format('Y-m-d');
        })->map(function($dayExpenses) {
            return [
                'date' => $dayExpenses->first()->expense_date,
                'total' => $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count()
            ];
        })->sortBy('date');

        return view('reports.expenses_month', [
            'month' => $month,
            'year' => $year,
            'expenses' => $expenses,
            'categoryTotals' => $categoryTotals,
            'totalExpenses' => $totalExpenses,
            'totalCount' => $totalCount,
            'topVendors' => $topVendors,
            'dailyBreakdown' => $dailyBreakdown,
            'monthName' => Carbon::createFromDate($year, $month, 1)->format('F Y')
        ]);
    }

    public function downloadExpensesByMonth(Request $request)
    {
        $month = (int) $request->input('month', Carbon::now()->format('m'));
        $year  = (int) $request->input('year', Carbon::now()->format('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate   = $startDate->copy()->endOfMonth();

        $expenses = Expense::with(['category', 'creator'])
                          ->whereBetween('expense_date', [$startDate, $endDate])
                          ->approved()
                          ->orderBy('expense_date', 'desc')
                          ->get();

        $totalExpenses = $expenses->sum('amount');

        $filename = "expenses_{$year}_{$month}.csv";
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($expenses, $totalExpenses) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date', 'Category', 'Description', 'Vendor', 'Amount', 
                'Payment Method', 'Transaction Ref', 'Created By', 'Notes'
            ]);

            // Data rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->category->name,
                    $expense->description,
                    $expense->vendor_name,
                    $expense->amount,
                    $expense->payment_method,
                    $expense->transaction_reference,
                    $expense->creator->name,
                    $expense->notes
                ]);
            }

            // Blank line and total
            fputcsv($file, []);
            fputcsv($file, ['Total Expenses', '', '', '', $totalExpenses]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}