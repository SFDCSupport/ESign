<?php

namespace NIIT\ESign\Http\Controllers;

use App\Actions\FilepondAction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as Base;
use Illuminate\Support\Facades\Validator;
use NIIT\ESign\Concerns\Auditable;
use NIIT\ESign\Models\Document;

class Controller extends Base
{
    use Auditable, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function remove(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:e_documents,id',
        ]);

        $isDeleted = optional(
            optional(
                Document::with($method)->find($data['id'])
            )->{$method}
        )->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function upload(Request $request, $type)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:e_documents,id',
            'document' => 'required|file|mimes:pdf', //'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->failed()) {
            return $this->jsonResponse(['errors' => $validator->errors()]);
        }

        $data = $validator->validated();

        $id = $data['id'];
        $file = $request->file('document');

        $filePath = null;
        $fileName = date('YmdHms').'_'.trim($originalName = $file->getClientOriginalName());
        $documentModel = Document::find($id);

        if ($documentModel->exists() && ($filePath = $file->storeAs(
            esignUploadPath($type, ['id' => $id]),
            $fileName,
            ($disk = FilepondAction::getDisk(true))
        ))) {
            (Document::find($id))->update([
                'file_name' => $originalName,
                'disk' => $disk,
                'path' => $filePath,
                'extension' => $file->getClientOriginalExtension(),
            ]);
        }

        return response(FilepondAction::loadFile($filePath, 'view'));
    }

    protected function jsonResponse($data, $status = 200, array $headers = [], $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    {
        return response()->json($data, $status, $headers, $options);
    }

    protected function user(?Request $request = null): User|Authenticatable|null
    {
        return ($request ?? auth())->user();
    }
}
