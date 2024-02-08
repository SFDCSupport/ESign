<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Http\Requests\DocumentRequest;
use NIIT\ESign\Http\Requests\SendMailRequest;
use NIIT\ESign\Http\Resources\SignerResource;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        return $this->view('esign::documents.index');
    }

    public function store(DocumentRequest $request)
    {
        $document = Document::create($request->all());

        return $request->expectsJson()
            ? response()->json([
                'id' => $document->id,
                'redirect' => route('esign.documents.show', $document),
            ])
            : redirect()->route('esign.documents.show', $document);
    }

    public function show(Document $document)
    {
        if ($document->statusIsNot(DocumentStatus::DRAFT)) {
            return redirect()->route('esign.documents.submissions.index', $document);
        }

        $loadedRelations = $document->loadMissing('document', 'signers.elements');

        $formattedData = [
            'title' => $document->title,
            'status' => $document->status,
            'notification_sequence' => $document->notification_sequence,
            'signers' => $this->mergeWhen(
                $loadedRelations->signers->count() > 0,
                SignerResource::collection($loadedRelations->signers),
                [['text' => '1st Signer', 'position' => 1, 'elements' => []]]
            )->data,
        ];

        return $this->view('esign::documents.show', compact(
            'document',
            'formattedData',
        ));
    }

    public function update(DocumentRequest $request, Document $document)
    {
        $document->update($request->all());

        return redirect()->route('esign.documents.index');
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return back() ?? redirect()->route('esign.documents.index');
    }

    public function copy(Document $document)
    {
        $replica = $document->replicate();
        $replica->parent_id = $document->id;
        $replica->title = '('.$this->getNextCloneSuffix($document).') '.$document->title;
        $replica->status = DocumentStatus::DRAFT;

        $replica->push();

        $document->relations = [];
        $attachment = $document->loadMissing('document')->document;

        if ($attachment) {
            $attachmentReplica = $attachment->replicate();
            $attachmentReplica->deleted_at = null;

            $replica->document()->save($attachmentReplica);
        }

        return redirect()->route('esign.documents.show', $replica);
    }

    public function sendMail(SendMailRequest $request, Document $document, ?Signer $signer)
    {
        /** @var string $mode */
        $mode = $request->validated('mode');

        if ($mode === 'all') {
            /** @var Collection<Signer> $signers */
            $signers = $document->loadMissing([
                'signers' => fn ($q) => $q->where('send_status', SendStatus::NOT_SENT)->orderBy('position'),
            ])->signers;

            if (count($signers) > 0) {
                foreach ($signers as $s) {
                    ESignFacade::sendSigningLink($s, $document);
                }
            }
        } elseif (isset($signer)) {
            abort_if($signer->sendStatusIs(SendStatus::SENT), 403);

            ESignFacade::sendSigningLink($signer, $document);
        }

        return $this->jsonResponse([
            'status' => 1,
        ])->notify(__('esign::label.mail_sent_successfully'));
    }

    protected function getNextCloneSuffix(Document $document)
    {
        $latestClone = $document->children()
            ->where('title', 'LIKE', '%) '.$document->title)
            ->whereNull('deleted_at')
            ->latest('title')
            ->first();

        if ($latestClone) {
            $matches = [];
            if (preg_match('/\((\d+)\)/', $latestClone->title, $matches)) {
                return (int) $matches[1] + 1;
            }
        }

        return 1;
    }
}
