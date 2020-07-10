<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'profile' => ProfileResource::collection(Profile::all()->keyBy->user_id),
            'roles' => $this->roles,
            'permissions' =>$this->getAllPermissions(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // return parent::toArray($request);
    }
}
