<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Financial Metrics
        $todayRevenue = Invoice::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $monthRevenue = Invoice::where('payment_status', 'paid')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // Appointments Metrics
        $todayAppointmentsCount = Appointment::whereDate('appointment_date', $today)->count();
        $todayCompletedCount = Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $todayInServiceCount = Appointment::whereDate('appointment_date', $today)->where('status', 'in_service')->count();
        $todayConfirmedCount = Appointment::whereDate('appointment_date', $today)->where('status', 'confirmed')->count();

        // Customer Metrics
        $totalCustomers = Customer::where('status', '!=', 'blocked')->count();
        $newCustomersThisMonth = Customer::where('created_at', '>=', $startOfMonth)->count();

        // Low Stock Products
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // Today's Appointments List
        $todayAppointments = Appointment::with(['customer', 'therapist', 'room', 'items.service'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->take(8)
            ->get();

        // 7-Day Revenue Trend for Chart.js (Single grouped query)
        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $dailyRevenues = Invoice::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as invoice_date, SUM(total_amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'invoice_date');

        $chartLabels = [];
        $chartRevenues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D, M d');
            $dateKey = $date->toDateString();
            $chartRevenues[] = (float) ($dailyRevenues[$dateKey] ?? 0);
        }

        // Top Performing Therapists (Single database query with eager aggregates)
        $topTherapists = User::where('role', 'therapist')
            ->withCount(['therapistAppointments as completed_appointments_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->withSum('invoiceItems as earned_commissions', 'commission_amount')
            ->orderByDesc('earned_commissions')
            ->take(4)
            ->get();

        // Therapist-specific view data if logged in as therapist
        $myTodaySchedule = null;
        $myCommissionEarned = 0.00;
        $myPendingCommission = 0.00;
        if ($user->isTherapist()) {
            $myTodaySchedule = Appointment::with(['customer', 'room', 'items.service'])
                ->where('therapist_id', $user->id)
                ->whereDate('appointment_date', $today)
                ->orderBy('start_time', 'asc')
                ->get();
            $myCommissionEarned = (float) $user->commissionPayouts()->where('status', 'paid')->sum('amount');
            $myPendingCommission = (float) $user->commissionPayouts()->where('status', 'pending')->sum('amount');
        }

        return view('dashboard.index', compact(
            'todayRevenue',
            'monthRevenue',
            'todayAppointmentsCount',
            'todayCompletedCount',
            'todayInServiceCount',
            'todayConfirmedCount',
            'totalCustomers',
            'newCustomersThisMonth',
            'lowStockProducts',
            'lowStockCount',
            'todayAppointments',
            'chartLabels',
            'chartRevenues',
            'topTherapists',
            'myTodaySchedule',
            'myCommissionEarned',
            'myPendingCommission'
        ));
    }
}
