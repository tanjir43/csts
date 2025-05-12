<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'       => 'required|string|max:255',
            'description'   => 'required|string',
            'status'        => 'required|in:open,in_progress,resolved,closed',
            'priority'      => 'required|in:low,medium,high',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'category'      => 'required|in:technical,billing,general',
        ];
    }
}
