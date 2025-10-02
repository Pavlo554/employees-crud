<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'salary' => (float) $this->salary,
            'hiredAt' => $this->hired_at ? $this->hired_at->format('Y-m-d') : null,
            'status' => $this->status,
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}