<?php
namespace App\Http\Requests;
class CancelOrderItemRequest extends ApiRequest
{
 public function rules(): array { return ['detail_id'=>['required','integer','min:1'],'qty'=>['required','integer','min:1'],'reason'=>['required','string','max:500','regex:/\S/']]; }
}
