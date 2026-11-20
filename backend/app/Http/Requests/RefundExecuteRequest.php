<?php
namespace App\Http\Requests;
class RefundExecuteRequest extends ApiRequest
{
 public function rules(): array { return ['method'=>['required','in:cash,bank_transfer'],'confirm_cash'=>['exclude_unless:method,cash','required','accepted'],'reference_number'=>['required_if:method,bank_transfer','nullable','string','max:120'],'proof'=>['required_if:method,bank_transfer','nullable','file','mimes:jpg,jpeg,png,pdf','max:5120']]; }
}
