<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NIIT\ESign\Enum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropIfTableExists('e_templates');
        $this->dropIfTableExists('e_documents');
        $this->dropIfTableExists('e_signers');
        $this->dropIfTableExists('e_signer_elements');
        $this->dropIfTableExists('e_assets');
        $this->dropIfTableExists('e_audits');
        Schema::enableForeignKeyConstraints();

        Schema::create('e_templates', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->longText('body');
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_documents', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('parent_id')->nullable()->constrained('e_documents');
            $table->foreignUuid('template_id')->nullable()->constrained('e_templates');
            $table->enum('status', Enum\DocumentStatus::values())->default(config('esign.defaults.document_status'));
            $table->enum('notification_sequence', Enum\NotificationSequence::values())->default(config('esign.defaults.notification_sequence'));
            $table->boolean('link_sent_to_all')->default(false);
            $table->boolean('is_signed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_signers', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->uuid('url')->unique();
            $table->string('email')->nullable();
            $table->string('text');
            $table->enum('signing_status', Enum\SigningStatus::values())->default(Enum\SigningStatus::NOT_SIGNED);
            $table->enum('read_status', Enum\ReadStatus::values())->default(Enum\ReadStatus::NOT_OPENED);
            $table->enum('send_status', Enum\SendStatus::values())->default(Enum\SendStatus::NOT_SENT);
            $table->boolean('is_next_receiver')->default(true);
            $table->integer('position')->default(0);
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_signer_elements', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->constrained('e_signers');
            $table->enum('type', Enum\ElementType::values());
            $table->string('text');
            $table->integer('page_index');
            $table->double('page_width');
            $table->double('page_height');
            $table->double('left');
            $table->double('top');
            $table->double('width');
            $table->double('height');
            $table->integer('position')->default(0);
            $table->boolean('is_required')->default(true);
            $table->text('data')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_assets', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('model');
            $table->enum('type', Enum\AssetType::values());
            $table->string('disk');
            $table->string('bucket')->nullable();
            $table->string('file_name');
            $table->string('extension');
            $table->string('path');
            $table->boolean('is_snapshot')->default(false);
            $table->enum('snapshot_type', Enum\SnapshotType::values())->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });

        Schema::create('e_audits', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('e_documents');
            $table->foreignUuid('signer_id')->nullable()->constrained('e_signers');
            $table->foreignUuid('element_id')->nullable()->constrained('e_signer_elements');
            $table->enum('event', Enum\AuditEvent::values());
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->eSignUserStamps();
        });
    }

    public function down(): void
    {
        Schema::drop('e_audits');
        Schema::drop('e_assets');
        Schema::drop('e_signer_elements');
        Schema::drop('e_signers');
        Schema::drop('e_documents');
        Schema::drop('e_templates');
    }

    protected function dropIfTableExists(string $table): void
    {
        if (Schema::hasTable($table)) {
            Schema::drop($table);
        }
    }
};
