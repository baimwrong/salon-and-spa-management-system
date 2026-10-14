<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CommissionPayout;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'items.*.name' => ['required', 'string'],
            'items.*.therapist_id' => ['nullable', 'exists:users,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,credit_card,debit_card,bank_transfer,loyalty_points,split'],
            'notes' => ['nullable', 'string'],
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        $discount = (float) ($validated['discount_amount'] ?? 0);
        $taxRate = (float) ($validated['tax_rate'] ?? 10.0);
        $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
        $tip = (float) ($validated['tip_amount'] ?? 0);
        $total = round(($subtotal - $discount) + $taxAmount + $tip, 2);
        $paid = (float) $validated['paid_amount'];
        $change = max(0, round($paid - $total, 2));

        $invoiceNumber = 'INV-' . Carbon::today()->format('Ymd') . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
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

            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $therapist = !empty($item['therapist_id']) ? User::find($item['therapist_id']) : null;
                $commissionRate = $therapist ? (float) $therapist->commission_rate : 0.00;
                $commissionAmount = round($itemSubtotal * ($commissionRate / 100), 2);

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['type'],
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'item_name' => $item['name'],
                    'therapist_id' => $therapist ? $therapist->id : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $itemSubtotal,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                ]);

                // Create commission payout entry for therapist
                if ($therapist && $commissionAmount > 0) {
                    CommissionPayout::create([
                        'therapist_id' => $therapist->id,
                        'invoice_item_id' => $invoiceItem->id,
                        'amount' => $commissionAmount,
                        'status' => 'pending',
                        'notes' => "Commission for {$item['name']} on invoice {$invoice->invoice_number}",
                    ]);
                }

                // If product, reduce stock and record movement
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
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
                }
            }

            // If attached to an appointment, complete it
            if (!empty($validated['appointment_id'])) {
                Appointment::where('id', $validated['appointment_id'])->update(['status' => 'completed']);
            }

            // Award loyalty points to customer (1 point per $10 spent)
            if (!empty($validated['customer_id'])) {
                $pointsAwarded = (int) floor($total / 10);
                if ($pointsAwarded > 0) {
                    Customer::where('id', $validated['customer_id'])->increment('loyalty_points', $pointsAwarded);
                }
            }

            DB::commit();

            return redirect()->route('pos.invoices.show', $invoice)
                ->with('success', "Payment processed! Invoice {$invoice->invoice_number} created.");
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
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'ilike', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
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
        $invoice->load(['customer', 'cashier', 'appointment', 'items.therapist']);
        return view('pos.show_invoice', compact('invoice'));
    }
}
