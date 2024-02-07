<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;

class AuditController extends Controller
{
    public function __invoke(Request $request, Document $document)
    {
        abort_if(! ($user = ($request->user() || auth()->user())), 404);

        if ($signerId = $request->get('signer_id')) {
            $auditLog = $document->getAuditsBySigner($signerId);
        } else {
            $auditLog = $document->getAuditsByDocument();
        }

        return $this->jsonResponse([
            'status' => 1,
            'data' => $auditLog,
        ])->notify(
            __('esign::label.success')
        );
    }
}
