<?php

namespace App\Http\Resources\Company\Employee;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Company\Team\Team as TeamResource;

class Employee extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'object' => 'employee',
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'permission_level' => $this->permission_level,
            'avatar' => $this->avatar,
            'position' => $this->position,
            'teams' => is_null($this->teams) ? null : TeamResource::collection($this->teams),
            'company' => [
                'id' => $this->company_id,
            ],
            'user' => [
                'id' => $this->user_id,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
