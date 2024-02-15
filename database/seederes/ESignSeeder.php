<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use NIIT\ESign\Models\Template;

class ESignSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([] as $template) {
            Template::create($template);
        }
    }

    protected function signingLink(): array
    {
        return [

        ];
    }

    protected function signingCompleted(): void
    {
        //
    }

    protected function signedByAll(): void
    {
        //
    }

    protected function signingPending(): void
    {
        //
    }
}
