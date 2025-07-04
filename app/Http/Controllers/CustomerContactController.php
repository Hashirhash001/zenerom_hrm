<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Http\Request;

class CustomerContactController extends Controller
{
    /**
     * Store a newly created contact for the customer.
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'contact_name'  => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        $validated['customer_id'] = $customer->id;
        $contact = CustomerContact::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact added successfully!',
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(CustomerContact $customerContact)
    {
        return view('customers.contact_edit_modal', compact('customerContact'));
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, CustomerContact $customerContact)
    {
        $validated = $request->validate([
            'contact_name'  => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        $customerContact->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully!',
            'contact' => $customerContact,
        ]);
    }

    /**
     * Remove the specified contact.
     */
   public function destroy(CustomerContact $customerContact)
    {
        $customerContact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully!'
        ]);
    }




}
