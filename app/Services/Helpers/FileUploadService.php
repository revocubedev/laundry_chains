<?php

namespace App\Services\Helpers;

use App\Exceptions\BadRequestException;
use Illuminate\Support\Facades\Date;
use Ramsey\Uuid\Uuid;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FileUploadService
{

    // protected function uploadToCloudinary($file, $path)
    // {
    //     $fileOriginalName = explode('.', $file->getClientOriginalName());
    //     $fileName = Date::now()->format('YmdHis') . '-' . Uuid::uuid4()->toString() . '-' . trim(preg_replace('/\s+/', '-', $fileOriginalName[0]));

    //     return Cloudinary::upload($file->getRealPath(), [
    //         'folder' => $path,
    //         'public_id' => $fileName,
    //         'resource_type' => 'auto',
    //     ])->getSecurePath();
    // }

    protected function uploadToLocal($file, $path)
    {
        $fileName = Date::now()->format('YmdHis') . '-' . Uuid::uuid4()->toString() . '-' . trim(preg_replace('/\s+/', '-', $file->getClientOriginalName()));
        $filePath = $file->storeAs($path, $fileName);

        return $filePath;
    }

    public function upload($file, $path)
    {
        if (!$file->isValid()) {
            throw new BadRequestException('invalid file');
        }

        // if (env('APP_ENV') !== 'local') {
        //     return $this->uploadToCloudinary($file, $path);
        // }

        return $this->uploadToLocal($file, $path);
    }
}
