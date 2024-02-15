<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum AuditEvent: string
{
    use EnumSupport;

    case SIGNING_STARTED = 'signing-started';
    case SIGNING_COMPLETED = 'signing-completed';
    case DOCUMENT_SIGNED = 'document-signed';
    case DOCUMENT_CREATED = 'document-created';
    case DOCUMENT_DELETED = 'document-deleted';
    case DOCUMENT_DELETED_FORCE = 'document-deleted-force';
    case DOCUMENT_UPDATED = 'document-updated';
    case DOCUMENT_RESTORED = 'document-restored';
    case DOCUMENT_STATUS_CHANGED = 'document-status-changed';
    case DOCUMENT_NOTIFICATION_SEQUENCE_CHANGED = 'document-notification-sequence-changed';
    case SIGNER_ADDED = 'signer-added';
    case SIGNER_DELETED = 'signer-deleted';
    case SIGNER_DELETED_FORCE = 'signer-deleted-force';
    case SIGNER_UPDATED = 'signer-updated';
    case SIGNER_RESTORED = 'signer-restored';
    case SIGNER_READ_STATUS_CHANGED = 'signer-read-status-changed';
    case SIGNER_SEND_STATUS_CHANGED = 'signer-send-status-changed';
    case SIGNER_SIGNING_STATUS_CHANGED = 'signer-signing-status-changed';
    case ELEMENT_ADDED = 'element-added';
    case ELEMENT_UPDATED = 'element-updated';
    case ELEMENT_DELETED = 'element-deleted';
    case ELEMENT_DELETED_FORCE = 'element-deleted-force';
    case ELEMENT_RESTORED = 'element-restored';
}
