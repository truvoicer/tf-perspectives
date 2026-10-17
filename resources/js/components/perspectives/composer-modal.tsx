// packages/truvoicer/tf-perspectives/resources/js/components/ComposerModal.tsx
import { Modal } from 'react-bootstrap';

import type { Category, Perspective } from '../../types/perspectives';
import { Composer } from './composer';

interface ComposerModalProps {
    open: boolean;
    onClose: () => void;
    parent?: Perspective | null;
    onSubmitted?: () => void;
    categories?: Category[];
    editing?: Perspective | null;
}

export function ComposerModal({ open, onClose, parent = null, onSubmitted, categories, editing = null }: ComposerModalProps) {
    const isEditing = editing !== null;
    return (
        <Modal
            show={open}
            onHide={onClose}
            centered
            size="lg"
            backdrop="static"
            className="tf-modal"
        >
            <Modal.Header closeButton>

                <Modal.Title>
                    {isEditing
                        ? 'Edit your perspective'
                        : parent
                            ? 'Step into their shoes'
                            : 'Offer a perspective'}
                </Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <Composer
                    parentId={parent?.id ?? null}
                    parentLabel={parent ? parent.voice ?? parent.author?.full_name ?? null : null}
                    editing={editing}
                    onSuccess={() => {
                        onSubmitted?.();
                        onClose();
                    }}
                    onCancel={onClose}
                    categories={categories}
                />
            </Modal.Body>
        </Modal>
    );
}
