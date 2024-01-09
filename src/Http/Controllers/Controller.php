<?php

namespace NIIT\ESign\Http\Controllers;

use App\Actions\FilepondAction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Base;
use Illuminate\Support\Str;
use NIIT\ESign\Concerns\Auditable;
use NIIT\ESign\Models\Document;

class Controller extends Base
{
    use Auditable, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function remove(Request $request)
    {
        $data = $request->validate([
            'type' => 'required',
            'id' => 'required|exists:e_documents,id',
        ]);

        $type = $data['type'];

        abort_if(! method_exists(Document::class, ($method = Str::camel($type))), 400);

        $isDeleted = optional(
            optional(
                Document::with($method)->find($data['id'])
            )->{$method}
        )->delete();

        $isRemoved = ! blank($isDeleted) || is_null($isDeleted) ? true : false;

        return $this->jsonResponse([
            'status' => $isRemoved ? 0 : 1,
        ]);
    }

    public function upload(Request $request, $type)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:pdf', //'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $id = $data['id'];
        $file = $request->file('file');

        abort_if(! method_exists(Document::class, ($method = Str::camel($type))), 400);

        $filePath = null;
        $fileName = date('YmdHms').'_'.trim($file->getClientOriginalName());

        if ($filePath = $file->storeAs(
            esignUploadPath($type, ['id' => $id]),
            $fileName,
            FilepondAction::getDisk(true)
        )) {
            $surveyModel = Document::with($method)->find($id);

            $surveyModel->{$method}()->updateOrCreate([
                'model_type' => Document::class,
                'model_id' => $id,
                'type' => $type,
            ], [
                'file_path' => $filePath,
                'status' => 1,
            ]);
        }

        return $this->jsonResponse([
            $type => FilepondAction::loadFile($filePath, 'view'),
        ]);
    }

    protected function user(?Request $request = null): User|Authenticatable|null
    {
        return ($request ?? auth())->user();
    }
}
