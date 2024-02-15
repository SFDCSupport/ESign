<?php

namespace NIIT\ESign\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use NIIT\ESign\Models\Template;

class ESignSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Template::truncate();
        Schema::enableForeignKeyConstraints();

        foreach (glob(__DIR__.'/../../resources/views/mails/*.blade.php') as $file) {
            $filename = Str::of(pathinfo($file, PATHINFO_FILENAME))
                ->title()
                ->replace('.blade', '')
                ->replace('-', ' ')
                ->value();

            Template::create([
                'title' => $filename,
                'body' => file_get_contents($file),
            ]);
        }
    }
}
