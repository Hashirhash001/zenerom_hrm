<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create_modal');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_info' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Customer created successfully!',
            'customer' => $customer,
        ]);
    }

    public function show(Customer $customer)
    {
        // Eager load contacts
        $customer->load('contacts');
        return view('customers.view_modal', compact('customer'));
    }



    public function edit(Customer $customer)
    {
        return view('customers.edit_modal', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_info' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Customer updated successfully!',
            'customer' => $customer,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully!',
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            $customers = Customer::latest()->get();
        } else {
            $customers = Customer::where('name', 'LIKE', "%{$query}%")
                ->orWhere('contact_info', 'LIKE', "%{$query}%")
                ->latest()
                ->get();
        }
        $html = view('customers._list', compact('customers'))->render();
        return response()->json(['html' => $html]);
    }
}
