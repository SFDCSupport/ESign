<?php

namespace NIIT\ESign\Http\Controllers;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Events\ReadStatusChanged;
use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Events\SigningStatusChanged;
use NIIT\ESign\Http\Requests\SigningRequest;
use NIIT\ESign\Http\Resources\SignerResource;
use NIIT\ESign\Models\Asset;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningController extends Controller
{
    public function index(Signer $signer)
    {
        $document = $signer->loadMissing('document.document', 'elements')->document;

        SigningProcessStarted::dispatch(
            $document,
            $signer
        );

        $formattedData = [
            'signers' => SignerResource::collection([$signer]),
        ];

        return $this->view('esign::signing.index', compact(
            'signer',
            'document',
            'formattedData',
        ));
    }

    public function store(SigningRequest $request, Signer $signer)
    {
        $storage = FilepondAction::getDisk();
        $disk = FilepondAction::getDisk(true);
        $loadedSigner = $signer->loadMissing('document.document');
        $signerUploadPath = $signer->getUploadPath();

        $data = collect($request->validated()['element'])->map(function ($d) use ($loadedSigner, $disk, $signerUploadPath) {
            $isSignaturePad = $d['type'] === 'signature_pad';

            $data = $d['data'];

            if ($isSignaturePad) {
                /** @var UploadedFile $file */
                $file = $d['data'];
                $fileName = $d['id'].'_'.trim($file->getClientOriginalName());

                $filePath = $file->storeAs(
                    $signerUploadPath,
                    $fileName.'.png',
                    $disk
                );

                $loadedSigner->elements()->where('id', $d['id'])->first()->update([
                    'data' => json_encode([
                        'file_path' => $signerUploadPath,
                        'file_name' => $fileName,
                        'disk' => $disk,
                        'saved_at' => now(),
                    ]),
                ]);

                $data = FilepondAction::loadFile($filePath, 'view');
            }

            return collect([
                'signer_element_id' => $d['id'],
                'pageIndex' => $d['page_index'],
                'pageWidth' => $d['page_width'],
                'pageHeight' => $d['page_height'],
                'top' => $d['top'],
                'left' => $d['left'],
                'width' => $d['width'],
                'height' => $d['height'],
            ])->when($isSignaturePad, fn ($c) => $c->merge([
                'data' => $data,
                'type' => 'image',
            ]), fn ($c) => $c->merge([
                'type' => 'text',
                'data' => $d['data'],
                'size' => $d['size'] ?? 16,
                'color' => $d['color'] ?? '0',
            ]));
        });

        /** @var UploadedFile $signedDocument */
        $signedDocument = $request->validated()['signed_document'];

        /** @var Asset $postSubmitAsset */
        $postSubmitAsset = $signer->createSnapshot(
            /** @var Asset $asset */
            ($asset = $loadedSigner->document->document),
            $signedDocument
        );

        $storage->copy(($loadFile = $postSubmitAsset->path.'/'.$postSubmitAsset->file_name), $asset->path.'/'.$asset->file_name);
        $asset->touch('updated_at');

        $downloadUrl = FilepondAction::loadFile($loadFile, 'view');
        $createOrUpdateSubmissions = static function ($data, $elementId) use ($loadedSigner) {
            return $loadedSigner->elements()->updateOrCreate([
                'id' => $elementId,
                'signer_id' => $loadedSigner->id,
                'document_id' => $loadedSigner->document->id,
            ], [
                'data' => $data,
                'submitted_at' => now(),
            ]);
        };

        $data->pluck('data', 'signer_element_id')->each(fn ($elementId, $data) => $createOrUpdateSubmissions($elementId, $data));

        SigningStatusChanged::dispatch(
            $loadedSigner->document,
            $signer,
            SigningStatus::SIGNED
        );

        return $this->jsonResponse([
            'status' => 1,
            'downloadUrl' => $downloadUrl,
            'redirectUrl' => $signer->signingUrl().'/show',
        ])->notify(__('esign::label.signing_success_message'));
    }

    public function show(Request $request, Signer $signer)
    {
        /** @var Document $document */
        $document = $signer->loadMissing('postSubmitSnapshot', 'document', 'elements')->document;
        $signedDocument = $signer->getSignedDocumentPath();

        /** @var Filesystem $disk */
        $disk = Storage::disk($document->document->disk);

        abort_if(
            ! $disk->exists($signedDocument) ||
            $signer->signingStatusIsNot(SigningStatus::SIGNED),
            404
        );

        $signedDocumentUrl = $signer->getSignedDocumentUrl();
        $formattedData = [];

        return $this->view('esign::signing.show', compact(
            'signedDocument',
            'signedDocumentUrl',
            'signer',
            'document',
            'formattedData',
        ));
    }

    public function sendCopy(Signer $signer)
    {
        $mailResponse = $signer->sendCopy();

        return $this->jsonResponse([
            'status' => (int) $mailResponse,
        ]);
    }

    public function mailTrackingPixel(Signer $signer)
    {
        $pixel = sprintf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c', 71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33, 249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59);

        $response = response($pixel, 200)
            ->header('Content-type', 'image/gif')
            ->header('Content-Length', 42)
            ->header('Cache-Control', 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate')
            ->header('Expires', now()->addMinutes(5)->format('D, d M Y H:i:s e'))
            ->header('Last-Modified', now()->subMinutes(5)->format('D, d M Y H:i:s e'))
            ->header('Pragma', 'no-cache');

        if ($signer->readStatusIsNot(ReadStatus::OPENED)) {
            ReadStatusChanged::dispatch($signer, ReadStatus::OPENED);
        }

        return $response;
    }
}
