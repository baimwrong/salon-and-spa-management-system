<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CommissionPayout;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display POS Checkout Terminal.
     */
    public function terminal(Request $request)
    {
        $customers = Customer::where('status', '!=', 'blocked')->orderBy('name')->get();
        $services = Service::where('is_active', true)->with('category')->orderBy('name')->get();
        $products = Product::where('is_active', true)->whereIn('type', ['retail', 'both'])->where('stock_quantity', '>', 0)->orderBy('name')->get();
        $therapists = User::where('role', 'therapist')->where('status', 'active')->orderBy('name')->get();

        $selectedAppointment = null;
        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::with(['customer', 'therapist', 'items.service'])->find($request->input('appointment_id'));
        }

        return view('pos.terminal', compact('customers', 'services', 'products', 'therapists', 'selectedAppointment'));
    }

    /**
     * Process POS Checkout / Invoice Creation.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:service,product'],
            'items.*.id' => ['required', 'integer'],
            'items.*.therapist_id' => ['nullable', 'exists:users,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,credit_card,debit_card,bank_transfer,loyalty_points,split'],
            'split_payments' => ['nullable', 'array'],
            'split_payments.*.method' => ['required_with:split_payments', 'string', 'in:cash,credit_card,debit_card,bank_transfer,loyalty_points'],
            'split_payments.*.amount' => ['required_with:split_payments', 'numeric', 'min:0.01'],
            'split_payments.*.reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // Authoritative server-side price resolution & inventory verification
            $resolvedItems = [];
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'service') {
                    $service = Service::with('consumables.product')->findOrFail($item['id']);
                    $unitPrice = (float) $service->price;
                    $itemName = $service->name;
                    $productModel = null;
                    $serviceModel = $service;
                } else {
                    $product = Product::where('id', $item['id'])->lockForUpdate()->firstOrFail();
                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \RuntimeException("Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, requested: {$item['quantity']}.");
                    }
                    $unitPrice = (float) $product->retail_price;
                    $itemName = $product->name;
                    $productModel = $product;
                    $serviceModel = null;
                }

                $itemSubtotal = round($unitPrice * $item['quantity'], 2);
                $subtotal += $itemSubtotal;

                $resolvedItems[] = [
                    'type' => $item['type'],
                    'id' => $item['id'],
                    'name' => $itemName,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'therapist_id' => $item['therapist_id'] ?? null,
                    'product_model' => $productModel,
                    'service_model' => $serviceModel,
                ];
            }

            $discount = (float) ($validated['discount_amount'] ?? 0);
            $discount = min($discount, $subtotal); // Discount cannot exceed subtotal

            $taxRate = (float) ($validated['tax_rate'] ?? 10.0);
            $taxableAmount = max(0, $subtotal - $discount);
            $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
            $tip = (float) ($validated['tip_amount'] ?? 0);
            $total = round($taxableAmount + $taxAmount + $tip, 2);
            $paid = (float) $validated['paid_amount'];
            $change = max(0, round($paid - $total, 2));

            $customer = !empty($validated['customer_id']) ? Customer::lockForUpdate()->find($validated['customer_id']) : null;

            // Handle loyalty points payment
            if ($validated['payment_method'] === 'loyalty_points') {
                if (!$customer) {
                    throw new \RuntimeException("A registered customer profile is required to pay with Loyalty Points.");
                }
                // Exchange policy: 10 points = $1.00 (1 point = $0.10)
                $pointsNeeded = (int) ceil($total * 10);
                if ($customer->loyalty_points < $pointsNeeded) {
                    throw new \RuntimeException("Insufficient loyalty points. This total requires {$pointsNeeded} points, but client only has {$customer->loyalty_points} points.");
                }
                $customer->decrement('loyalty_points', $pointsNeeded);
            }

            // Sequential & Compliant Invoice Number Generation
            $todayStr = Carbon::today()->format('Ymd');
            $prefix = "INV-{$todayStr}-";
            $lastInvoice = Invoice::where('invoice_number', 'LIKE', "{$prefix}%")
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($lastInvoice && preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches)) {
                $nextSeq = intval($matches[1]) + 1;
            } else {
                $nextSeq = 1;
            }
            $invoiceNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'customer_id' => $customer?->id,
                'cashier_id' => Auth::id(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'tip_amount' => $tip,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'change_amount' => $change,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paid >= $total ? 'paid' : 'partial',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Record Itemized Payments into payments table
            if ($validated['payment_method'] === 'split' && !empty($validated['split_payments'])) {
                $splitTotal = 0;
                foreach ($validated['split_payments'] as $split) {
                    $splitAmount = (float) $split['amount'];
                    $splitTotal += $splitAmount;

                    if ($split['method'] === 'loyalty_points') {
                        if (!$customer) {
                            throw new \RuntimeException("Registered customer profile required to use Loyalty Points in split payment.");
                        }
                        $pointsNeeded = (int) ceil($splitAmount * 10);
                        if ($customer->loyalty_points < $pointsNeeded) {
                            throw new \RuntimeException("Insufficient loyalty points for split amount \${$splitAmount}. Requires {$pointsNeeded} points.");
                        }
                        $customer->decrement('loyalty_points', $pointsNeeded);
                    }

                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'payment_method' => $split['method'],
                        'amount' => $splitAmount,
                        'tendered_amount' => $splitAmount,
                        'change_amount' => 0.00,
                        'reference_number' => $split['reference'] ?? null,
                        'notes' => 'Split payment item',
                    ]);
                }
            } else {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method' => $validated['payment_method'],
                    'amount' => min($paid, $total),
                    'tendered_amount' => $paid,
                    'change_amount' => $change,
                    'reference_number' => null,
                    'notes' => 'Standard payment checkout',
                ]);
            }

            foreach ($resolvedItems as $item) {
                $therapist = !empty($item['therapist_id']) ? User::find($item['therapist_id']) : null;
                $commissionRate = $therapist ? (float) $therapist->commission_rate : 0.00;
                $commissionAmount = round($item['subtotal'] * ($commissionRate / 100), 2);

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['type'],
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'item_name' => $item['name'],
                    'therapist_id' => $therapist ? $therapist->id : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                ]);

                // Create commission payout entry for therapist only if invoice is paid or partial with proportional threshold
                if ($therapist && $commissionAmount > 0 && $invoice->payment_status === 'paid') {
                    CommissionPayout::create([
                        'therapist_id' => $therapist->id,
                        'invoice_item_id' => $invoiceItem->id,
                        'amount' => $commissionAmount,
                        'status' => 'pending',
                        'notes' => "Commission for {$item['name']} on invoice {$invoice->invoice_number}",
                    ]);
                }

                // If product, decrement stock and record stock movement
                if ($item['type'] === 'product' && $item['product_model']) {
                    $product = $item['product_model'];
                    $product->decrement('stock_quantity', $item['quantity']);
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'unit_cost' => $product->cost_price,
                        'reference_number' => $invoice->invoice_number,
                        'notes' => "POS Sale checkout",
                    ]);
                }

                // If service has backbar consumables, auto-deduct them
                if ($item['type'] === 'service' && $item['service_model']) {
                    $service = $item['service_model'];
                    if ($service->consumables->isNotEmpty()) {
                        foreach ($service->consumables as $consumable) {
                            $prod = Product::where('id', $consumable->product_id)->lockForUpdate()->first();
                            if ($prod) {
                                $usedQty = $consumable->quantity_used * $item['quantity'];
                                $prod->decrement('stock_quantity', $usedQty);
                                StockMovement::create([
                                    'product_id' => $prod->id,
                                    'user_id' => Auth::id(),
                                    'type' => 'service_usage',
                                    'quantity' => -$usedQty,
                                    'unit_cost' => $prod->cost_price,
                                    'reference_number' => $invoice->invoice_number,
                                    'notes' => "Auto backbar usage for {$service->name} on invoice {$invoice->invoice_number}",
                                ]);
                            }
                        }
                    }
                }
            }

            // If attached to an appointment, complete it
            if (!empty($validated['appointment_id'])) {
                Appointment::where('id', $validated['appointment_id'])->update(['status' => 'completed']);
            }

            // Award loyalty points to customer (1 point per $10 spent) only if not paying with points
            if ($customer && $validated['payment_method'] !== 'loyalty_points') {
                $pointsAwarded = (int) floor($total / 10);
                if ($pointsAwarded > 0) {
                    $customer->increment('loyalty_points', $pointsAwarded);
                }
            }

            DB::commit();

            return redirect()->route('pos.invoices.show', $invoice)
                ->with('success', "Payment processed! Sequential Invoice {$invoice->invoice_number} created.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    /**
     * Invoices History List.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with(['customer', 'cashier']);

        if ($request->filled('search')) {
            $search = strtolower(trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(invoice_number) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('pos.invoices', compact('invoices'));
    }

    /**
     * View Invoice & Printable Receipt.
     */
    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'cashier', 'appointment', 'items.therapist', 'payments']);
        return view('pos.show_invoice', compact('invoice'));
    }

    /**
     * Process Refund & Stock / Commission Reversal.
     */
    public function refund(Request $request, Invoice $invoice)
    {
        $request->validate([
            'refund_reason' => ['required', 'string', 'max:255'],
        ]);

        if (!in_array($invoice->payment_status, ['paid', 'partial'])) {
            return back()->with('error', 'Only paid or partial invoices can be refunded.');
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'payment_status' => 'refunded',
                'notes' => trim(($invoice->notes ?? '') . "\n[REFUNDED by " . Auth::user()->name . ']: ' . $request->input('refund_reason')),
            ]);

            // 1. Restock retail products
            foreach ($invoice->items as $item) {
                if ($item->item_type === 'product' && $item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'type' => 'adjustment',
                            'quantity' => $item->quantity,
                            'unit_cost' => $product->cost_price,
                            'reference_number' => $invoice->invoice_number,
                            'notes' => 'Restocked due to refund on invoice: ' . $invoice->invoice_number,
                        ]);
                    }
                }
            }

            // 2. Clawback / cancel therapist commissions
            $payouts = CommissionPayout::whereHas('invoiceItem', fn($q) => $q->where('invoice_id', $invoice->id))->get();
            foreach ($payouts as $payout) {
                if ($payout->status === 'pending') {
                    $payout->update(['status' => 'cancelled', 'notes' => 'Cancelled due to invoice refund: ' . $invoice->invoice_number]);
                } elseif ($payout->status === 'paid') {
                    CommissionPayout::create([
                        'therapist_id' => $payout->therapist_id,
                        'invoice_item_id' => $payout->invoice_item_id,
                        'amount' => -$payout->amount,
                        'status' => 'pending',
                        'notes' => 'Commission clawback for refunded invoice: ' . $invoice->invoice_number,
                    ]);
                }
            }

            // 3. Reverse customer loyalty points if awarded
            if ($invoice->customer_id) {
                $customer = Customer::lockForUpdate()->find($invoice->customer_id);
                if ($customer && $invoice->payment_method !== 'loyalty_points') {
                    $pointsAwarded = (int) floor($invoice->total_amount / 10);
                    if ($pointsAwarded > 0) {
                        $customer->decrement('loyalty_points', min($customer->loyalty_points, $pointsAwarded));
                    }
                }
                // If paid by loyalty points, refund them
                if ($customer && $invoice->payment_method === 'loyalty_points') {
                    $pointsSpent = (int) ceil($invoice->total_amount * 10);
                    $customer->increment('loyalty_points', $pointsSpent);
                }
            }

            DB::commit();
            return back()->with('success', "Invoice {$invoice->invoice_number} has been refunded, stock restored, and commissions adjusted.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
