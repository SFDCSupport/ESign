<?php

namespace NIIT\ESign\Livewire;

use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;
use NIIT\ESign\Models\Document;

#[Lazy(isolate: false)]
class DocumentComponent extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filter = 'all';

    protected $paginationTheme = 'bootstrap';

    public function setFilter($filter): void
    {
        $this->filter = $filter;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="text-center no_file_found_section">
            <i class="fa-solid fa-spinner"></i>
        </div>
        HTML;
    }

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('esign::livewire.documents-component')
            ->with([
                'documents' => Document::with('creator', 'document')
                    ->when(! blank($this->search),
                        fn ($q) => $q->where('title', 'LIKE', "%{$this->search}%")
                    )->when($this->filter !== 'all',
                        fn ($q) => $q->where('status', $this->filter)
                    )->paginate(2),
            ]);
    }
}
