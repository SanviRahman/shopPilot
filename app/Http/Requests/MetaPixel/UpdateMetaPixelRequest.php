<?php

namespace App\Http\Requests\MetaPixel;

class UpdateMetaPixelRequest extends StoreMetaPixelRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('meta-pixels.update');
    }
}
