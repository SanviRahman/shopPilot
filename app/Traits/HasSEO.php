<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSEO
{
    public function getMetaTitle(): string
    {
        return $this->meta_title ?: ($this->name ?? config('app.name'));
    }

    public function getMetaDescription(): string
    {
        if (! empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (! empty($this->description)) {
            return Str::limit(strip_tags($this->description), 160);
        }

        return config('app.name') . ' category details.';
    }

    public function getOgTitle(): string
    {
        return $this->og_title ?: $this->getMetaTitle();
    }

    public function getOgDescription(): string
    {
        return $this->og_description ?: $this->getMetaDescription();
    }

    public function getOgImageUrl(): ?string
    {
        if (method_exists($this, 'getFirstMediaUrl')) {
            return $this->getFirstMediaUrl('category_og_image') 
                ?: $this->getFirstMediaUrl('category_image') 
                ?: null;
        }

        return null;
    }

    public function getTwitterTitle(): string
    {
        return $this->twitter_title ?: $this->getOgTitle();
    }

    public function getTwitterDescription(): string
    {
        return $this->twitter_description ?: $this->getOgDescription();
    }

    public function getTwitterImageUrl(): ?string
    {
        if (method_exists($this, 'getFirstMediaUrl')) {
            return $this->getFirstMediaUrl('category_twitter_image') 
                ?: $this->getOgImageUrl();
        }

        return null;
    }
}