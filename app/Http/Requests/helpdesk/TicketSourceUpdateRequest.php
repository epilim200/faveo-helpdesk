<?php

namespace App\Http\Requests\helpdesk;

use App\Http\Requests\Request;

class TicketSourceUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'value' => 'required|max:255',
            'css_class' => 'required|max:255',
            'status' => 'required|integer',
        ];
    }
}
