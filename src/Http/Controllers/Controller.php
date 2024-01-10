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

        tap(
            optional(
                optional(
                    Document::with($method)->find($data['id'])
                )->{$method}
            )->update(['is_current' => false])
        )->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:e_documents,id',
            'type' => 'required|string',
            'document' => 'required|file|mimes:pdf', //'required|image|mimes:jpg,png,jpeg|max:2048',
        ], [
            'id.required' => __('esign::validations.required_document_id'),
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse(['errors' => $validator->errors()]);
        }

        $data = $validator->validated();

        $id = $data['id'];
        $type = $data['type'];
        $file = $request->file('document');

        abort_if(! method_exists(Document::class, ($method = Str::camel($type))), 400);

        $filePath = null;
        $fileName = date('YmdHms').'_'.trim($originalFileName = $file->getClientOriginalName());

        if ($filePath = $file->storeAs(
            esignUploadPath($type, ['id' => $id]),
            $fileName,
            ($disk = FilepondAction::getDisk(true))
        )) {
            $surveyModel = Document::with($method)->find($id);

            $surveyModel->{$method}()->updateOrCreate([
                'model_type' => Document::class,
                'model_id' => $id,
                'type' => $type,
            ], [
                'path' => $filePath,
                'file_name' => $originalFileName,
                'disk' => $disk,
                'extension' => $file->getClientOriginalExtension(),
                'is_current' => 1,
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
