<?php

namespace NIIT\ESign\Livewire;

use Livewire\Component;
use NIIT\ESign\Models\Document;

class DocumentComponent extends Component
{
    public ?string $search = '';

    public string $filter = 'all';

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('esign::livewire.documents-component')
            ->with([
                'documents' => Document::with('creator', 'document')
                    ->when(! blank($this->search),
                        fn ($q) => $q->where('title', 'LIKE', "%{$this->search}%")
                    )->when($this->filter !== 'all',
                        fn ($q) => $q->where('status', $this->filter)
                    )->get(),
            ]);
    }
}
