<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->serializeDatesForUser(
            $request,
            parent::toArray($request),
        );
    }
}
