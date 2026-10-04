// packages/truvoicer/tf-perspectives/resources/js/components/ComposerModal.tsx
import { Modal } from 'react-bootstrap';
import { Composer } from './Composer';
import type { ComposerPayload, Perspective } from '../types';

interface ComposerModalProps {
    open: boolean;
    onClose: () => void;
    parent?: Perspective | null;
    onSubmitted?: () => void;
}

export function ComposerModal({ open, onClose, parent = null, onSubmitted }: ComposerModalProps) {
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
                    {parent ? 'Step into their shoes' : 'Offer a perspective'}
                </Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <Composer
                    parentId={parent?.id ?? null}
                    parentLabel={parent ? parent.voice ?? parent.author?.name ?? null : null}
                    onSuccess={() => {
                        onSubmitted?.();
                        onClose();
                    }}
                    onCancel={onClose}
                />
            </Modal.Body>
        </Modal>
    );
}
