<?php

namespace App\Http\Requests;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class AttendanceEventRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('photo')) {
                return;
            }

            $photo = $this->file('photo');
            if (! $photo instanceof UploadedFile || ! $photo->isValid()) {
                return;
            }

            $path = $photo->getRealPath();
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            $image = @getimagesize($path);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];

            if (! $image || ! in_array($mime, $allowed, true) || ($image['mime'] ?? null) !== $mime) {
                $validator->errors()->add('photo', 'File harus berisi gambar JPEG, PNG, atau WebP yang valid.');
            }
        });
    }
}
