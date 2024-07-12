<?php

namespace App\Services;

use App\Models\ItemSetting;
use App\Models\Damage;
use App\Models\ItemPattern;
use App\Exceptions\NotFoundException;
use App\Services\Helpers\FileUploadService;
use Illuminate\Http\UploadedFile;
use Milon\Barcode\DNS1D;

class ItemService
{
    private $barcode;
    private $fileUploadService;

    public function __construct(DNS1D $barcode, FileUploadService $fileUploadService)
    {
        $this->barcode = $barcode;
        $this->fileUploadService = $fileUploadService;
    }

    public function addItemSetting($data)
    {
        if (isset($data['patterns'])) {
            $data['patterns'] = strtolower($data['patterns']);
        }

        if (isset($data['stains'])) {
            $data['stains'] = strtolower($data['stains']);
        }

        if (isset($data['materials'])) {
            $data['materials'] = strtolower($data['materials']);
        }

        if (isset($data['styles'])) {
            $data['styles'] = strtolower($data['styles']);
        }

        if (isset($data['color'])) {
            $data['color'] = strtolower($data['color']);
        }

        if (isset($data['brand'])) {
            $data['brand'] = strtolower($data['brand']);
        }

        return ItemSetting::updateOrCreate(
            ['id' => 1],
            $data
        );
    }

    public function getItemSetting()
    {
        return ItemSetting::all();
    }

    public function addColorBrand($data)
    {
        if (isset($data['color'])) {
            $data['color'] = strtolower($data['color']);
        }

        if (isset($data['brand'])) {
            $data['brand'] = strtolower($data['brand']);
        }

        $itemSettings = ItemSetting::find(1);
        $itemSettings->color = $data["color"] . ',' . $itemSettings['color'] ?? $data["color"];
        $itemSettings->brand = $data["brand"] . ',' . $itemSettings["brand"] ?? $data["brand"];
        $itemSettings->save();

        return $itemSettings;
    }

    public function createDamage($data)
    {
        return Damage::create($data);
    }

    public function getDamages()
    {
        return Damage::all();
    }

    public function editDamage($data, $uuid)
    {
        $damage = Damage::where('uuid', $uuid)->first();
        if (!$damage) {
            throw new NotFoundException("Damage not found");
        }

        $damage->update($data);

        return $damage;
    }

    public function deleteDamage($uuid)
    {
        $damage = Damage::where('uuid', $uuid)->first();
        if (!$damage) {
            throw new NotFoundException("Damage not found");
        }

        $damage->delete();
    }

    public function createPattern($data)
    {
        if (isset($data['image'])) {
            $data['image'] = $this->fileUploadService->upload($data['image'], 'images');
        }

        return ItemPattern::create($data);
    }

    public function getPattern()
    {
        return ItemPattern::all();
    }

    public function editPattern($data, $uuid)
    {
        $pattern = ItemPattern::where('uuid', $uuid)->first();
        if (!$pattern) {
            throw new NotFoundException("Pattern not found");
        }

        if (isset($data['image'])) {
            $data['image'] = ($data['image'] instanceof UploadedFile) ? $this->fileUploadService->upload($data['image'], 'images') : $pattern->image;
        }

        $pattern->update($data);

        return $pattern;
    }

    public function deletePattern($uuid)
    {
        $pattern = ItemPattern::where('uuid', $uuid)->first();
        if (!$pattern) {
            throw new NotFoundException("Pattern not found");
        }

        $pattern->delete();
    }
}
