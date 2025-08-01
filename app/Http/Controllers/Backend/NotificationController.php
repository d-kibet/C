<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();
        
        // Filter by service type if specified
        if ($request->has('type') && in_array($request->type, ['carpet', 'laundry'])) {
            $query->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                  ->whereJsonContains('data->service_type', $request->type);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        $currentFilter = $request->get('type', 'all');
        
        // Get notification counts by service type
        $stats = [
            'all' => Auth::user()->notifications()->count(),
            'carpet' => Auth::user()->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'carpet')->count(),
            'laundry' => Auth::user()->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'laundry')->count(),
        ];
        
        return view('backend.notifications.index', compact('notifications', 'currentFilter', 'stats'));
    }

    public function unread()
    {
        $notifications = Auth::user()->unreadNotifications;
        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->take(10)->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'message' => $notification->data['message'] ?? 'New notification',
                ];
            })
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    public function overdueDeliveries(Request $request)
    {
        $query = Auth::user()->notifications()
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification');
            
        // Filter by service type if specified
        if ($request->has('type') && in_array($request->type, ['carpet', 'laundry'])) {
            $query->whereJsonContains('data->service_type', $request->type);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);
        $currentFilter = $request->get('type', 'all');
        
        // Get overdue notification counts by service type
        $stats = [
            'all' => Auth::user()->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')->count(),
            'carpet' => Auth::user()->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'carpet')->count(),
            'laundry' => Auth::user()->notifications()
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereJsonContains('data->service_type', 'laundry')->count(),
        ];
        
        return view('backend.notifications.overdue', compact('notifications', 'currentFilter', 'stats'));
    }
}