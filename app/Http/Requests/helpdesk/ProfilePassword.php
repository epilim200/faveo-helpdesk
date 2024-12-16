<?php

namespace App\Http\Requests\helpdesk;

use App\Http\Requests\Request;
use Illuminate\Validation\Rules\Password;

/**
 * ProfilePassword.
 *
 * @author  Ladybird <info@ladybirdweb.com>
 */
class ProfilePassword extends Request
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
            'old_password'     => 'required',
            // 'new_password'     => 'required|min:6',
            'new_password'     => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
            // 'confirm_password' => 'required|same:new_password',
            'confirm_password' => ['required', 'same:new_password', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }
}
