<?php

declare(strict_types = 1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class SmsCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules() : array
    {
        return [
            'mobile' => 'required|max:12|mobile',
            'type'   => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     */
    public function messages() : array
    {
        return [
            'mobile.required' => '手机号必须填写!',
            'mobile.mobile'   => '无效的手机号码!',
            'type.required'   => '类型不能为空!',
        ];
    }
}
