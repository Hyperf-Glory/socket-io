<?php

declare(strict_types = 1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class RegisterRequest extends FormRequest
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
            'mobile'    => 'required|max:12|mobile',
            'password' => 'required',
            'sms_code' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     */
    public function messages() : array
    {
        return [
            'mobile.required'    => '手机号必须填写!',
            'mobile.mobile'      => '无效的手机号码!',
            'password.required' => '密码不能为空!',
            'sms_code.required' => '验证码不能为空!',
        ];
    }
}
