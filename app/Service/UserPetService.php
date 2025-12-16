<?php
namespace App\Service;

use App\Models\UserPet;

class UserPetService
{
    // 初始化寵物資訊
    public static function init($user)
    {
        $userPets = UserPet::where('uid', $user->uid)->get();
        if (! filled($userPets)) {
            for ($i = 0; $i < 6; $i++) {
                $pet_id = $i + 1;
                UserPet::create([
                    'uid'                    => $user->uid,
                    'pet_id'                 => $pet_id,
                    'pet_name'               => '',
                    'pet_str'                => 0,
                    'pet_def'                => 0,
                    'pet_sta'                => 0,
                    'pet_exp'                => 0,
                    'pet_level'              => 1,
                    'pet_unallocated_points' => 0,
                    'pet_skin_id'            => 0,
                ]);
            }
        }

        return $userPets;
    }

    // 取得寵物資訊
    public static function getPets($uid)
    {
        $userPets = UserPet::where('uid', $uid)->get();
        if (empty($userPets)) {
            $this->init($uid);
            $userPets = UserPet::where('uid', $uid)->get();
        }
        return $userPets;
    }

    // 更新寵物資訊
    public static function updatePet($user, $data)
    {
        $pet = UserPet::where('uid', $user->uid)->where('pet_id', $data['pet_id'])->first();
        if (empty($pet)) {
            return null;
        }
        // str+def+sta 不應該 > pet_unallocated_points
        // if ($data['pet_str'] + $data['pet_def'] + $data['pet_sta'] > $data['pet_unallocated_points']) {
        //     return null;
        // }

        $pet->update([
            'pet_name'               => $data['pet_name'] ?? '',
            'pet_str'                => $data['pet_str'],
            'pet_def'                => $data['pet_def'],
            'pet_sta'                => $data['pet_sta'],
            'pet_exp'                => $data['pet_exp'],
            'pet_level'              => $data['pet_level'],
            'pet_unallocated_points' => $data['pet_unallocated_points'],
        ]);
        return $pet;
    }

    // 單純更新寵物名稱
    public static function updatePetName($user, $petName, $pet_id)
    {
        $pet = UserPet::where('uid', $user->uid)->where('pet_id', $pet_id)->first();
        if (empty($pet)) {
            return null;
        }

        $pet->update([
            'pet_name' => $petName,
        ]);
        return $pet;
    }
}
