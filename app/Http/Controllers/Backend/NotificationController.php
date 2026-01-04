<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get authenticated user with proper type hint for IDE support
     * @return User
     */
    private function getAuthenticatedUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $user->notifications();

        // Filter by service type if specified
        if ($request->has('type') && in_array($request->type, ['carpet', 'laundry'])) {
            $query->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                  ->whereJsonContains('data->service_type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);
        $currentFilter = $request->get('type', 'all');

        // Get notification counts by service type
        $stats = [
            'all' => $user->notifications()->count(),
            'carpet' => $user->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'carpet')->count(),
            'laundry' => $user->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'laundry')->count(),
        ];

        return view('backend.notifications.index', compact('notifications', 'currentFilter', 'stats'));
    }

    public function unread()
    {
        try {
            $user = $this->getAuthenticatedUser();

            // Get count efficiently without loading all records
            $count = $user->unreadNotifications()->count();

            // Only load the top 10 notifications we need
            $notifications = $user->unreadNotifications()
                ->latest()
                ->limit(10)
                ->get();

            return response()->json([
                'count' => $count,
                'notifications' => $notifications->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'message' => $notification->data['message'] ?? 'New notification',
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading notifications: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'count' => 0,
                'notifications' => [],
                'error' => 'Failed to load notifications'
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        $user = $this->getAuthenticatedUser();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        $user = $this->getAuthenticatedUser();
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function overdueDeliveries(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $user->notifications()
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification');

        // Filter by service type if specified
        if ($request->has('type') && in_array($request->type, ['carpet', 'laundry'])) {
            $query->whereJsonContains('data->service_type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);
        $currentFilter = $request->get('type', 'all');

        // Get overdue notification counts by service type
        $stats = [
            'all' => $user->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')->count(),
            'carpet' => $user->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'carpet')->count(),
            'laundry' => $user->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'laundry')->count(),
        ];

        // Load service data for each notification (more efficient approach)
        $serviceData = [];
        foreach ($notifications as $notification) {
            $data = $notification->data;
            $serviceType = $data['service_type'];
            $serviceId = $data['service_id'];

            if ($serviceType === 'carpet') {
                $service = \App\Models\Carpet::find($serviceId);
                if ($service) {
                    $serviceData[$notification->id] = [
                        'payment_status' => $service->payment_status,
                        'transaction_code' => $service->transaction_code,
                        'delivered' => $service->delivered,
                        'date_delivered' => $service->date_delivered,
                        'service_type' => 'carpet'
                    ];
                }
            } elseif ($serviceType === 'laundry') {
                $service = \App\Models\Laundry::find($serviceId);
                if ($service) {
                    $serviceData[$notification->id] = [
                        'status' => $service->status ?? 'Pending',
                        'delivery_date' => $service->delivery_date,
                        'notes' => $service->notes,
                        'service_type' => 'laundry'
                    ];
                }
            }
        }

        return view('backend.notifications.overdue', compact('notifications', 'currentFilter', 'stats', 'serviceData'));
    }

    public function quickUpdate(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $notification = $user->notifications()->find($id);

        if (!$notification || $notification->type !== 'App\\Notifications\\OverdueDeliveryNotification') {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $data = $notification->data;
        $serviceType = $data['service_type'];
        $serviceId = $data['service_id'];

        // Update the service based on type
        if ($serviceType === 'carpet') {
            $service = \App\Models\Carpet::find($serviceId);
        } elseif ($serviceType === 'laundry') {
            $service = \App\Models\Laundry::find($serviceId);
        }

        if (!$service) {
            return response()->json(['success' => false, 'message' => 'Service not found'], 404);
        }

        // Update service based on type with correct fields
        if ($serviceType === 'carpet') {
            $validatedData = $request->validate([
                'payment_status' => 'required|in:Not Paid,Paid',
                'transaction_code' => 'nullable|string|max:50',
                'delivered' => 'required|in:Not Delivered,Delivered',
                'date_delivered' => 'nullable|date'
            ]);

            $service->update([
                'payment_status' => $validatedData['payment_status'],
                'transaction_code' => $validatedData['transaction_code'],
                'delivered' => $validatedData['delivered'],
                'date_delivered' => $validatedData['date_delivered'] ?? $service->date_delivered,
            ]);
        } elseif ($serviceType === 'laundry') {
            // Keep original validation for laundry
            $validatedData = $request->validate([
                'status' => 'required|in:Pending,Ready for Delivery,Delivered,Cancelled',
                'delivery_date' => 'nullable|date',
                'notes' => 'nullable|string|max:500'
            ]);

            $service->update([
                'status' => $validatedData['status'],
                'delivery_date' => $validatedData['delivery_date'] ?? null,
                'notes' => $validatedData['notes'] ?? $service->notes,
            ]);
        }

        // If delivered or cancelled, remove all related overdue notifications
        $shouldRemove = false;
        if ($serviceType === 'carpet') {
            $shouldRemove = $validatedData['delivered'] === 'Delivered';
        } elseif ($serviceType === 'laundry') {
            $shouldRemove = in_array($validatedData['status'], ['Delivered', 'Cancelled']);
        }

        if ($shouldRemove) {
            $this->removeRelatedNotifications($serviceType, $serviceId);
        } else {
            // Just mark this notification as read
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'remove_notification' => $shouldRemove,
            'updated_data' => [
                'payment_status' => $service->payment_status ?? null,
                'transaction_code' => $service->transaction_code ?? null,
                'delivered' => $service->delivered ?? $service->status ?? null,
                'date_delivered' => $service->date_delivered ?? $service->delivery_date ?? null
            ]
        ]);
    }


    private function removeRelatedNotifications($serviceType, $serviceId)
    {
        // Remove all overdue notifications for this specific service
        $user = $this->getAuthenticatedUser();
        $user->notifications()
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereJsonContains('data->service_type', $serviceType)
            ->whereJsonContains('data->service_id', $serviceId)
            ->delete();
    }
}
