<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Carpet;
use App\Models\Laundry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerRetentionController extends Controller
{
    public function index(Request $request)
    {
        // Default filter parameters
        $inactiveMonths = $request->get('inactive_months', 2);
        $serviceType = $request->get('service_type', 'all');
        $phoneSearch = $request->get('phone_search', '');
        $uniqueIdSearch = $request->get('unique_id_search', '');
        $minValue = $request->get('min_value', '');
        $maxValue = $request->get('max_value', '');

        // Calculate cutoff date
        $cutoffDate = Carbon::now()->subMonths($inactiveMonths);

        // Get inactive customers
        $inactiveCustomers = $this->getInactiveCustomers(
            $cutoffDate,
            $serviceType,
            $phoneSearch,
            $uniqueIdSearch,
            $minValue,
            $maxValue
        );

        // Calculate summary statistics
        $stats = $this->calculateRetentionStats($cutoffDate);

        return view('backend.reports.customer-retention', compact(
            'inactiveCustomers',
            'stats',
            'inactiveMonths',
            'serviceType',
            'phoneSearch',
            'uniqueIdSearch',
            'minValue',
            'maxValue'
        ));
    }

    private function getInactiveCustomers($cutoffDate, $serviceType, $phoneSearch, $uniqueIdSearch, $minValue, $maxValue)
    {
        $carpetQuery = Carpet::select([
            'phone',
            'name',
            'location',
            DB::raw('MAX(date_received) as last_service_date'),
            DB::raw('COUNT(*) as total_services'),
            DB::raw('SUM(price) as total_value'),
            DB::raw('"Carpet" as service_type'),
            DB::raw('GROUP_CONCAT(uniqueid ORDER BY date_received DESC SEPARATOR ", ") as recent_unique_ids')
        ])
        ->where('date_received', '<', $cutoffDate)
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->groupBy('phone', 'name', 'location');

        $laundryQuery = Laundry::select([
            'phone',
            'name',
            'location',
            DB::raw('MAX(date_received) as last_service_date'),
            DB::raw('COUNT(*) as total_services'),
            DB::raw('SUM(COALESCE(total, price, 0)) as total_value'),
            DB::raw('"Laundry" as service_type'),
            DB::raw('GROUP_CONCAT(unique_id ORDER BY date_received DESC SEPARATOR ", ") as recent_unique_ids')
        ])
        ->where('date_received', '<', $cutoffDate)
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->groupBy('phone', 'name', 'location');

        // Apply filters
        if ($phoneSearch) {
            $carpetQuery->where('phone', 'LIKE', "%{$phoneSearch}%");
            $laundryQuery->where('phone', 'LIKE', "%{$phoneSearch}%");
        }

        if ($uniqueIdSearch) {
            $carpetQuery->where('uniqueid', 'LIKE', "%{$uniqueIdSearch}%");
            $laundryQuery->where('unique_id', 'LIKE', "%{$uniqueIdSearch}%");
        }

        // Service type filter
        $customers = collect();

        if ($serviceType === 'all' || $serviceType === 'carpet') {
            $carpetCustomers = $carpetQuery->get();
            $customers = $customers->merge($carpetCustomers);
        }

        if ($serviceType === 'all' || $serviceType === 'laundry') {
            $laundryCustomers = $laundryQuery->get();
            $customers = $customers->merge($laundryCustomers);
        }

        // Merge customers with same phone number across services
        $mergedCustomers = $customers->groupBy('phone')->map(function ($group) {
            $customer = $group->first();

            if ($group->count() > 1) {
                // Customer used both services - merge data
                $customer->service_type = 'Both Services';
                $customer->total_services = $group->sum('total_services');
                $customer->total_value = $group->sum('total_value');
                $customer->last_service_date = $group->max('last_service_date');
                $customer->recent_unique_ids = $group->pluck('recent_unique_ids')->join(', ');
            }

            return $customer;
        })->values();

        // Apply value filters
        if ($minValue) {
            $mergedCustomers = $mergedCustomers->where('total_value', '>=', $minValue);
        }

        if ($maxValue) {
            $mergedCustomers = $mergedCustomers->where('total_value', '<=', $maxValue);
        }

        // Sort by last service date (oldest first) and total value (highest first)
        return $mergedCustomers->sortBy([
            ['last_service_date', 'asc'],
            ['total_value', 'desc']
        ]);
    }

    private function calculateRetentionStats($cutoffDate)
    {
        // Total unique customers
        $totalCarpetCustomers = Carpet::distinct('phone')->whereNotNull('phone')->where('phone', '!=', '')->count();
        $totalLaundryCustomers = Laundry::distinct('phone')->whereNotNull('phone')->where('phone', '!=', '')->count();

        // Active customers (recent services)
        $activeCarpetCustomers = Carpet::distinct('phone')
            ->where('date_received', '>=', $cutoffDate)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        $activeLaundryCustomers = Laundry::distinct('phone')
            ->where('date_received', '>=', $cutoffDate)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        // Inactive customers
        $inactiveCarpetCustomers = $totalCarpetCustomers - $activeCarpetCustomers;
        $inactiveLaundryCustomers = $totalLaundryCustomers - $activeLaundryCustomers;

        // Revenue analysis
        $lostRevenueCarpet = Carpet::select(DB::raw('SUM(price) as total'))
            ->where('date_received', '<', $cutoffDate)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->first()->total ?? 0;

        $lostRevenueLaundry = Laundry::select(DB::raw('SUM(COALESCE(total, price, 0)) as total'))
            ->where('date_received', '<', $cutoffDate)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->first()->total ?? 0;

        // Average service values
        $avgCarpetValue = Carpet::whereNotNull('phone')->where('phone', '!=', '')->avg('price') ?? 0;
        $avgLaundryValue = Laundry::select(DB::raw('AVG(COALESCE(total, price, 0)) as avg_value'))
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->first()->avg_value ?? 0;

        return [
            'total_customers' => [
                'carpet' => $totalCarpetCustomers,
                'laundry' => $totalLaundryCustomers,
                'combined' => $totalCarpetCustomers + $totalLaundryCustomers
            ],
            'active_customers' => [
                'carpet' => $activeCarpetCustomers,
                'laundry' => $activeLaundryCustomers,
                'combined' => $activeCarpetCustomers + $activeLaundryCustomers
            ],
            'inactive_customers' => [
                'carpet' => $inactiveCarpetCustomers,
                'laundry' => $inactiveLaundryCustomers,
                'combined' => $inactiveCarpetCustomers + $inactiveLaundryCustomers
            ],
            'retention_rate' => [
                'carpet' => $totalCarpetCustomers > 0 ? round(($activeCarpetCustomers / $totalCarpetCustomers) * 100, 1) : 0,
                'laundry' => $totalLaundryCustomers > 0 ? round(($activeLaundryCustomers / $totalLaundryCustomers) * 100, 1) : 0,
            ],
            'potential_revenue' => [
                'carpet' => $inactiveCarpetCustomers * $avgCarpetValue,
                'laundry' => $inactiveLaundryCustomers * $avgLaundryValue,
                'total' => ($inactiveCarpetCustomers * $avgCarpetValue) + ($inactiveLaundryCustomers * $avgLaundryValue)
            ],
            'avg_service_value' => [
                'carpet' => round($avgCarpetValue, 2),
                'laundry' => round($avgLaundryValue, 2)
            ]
        ];
    }

    public function export(Request $request)
    {
        $inactiveMonths = $request->get('inactive_months', 2);
        $serviceType = $request->get('service_type', 'all');
        $phoneSearch = $request->get('phone_search', '');
        $uniqueIdSearch = $request->get('unique_id_search', '');
        $minValue = $request->get('min_value', '');
        $maxValue = $request->get('max_value', '');
        $format = $request->get('format', 'csv');

        $cutoffDate = Carbon::now()->subMonths($inactiveMonths);

        $inactiveCustomers = $this->getInactiveCustomers(
            $cutoffDate,
            $serviceType,
            $phoneSearch,
            $uniqueIdSearch,
            $minValue,
            $maxValue
        );

        $filename = "inactive-customers-{$inactiveMonths}months-" . date('Y-m-d-H-i-s');

        if ($format === 'csv') {
            return $this->exportCsv($inactiveCustomers, $filename);
        } else {
            return $this->exportExcel($inactiveCustomers, $filename);
        }
    }

    private function exportCsv($customers, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Phone', 'Name', 'Location', 'Service Type', 'Last Service Date',
                'Total Services', 'Total Value (KES)', 'Days Since Last Service',
                'Recent Unique IDs', 'Customer Tier'
            ]);

            // Data rows
            foreach ($customers as $customer) {
                $daysSinceLastService = Carbon::parse($customer->last_service_date)->diffInDays(now());
                $customerTier = $this->getCustomerTier($customer->total_value);

                fputcsv($file, [
                    $customer->phone,
                    $customer->name,
                    $customer->location,
                    $customer->service_type,
                    Carbon::parse($customer->last_service_date)->format('Y-m-d'),
                    $customer->total_services,
                    number_format($customer->total_value, 2),
                    $daysSinceLastService,
                    $customer->recent_unique_ids,
                    $customerTier
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($customers, $filename)
    {
        // For now, return CSV format
        // TODO: Implement proper Excel export with charts
        return $this->exportCsv($customers, $filename);
    }

    private function getCustomerTier($totalValue)
    {
        if ($totalValue >= 10000) return 'VIP';
        if ($totalValue >= 5000) return 'Premium';
        if ($totalValue >= 2000) return 'Regular';
        return 'Basic';
    }

    public function generateFollowUpList(Request $request)
    {
        $customerIds = $request->get('customer_ids', []);
        $format = $request->get('format', 'sms');

        if (empty($customerIds)) {
            return back()->with('error', 'Please select customers for follow-up');
        }

        // Get customer details
        $customers = collect();

        foreach ($customerIds as $phone) {
            $carpetCustomer = Carpet::where('phone', $phone)->first();
            $laundryCustomer = Laundry::where('phone', $phone)->first();

            $customer = $carpetCustomer ?? $laundryCustomer;
            if ($customer) {
                $customers->push($customer);
            }
        }

        if ($format === 'sms') {
            return $this->generateSmsTemplate($customers);
        } else {
            return $this->generateWhatsAppTemplate($customers);
        }
    }

    private function generateSmsTemplate($customers)
    {
        $template = "Hi {name}, we miss you at [Business Name]! It's been a while since your last {service} service. Book now and get 10% off! Call us at [Phone Number].";

        $messages = $customers->map(function($customer) use ($template) {
            return [
                'phone' => $customer->phone,
                'name' => $customer->name,
                'message' => str_replace(
                    ['{name}', '{service}'],
                    [$customer->name, 'carpet cleaning'],
                    $template
                )
            ];
        });

        return response()->json([
            'success' => true,
            'template' => $template,
            'messages' => $messages,
            'total_customers' => $messages->count()
        ]);
    }

    private function generateWhatsAppTemplate($customers)
    {
        $template = "Hello {name}! 👋\n\nWe hope you're doing well! It's been a while since we last serviced your carpets, and we wanted to reach out.\n\n🎉 Special Offer: Book your next carpet cleaning service and get 15% off!\n\n📞 Call us or reply to schedule.\n\nBest regards,\n[Your Business Name]";

        $messages = $customers->map(function($customer) use ($template) {
            return [
                'phone' => $customer->phone,
                'name' => $customer->name,
                'whatsapp_url' => "https://wa.me/" . preg_replace('/[^0-9]/', '', $customer->phone) . "?text=" . urlencode(str_replace('{name}', $customer->name, $template))
            ];
        });

        return response()->json([
            'success' => true,
            'template' => $template,
            'messages' => $messages,
            'total_customers' => $messages->count()
        ]);
    }
}