<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\ElementType;
use NIIT\ESign\Enum\MailStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SignerStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->longText('body');
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('file_name');
            $table->string('disk');
            $table->string('extension');
            $table->string('path');
            $table->foreignUuid('template_id')->nullable()->constrained('e_templates');
            $table->enum('status', DocumentStatus::values())->default(DocumentStatus::DRAFT);
            $table->enum('notification_sequence', NotificationSequence::values())->default(NotificationSequence::ASYNC);
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_document_signers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->string('email')->nullable();
            $table->enum('mail_status', MailStatus::values())->default(MailStatus::NOT_SENT);
            $table->enum('status', SignerStatus::values())->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps();
        });

        Schema::create('e_document_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->constrained('e_signers');
            $table->enum('type', ElementType::values());
            $table->integer('on_page');
            $table->integer('x_axis');
            $table->integer('y_axis');
            $table->integer('width');
            $table->integer('height');
            $table->timestamps();
            $table->softDeletes();
            $table->userStamps(['created_by']);
        });

        Schema::create('e_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->nullable()->constrained('e_signers');
            $table->string('event');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::drop('e_documents');
        Schema::drop('e_signers');
        Schema::drop('e_signer_elements');
        Schema::drop('e_audits');
        Schema::drop('e_templates');
    }
};
