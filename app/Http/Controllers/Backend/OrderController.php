<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * All Orders listing page.
     */
    public function index()
    {
        return view('backend.orders.index');
    }

    /**
     * Server-side DataTables data for All Orders.
     */
    public function getOrdersData(Request $request)
    {
        try {
            $draw             = (int) $request->input('draw', 1);
            $start            = (int) $request->input('start', 0);
            $length           = min((int) $request->input('length', 25), 100);
            $search           = $request->input('search.value', '');
            $orderColumnIndex = (int) $request->input('order.0.column', 0);
            $orderDirection   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

            $columns     = ['phone', 'date_received', 'payment_status'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'date_received';

            $canCarpet  = Gate::allows('carpet.all');
            $canLaundry = Gate::allows('laundry.all');

            $query = Order::withCount('items')->with('items');

            if ($canCarpet && !$canLaundry) {
                $query->where('type', 'carpet');
                $totalRecords = Order::where('type', 'carpet')->count();
            } elseif ($canLaundry && !$canCarpet) {
                $query->where('type', 'laundry');
                $totalRecords = Order::where('type', 'laundry')->count();
            } else {
                $totalRecords = Order::count();
            }

            if (!empty($search)) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('payment_status', 'like', "%{$search}%")
                      ->orWhereHas('items', fn($qi) => $qi->where('unique_id', 'like', "%{$search}%"));
                });
            }

            $filteredRecords = $query->count();

            $orders = $query->orderBy($orderColumn, $orderDirection)
                            ->skip($start)
                            ->take($length)
                            ->get();

            $data = [];
            foreach ($orders as $order) {
                $deliveredCount = $order->items->where('delivered', 'Delivered')->count();
                $totalItems     = $order->items_count;
                $isLocked       = $order->payment_status === 'Paid' && $deliveredCount === $totalItems && $totalItems > 0;

                $uids = $order->items->pluck('unique_id')->filter()->values();
                if ($uids->isEmpty()) {
                    $uniqueIds = '<span class="text-muted">—</span>';
                } else {
                    $visible = $uids->take(2)
                        ->map(fn($u) => '<span class="badge bg-light text-dark border me-1">' . e($u) . '</span>')
                        ->implode('');
                    $extra = $uids->count() - 2;
                    if ($extra > 0) {
                        $all = e($uids->implode(', '));
                        $visible .= '<span class="badge bg-secondary ms-1 uid-more" style="cursor:default" '
                            . 'data-bs-toggle="tooltip" data-bs-placement="top" title="' . $all . '">+'
                            . $extra . ' more</span>';
                    }
                    $uniqueIds = $visible;
                }

                $payBadge = match($order->payment_status) {
                    'Paid'     => '<span class="badge bg-success">Paid</span>',
                    'Partial'  => '<span class="badge bg-warning text-dark">Partial</span>',
                    default    => '<span class="badge bg-danger">Not Paid</span>',
                };

                $deliveryBadge = $totalItems > 0
                    ? "<span class=\"badge bg-secondary\">{$deliveredCount}/{$totalItems}</span>"
                    : '<span class="badge bg-light text-dark">—</span>';

                $isCarpetOrder  = $order->type === 'carpet';
                $canViewOrder   = $isCarpetOrder ? Gate::allows('carpet.details') : Gate::allows('laundry.all');
                $canEditOrder   = $isCarpetOrder ? Gate::allows('carpet.edit')    : Gate::allows('laundry.all');
                $canDeleteOrder = $isCarpetOrder ? Gate::allows('carpet.delete')  : Gate::allows('laundry.all');

                $dropId  = 'act-' . $order->id;
                $items   = '';

                if ($canViewOrder) {
                    $items .= '<li><a class="dropdown-item" href="' . route('orders.show', $order->id) . '">'
                        . '<i class="fa fa-eye me-2 text-info"></i> View Details</a></li>';
                }

                if ($canEditOrder && !$isLocked) {
                    $items .= '<li><a class="dropdown-item" href="' . route('orders.edit', $order->id) . '">'
                        . '<i class="fa fa-pencil me-2 text-secondary"></i> Edit Order</a></li>';
                }

                if ($order->payment_status !== 'Paid') {
                    $items .= '<li><a class="dropdown-item mpesa-btn" href="#" '
                        . 'data-service-type="order" '
                        . 'data-service-id="' . $order->id . '" '
                        . 'data-phone="' . e($order->phone) . '" '
                        . 'data-amount="' . $order->total . '" '
                        . 'data-name="' . e($order->order_number . ' — ' . $order->name) . '">'
                        . '<i class="mdi mdi-cellphone me-2 text-success"></i> Send M-Pesa</a></li>';
                }

                if ($canDeleteOrder && (!$isLocked || Gate::allows('admin.all'))) {
                    $items .= '<li><hr class="dropdown-divider"></li>'
                        . '<li><a class="dropdown-item text-danger delete-order-btn" href="#" '
                        . 'data-id="' . $order->id . '" '
                        . 'data-label="' . e($order->order_number) . '">'
                        . '<i class="fa fa-trash me-2"></i> Delete</a></li>'
                        . '<form id="del-' . $order->id . '" action="' . route('orders.destroy', $order->id) . '" method="POST" style="display:none">'
                        . csrf_field() . method_field('DELETE') . '</form>';
                }

                $actions = '<div class="dropdown">'
                    . '<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" '
                    . 'id="' . $dropId . '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>'
                    . '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="' . $dropId . '">'
                    . $items
                    . '</ul></div>';

                $data[] = [
                    'unique_ids'     => $uniqueIds,
                    'phone'          => e($order->phone),
                    'date_received'  => e($order->date_received?->format('d M Y')),
                    'items_count'    => $totalItems,
                    'total'          => 'KES ' . number_format($order->total, 2),
                    'payment_status' => $payBadge,
                    'payment_date'   => $order->payment_date ? $order->payment_date->format('d M Y') : '<span class="text-muted">—</span>',
                    'delivery'       => $deliveryBadge,
                    'actions'        => $actions,
                ];
            }

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'An error occurred while fetching data.',
            ], 500);
        }
    }

    /**
     * Show the create order form.
     */
    public function create()
    {
        return view('backend.orders.create');
    }

    /**
     * Store a new order with items.
     */
    public function store(StoreOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $order = Order::create([
                'order_number'   => Order::generateOrderNumber(),
                'type'           => $request->type,
                'name'           => $request->name,
                'phone'          => $request->phone,
                'location'       => $request->location,
                'date_received'  => $request->date_received,
                'date_delivered' => $request->date_delivered,
                'payment_status' => $request->payment_status,
                'transaction_code' => $request->transaction_code,
                'notes'          => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $price    = (float) ($itemData['price'] ?? 0);
                $discount = (float) ($itemData['discount'] ?? 0);

                $order->items()->create([
                    'unique_id'        => $itemData['unique_id'] ?? null,
                    'price'            => $price,
                    'discount'         => $discount,
                    'item_total'       => max(0, $price - $discount),
                    'delivered'        => 'Not Delivered',
                    'size'             => $itemData['size'] ?? null,
                    'multiplier'       => $itemData['multiplier'] ?? null,
                    'quantity'         => $itemData['quantity'] ?? null,
                    'item_description' => $itemData['item_description'] ?? null,
                    'weight'           => $itemData['weight'] ?? null,
                ]);
            }

            $order->recalculateTotals();
        });

        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Show order details.
     */
    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return view('backend.orders.show', compact('order'));
    }

    /**
     * Show the edit order form.
     */
    public function edit($id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->isLocked() && !Gate::allows('admin.all')) {
            return redirect()->route('orders.show', $id)
                ->with('error', 'This order is locked (Paid & Delivered) and cannot be edited.');
        }

        return view('backend.orders.edit', compact('order'));
    }

    /**
     * Update the order and its items.
     */
    public function update(UpdateOrderRequest $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->isLocked() && !Gate::allows('admin.all')) {
            return redirect()->route('orders.show', $id)
                ->with('error', 'This order is locked and cannot be edited.');
        }

        DB::transaction(function () use ($request, $order) {
            $wasNotPaid   = $order->payment_status !== 'Paid';
            $nowPaid      = $request->payment_status === 'Paid';
            $paymentDate  = $order->payment_date;

            if ($nowPaid && $wasNotPaid) {
                // Being marked Paid for the first time — stamp today
                $paymentDate = now()->toDateString();
            } elseif (!$nowPaid) {
                // Reverted to Not Paid or Partial — clear the date
                $paymentDate = null;
            }

            $order->update([
                'name'             => $request->name,
                'phone'            => $request->phone,
                'location'         => $request->location,
                'date_received'    => $request->date_received,
                'date_delivered'   => $request->date_delivered,
                'payment_status'   => $request->payment_status,
                'transaction_code' => $request->transaction_code,
                'payment_date'     => $paymentDate,
                'notes'            => $request->notes,
            ]);

            // Delete existing items and re-create from form
            $order->items()->delete();

            foreach ($request->items as $itemData) {
                $price    = (float) ($itemData['price'] ?? 0);
                $discount = (float) ($itemData['discount'] ?? 0);

                $order->items()->create([
                    'unique_id'        => $itemData['unique_id'] ?? null,
                    'price'            => $price,
                    'discount'         => $discount,
                    'item_total'       => max(0, $price - $discount),
                    'delivered'        => $itemData['delivered'] ?? 'Not Delivered',
                    'date_delivered'   => $itemData['date_delivered'] ?? null,
                    'size'             => $itemData['size'] ?? null,
                    'multiplier'       => $itemData['multiplier'] ?? null,
                    'quantity'         => $itemData['quantity'] ?? null,
                    'item_description' => $itemData['item_description'] ?? null,
                    'weight'           => $itemData['weight'] ?? null,
                ]);
            }

            $order->recalculateTotals();
        });

        return redirect()->route('orders.show', $id)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Soft delete an order.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted.');
    }

    /**
     * Mark a single item as delivered (AJAX).
     */
    public function deliverItem($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'delivered'      => 'Delivered',
            'date_delivered' => now()->toDateString(),
        ]);

        $order = $item->order;

        // If all items are now delivered, set order date_delivered
        if ($order->isFullyDelivered()) {
            $order->update(['date_delivered' => now()->toDateString()]);
        }

        return response()->json([
            'success'        => true,
            'fully_delivered'=> $order->fresh()->isFullyDelivered(),
            'delivered_count'=> $order->fresh()->deliveredCount(),
            'item_count'     => $order->itemCount(),
        ]);
    }

    /**
     * Autofill customer by phone (shared route, still served from CarpetController).
     * Proxied here just so OrderController references exist if needed.
     */
    public function getCustomerByPhone(Request $request)
    {
        $phone = $request->input('phone', '');

        $carpet  = \App\Models\Carpet::where('phone', $phone)->latest()->first();
        $laundry = \App\Models\Laundry::where('phone', $phone)->latest()->first();
        $order   = Order::where('phone', $phone)->latest()->first();

        $source = $order ?? $carpet ?? $laundry;

        if ($source) {
            return response()->json([
                'found'    => true,
                'name'     => $source->name,
                'location' => $source->location,
                'phone'    => $source->phone,
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Return the items from the most recent order for a given phone + type.
     * Used to pre-populate the New Order form.
     */
    public function getPreviousItems(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        $type  = $request->input('type', 'carpet');

        if (!$phone) {
            return response()->json(['found' => false]);
        }

        $order = Order::where('phone', $phone)
            ->where('type', $type)
            ->latest()
            ->with('items')
            ->first();

        if (!$order || $order->items->isEmpty()) {
            return response()->json(['found' => false]);
        }

        $items = $order->items->map(function ($item) use ($type) {
            if ($type === 'carpet') {
                return [
                    'unique_id'  => $item->unique_id,
                    'size'       => $item->size,
                    'multiplier' => $item->multiplier ?? 30,
                    'price'      => $item->price,
                    'discount'   => $item->discount,
                ];
            } else {
                return [
                    'unique_id'        => $item->unique_id,
                    'item_description' => $item->item_description,
                    'quantity'         => $item->quantity ?? 1,
                    'weight'           => $item->weight,
                    'price'            => $item->price,
                    'discount'         => $item->discount,
                ];
            }
        });

        return response()->json([
            'found'        => true,
            'order_number' => $order->order_number,
            'items'        => $items,
        ]);
    }
}
