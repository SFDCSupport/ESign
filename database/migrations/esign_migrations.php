<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\ElementType;
use NIIT\ESign\Enum\MailEvent;
use NIIT\ESign\Enum\SignerStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('name');
            $table->string('disk');
            $table->string('extension');
            $table->enum('status', DocumentStatus::values())->default(DocumentStatus::DRAFT);
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_document_signers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('e_document_id')->constrained('e_documents');
            $table->string('email')->nullable();
            $table->enum('mail_event', MailEvent::values())->default(MailEvent::NOT_SENT);
            $table->enum('status', SignerStatus::values())->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_document_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('e_document_id')->constrained('e_documents');
            $table->foreignUuid('e_signer_id')->constrained('e_signers');
            $table->enum('type', ElementType::values());
            $table->integer('on_page');
            $table->integer('x_axis');
            $table->integer('y_axis');
            $table->integer('width');
            $table->integer('height');
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_document_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('e_document_id')->constrained('e_documents');
            $table->foreignUuid('e_signer_id')->constrained('e_signers');
            $table->string('event');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::drop('esign');
    }
};
