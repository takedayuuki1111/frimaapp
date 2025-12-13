<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'avatar_image' => 'nullable|image|mimes:jpeg,png', // 画像は任意（変更しない場合もあるため）
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:8', // ハイフン考慮
            'address' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'address.required' => '住所を入力してください。',
        ];
    }
}