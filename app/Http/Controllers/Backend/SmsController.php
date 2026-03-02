<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\RobermsSmsService;
use App\Models\Carpet;
use App\Models\Laundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(RobermsSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show SMS dashboard
     */
    public function dashboard()
    {
        $balance = $this->smsService->getCreditBalance();

        // Get SMS statistics (you can create an sms_logs table later)
        $stats = [
            'balance' => $balance['balance'] ?? 0,
            'total_customers' => $this->getTotalCustomers(),
            'templates' => count(config('sms.templates')),
        ];

        return view('backend.sms.dashboard', compact('stats'));
    }

    /**
     * Show send SMS form
     */
    public function sendForm()
    {
        $templates = config('sms.templates');
        return view('backend.sms.send', compact('templates'));
    }

    /**
     * Send single SMS
     */
    public function sendSingle(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:480',
        ]);

        $result = $this->smsService->sendSms($request->phone, $request->message);

        if ($result['success']) {
            $notification = [
                'message' => 'SMS sent successfully!',
                'alert-type' => 'success'
            ];
        } else {
            $notification = [
                'message' => 'Failed to send SMS: ' . $result['message'],
                'alert-type' => 'error'
            ];
        }

        return redirect()->back()->with($notification);
    }

    /**
     * Show bulk SMS form
     */
    public function bulkForm()
    {
        $templates = config('sms.templates');

        // Get filter options
        $filters = [
            'all_carpets' => 'All Carpet Customers',
            'all_laundry' => 'All Laundry Customers',
            'unpaid_carpets' => 'Unpaid Carpet Orders',
            'unpaid_laundry' => 'Unpaid Laundry Orders',
            'ready_carpets' => 'Ready for Pickup (Carpets)',
            'ready_laundry' => 'Ready for Pickup (Laundry)',
            'inactive_customers' => 'Inactive Customers (60+ days)',
        ];

        return view('backend.sms.bulk', compact('templates', 'filters'));
    }

    /**
     * Preview bulk SMS recipients
     */
    public function previewRecipients(Request $request)
    {
        $filter = $request->filter;
        $recipients = $this->getRecipientsByFilter($filter);

        return response()->json([
            'count' => count($recipients),
            'recipients' => array_slice($recipients, 0, 10), // Preview first 10
        ]);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(Request $request)
    {
        // Remove PHP execution time limit so all messages go out
        set_time_limit(0);
        // Keep running even if the browser disconnects mid-send
        ignore_user_abort(true);

        $request->validate([
            'filter' => 'required|string',
            'message' => 'required|string|max:480',
        ]);

        $recipients = $this->getRecipientsByFilter($request->filter);

        if (empty($recipients)) {
            return redirect()->back()->with([
                'message'    => 'No recipients found for the selected filter.',
                'alert-type' => 'warning',
            ]);
        }

        // Get phone numbers only
        $phoneNumbers = array_column($recipients, 'phone');

        // Send bulk SMS
        $result = $this->smsService->sendBulkSms($phoneNumbers, $request->message);

        return redirect()->back()->with([
            'message'    => "Bulk SMS complete. Total: {$result['total']}, Sent: {$result['sent']}, Failed: {$result['failed']}",
            'alert-type' => $result['sent'] > 0 ? 'success' : 'error',
        ]);
    }

    /**
     * Send SMS to specific carpet customer
     */
    public function sendToCarpet($carpetId)
    {
        $carpet = Carpet::findOrFail($carpetId);

        if (!$carpet->phone) {
            $notification = [
                'message' => 'No phone number available for this customer.',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }

        // Get template based on status
        if ($carpet->delivered === 'Delivered') {
            $template = config('sms.templates.thank_you');
        } elseif ($carpet->payment_status === 'Not Paid') {
            $template = config('sms.templates.payment_reminder');
        } else {
            $template = config('sms.templates.ready_for_pickup');
        }

        $message = $this->replacePlaceholders($template, [
            'name' => $carpet->name ?? 'Customer',
            'uniqueid' => $carpet->uniqueid,
            'service' => 'Carpet',
            'amount' => number_format($carpet->price),
            'location' => $carpet->location ?? config('sms.business.location'),
            'phone' => config('sms.business.phone'),
            'company' => config('sms.business.name'),
        ]);

        $result = $this->smsService->sendSms($carpet->phone, $message);

        if ($result['success']) {
            $notification = [
                'message' => 'SMS sent successfully!',
                'alert-type' => 'success'
            ];
        } else {
            $notification = [
                'message' => 'Failed to send SMS: ' . $result['message'],
                'alert-type' => 'error'
            ];
        }

        return redirect()->back()->with($notification);
    }

    /**
     * Get credit balance
     */
    public function getBalance()
    {
        $result = $this->smsService->getCreditBalance();

        return response()->json($result);
    }

    /**
     * Get recipients by filter
     */
    private function getRecipientsByFilter($filter)
    {
        $recipients = [];

        switch ($filter) {
            case 'all_carpets':
                $recipients = Carpet::select('phone', DB::raw('MAX(name) as name'))
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'all_laundry':
                $recipients = Laundry::select('phone', DB::raw('MAX(name) as name'))
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'unpaid_carpets':
                $recipients = Carpet::select('phone', DB::raw('MAX(name) as name'))
                    ->where('payment_status', 'Not Paid')
                    ->where('delivered', 'Not Delivered')
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'unpaid_laundry':
                $recipients = Laundry::select('phone', DB::raw('MAX(name) as name'))
                    ->where('payment_status', 'Not Paid')
                    ->where('delivered', 'Not Delivered')
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'ready_carpets':
                $recipients = Carpet::select('phone', DB::raw('MAX(name) as name'))
                    ->where('delivered', 'Delivered')
                    ->where('payment_status', 'Not Paid')
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'ready_laundry':
                $recipients = Laundry::select('phone', DB::raw('MAX(name) as name'))
                    ->where('delivered', 'Delivered')
                    ->where('payment_status', 'Not Paid')
                    ->whereNotNull('phone')->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->get()->toArray();
                break;

            case 'inactive_customers':
                // Get customers who haven't had an order in 60+ days
                $cutoffDate = Carbon::now()->subDays(60);

                $carpetPhones = Carpet::select('phone', DB::raw('MAX(date_received) as last_order'))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->having('last_order', '<', $cutoffDate)
                    ->pluck('phone')
                    ->toArray();

                $laundryPhones = Laundry::select('phone', DB::raw('MAX(date_received) as last_order'))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->groupBy('phone')
                    ->having('last_order', '<', $cutoffDate)
                    ->pluck('phone')
                    ->toArray();

                $allPhones = array_unique(array_merge($carpetPhones, $laundryPhones));

                $recipients = array_map(function ($phone) {
                    return ['phone' => $phone, 'name' => 'Customer'];
                }, $allPhones);
                break;
        }

        return $recipients;
    }

    /**
     * Get total unique customers
     */
    private function getTotalCustomers()
    {
        $carpetPhones = Carpet::whereNotNull('phone')->distinct()->pluck('phone');
        $laundryPhones = Laundry::whereNotNull('phone')->distinct()->pluck('phone');

        return count(array_unique(array_merge($carpetPhones->toArray(), $laundryPhones->toArray())));
    }

    /**
     * Replace placeholders in message template
     */
    private function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace(':' . $key, $value, $template);
        }

        return $template;
    }

    /**
     * Show SMS history
     */
    public function history(Request $request)
    {
        $query = \App\Models\SmsLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by delivery status
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by phone or message
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $smsLogs = $query->paginate(50);

        // Statistics
        $stats = [
            'total' => \App\Models\SmsLog::count(),
            'sent' => \App\Models\SmsLog::where('status', 'sent')->count(),
            'failed' => \App\Models\SmsLog::where('status', 'failed')->count(),
            'today' => \App\Models\SmsLog::whereDate('created_at', today())->count(),
            'this_month' => \App\Models\SmsLog::whereMonth('created_at', now()->month)->count(),
        ];

        return view('backend.sms.history', compact('smsLogs', 'stats'));
    }

    /**
     * Show SMS statistics
     */
    public function statistics()
    {
        // Overall statistics
        $stats = [
            'total_sent' => \App\Models\SmsLog::where('status', 'sent')->count(),
            'total_failed' => \App\Models\SmsLog::where('status', 'failed')->count(),
            'total_sms' => \App\Models\SmsLog::count(),
            'today' => \App\Models\SmsLog::whereDate('created_at', today())->count(),
            'yesterday' => \App\Models\SmsLog::whereDate('created_at', today()->subDay())->count(),
            'this_week' => \App\Models\SmsLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => \App\Models\SmsLog::whereMonth('created_at', now()->month)->count(),
            'last_month' => \App\Models\SmsLog::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Success rate
        $stats['success_rate'] = $stats['total_sms'] > 0
            ? round(($stats['total_sent'] / $stats['total_sms']) * 100, 2)
            : 0;

        // Delivery statistics
        $stats['delivered'] = \App\Models\SmsLog::where('delivery_status', 'delivered')->count();
        $stats['submitted'] = \App\Models\SmsLog::where('delivery_status', 'submitted')->count();
        $stats['delivery_pending'] = \App\Models\SmsLog::where('delivery_status', 'pending')->count();
        $stats['delivery_failed'] = \App\Models\SmsLog::whereIn('delivery_status', ['failed', 'undelivered', 'rejected', 'expired'])->count();

        // Delivery rate (delivered vs total sent)
        $stats['delivery_rate'] = $stats['total_sent'] > 0
            ? round(($stats['delivered'] / $stats['total_sent']) * 100, 2)
            : 0;

        // SMS by type
        $byType = \App\Models\SmsLog::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        // SMS by status
        $byStatus = \App\Models\SmsLog::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // SMS by delivery status
        $byDeliveryStatus = \App\Models\SmsLog::select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->get();

        // Last 30 days chart data
        $last30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last30Days[] = [
                'date' => $date->format('M d'),
                'count' => \App\Models\SmsLog::whereDate('created_at', $date)->count(),
                'sent' => \App\Models\SmsLog::whereDate('created_at', $date)->where('status', 'sent')->count(),
                'failed' => \App\Models\SmsLog::whereDate('created_at', $date)->where('status', 'failed')->count(),
            ];
        }

        // Top recipients
        $topRecipients = \App\Models\SmsLog::select('phone_number', DB::raw('count(*) as count'))
            ->groupBy('phone_number')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Recent activity
        $recentActivity = \App\Models\SmsLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Balance
        $balance = $this->smsService->getCreditBalance();

        return view('backend.sms.statistics', compact(
            'stats',
            'byType',
            'byStatus',
            'byDeliveryStatus',
            'last30Days',
            'topRecipients',
            'recentActivity',
            'balance'
        ));
    }
}
