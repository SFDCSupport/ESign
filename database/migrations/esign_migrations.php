<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NIIT\ESign\Enum\AttachmentType;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\ElementType;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropIfTableExists('e_templates');
        $this->dropIfTableExists('e_documents');
        $this->dropIfTableExists('e_attachments');
        $this->dropIfTableExists('e_signers');
        $this->dropIfTableExists('e_signer_elements');
        $this->dropIfTableExists('e_submissions');
        $this->dropIfTableExists('e_audits');
        Schema::enableForeignKeyConstraints();

        Schema::create('e_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->longText('body');
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('parent_id')->nullable()->constrained('e_documents');
            $table->foreignUuid('template_id')->nullable()->constrained('e_templates');
            $table->enum('status', DocumentStatus::values())->default(DocumentStatus::DRAFT);
            $table->enum('notification_sequence', NotificationSequence::values())->default(NotificationSequence::ASYNC);
            $table->boolean('link_sent_to_all')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('model');
            $table->enum('type', AttachmentType::values())->default(AttachmentType::DOCUMENT);
            $table->string('disk');
            $table->string('bucket')->nullable();
            $table->string('file_name');
            $table->string('extension');
            $table->string('path');
            $table->boolean('is_current')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_signers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->uuid('url')->unique();
            $table->string('email')->nullable();
            $table->string('label');
            $table->enum('signing_status', SigningStatus::values())->default(SigningStatus::NOT_SIGNED);
            $table->enum('read_status', ReadStatus::values())->default(ReadStatus::NOT_OPENED);
            $table->enum('send_status', SendStatus::values())->default(SendStatus::NOT_SENT);
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_signer_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->constrained('e_signers');
            $table->enum('type', ElementType::values());
            $table->string('label');
            $table->integer('page_index');
            $table->double('page_width');
            $table->double('page_height');
            $table->double('left');
            $table->double('top');
            $table->double('scale_x')->nullable();
            $table->double('scale_y')->nullable();
            $table->double('width');
            $table->double('height');
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->constrained('e_signers');
            $table->foreignUuid('signer_element_id')->constrained('e_signer_elements');
            $table->longText('data');
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps('restored_at');
        });

        Schema::create('e_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->nullable()->constrained('e_signers');
            $table->string('event');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps('restored_at');
        });
    }

    public function down(): void
    {
        Schema::drop('e_audits');
        Schema::drop('e_submissions');
        Schema::drop('e_signer_elements');
        Schema::drop('e_signers');
        Schema::drop('e_documents');
        Schema::drop('e_templates');
        Schema::drop('e_attachments');
    }

    protected function dropIfTableExists(string $table): void
    {
        if (Schema::hasTable($table)) {
            Schema::drop($table);
        }
    }
};
