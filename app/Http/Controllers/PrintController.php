<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Display a printer-friendly version of the specified requisition.
     */
    public function printRequisition(Requisition $requisition)
    {
        // Optional but recommended: Authorize the request.
        // This ensures only the original requester or an admin can print the form.
        if (!auth()->user()->is_admin && auth()->id() !== $requisition->user_id) {
            abort(403, 'You are not authorized to view this document.');
        }

        // Return the 'print.requisition' view and pass the $requisition model to it.
        // We are aliasing it as 'record' to match the variable name in the Blade file.
        return view('print.requisition', ['record' => $requisition]);
    }
}