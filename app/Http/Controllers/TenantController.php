<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Customer;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255', 'unique:tenants,subdomain'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create Stripe customer
        Stripe::setApiKey(config('services.stripe.secret'));
        $customer = Customer::create([
            'email' => $data['email'],
            'name' => $data['name'],
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name' => $data['name'],
            'subdomain' => $data['subdomain'],
            'stripe_customer_id' => $customer->id,
        ]);

        // Create admin user for tenant
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully');
    }
}