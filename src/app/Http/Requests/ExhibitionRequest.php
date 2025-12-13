<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'item_image' => 'required|image|mimes:jpeg,png', 
            'categories' => 'required|array|min:1', 
            'condition_id' => 'required|exists:conditions,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'item_image.required' => '商品画像を選択してください。',
            'categories.required' => 'カテゴリーを選択してください。',
            'condition_id.required' => '商品の状態を選択してください。',
            'name.required' => '商品名を入力してください。',
            'description.required' => '商品説明を入力してください。',
            'price.required' => '販売価格を入力してください。',
        ];
    }
}