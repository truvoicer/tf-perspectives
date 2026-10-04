// packages/truvoicer/tf-perspectives/resources/js/components/ReportModal.tsx
import { useState } from 'react';
import { Modal, Form, Button } from 'react-bootstrap';
import { router } from '@inertiajs/react';

const REASONS = [
    { value: 'spam', label: 'Spam or promotional' },
    { value: 'harassment', label: 'Harassment or hate' },
    { value: 'misinformation', label: 'Misinformation' },
    { value: 'off-topic', label: 'Off-topic' },
    { value: 'other', label: 'Something else' },
];

interface ReportModalProps {
    open: boolean;
    perspectiveId: number | null;
    onClose: () => void;
}

export function ReportModal({ open, perspectiveId, onClose }: ReportModalProps) {
    const [reason, setReason] = useState('spam');
    const [notes, setNotes] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = () => {
        if (!perspectiveId) return;
        setBusy(true);
        router.post(
            `/perspectives/${perspectiveId}/report`,
            { reason, notes },
            {
                preserveScroll: true,
                onFinish: () => {
                    setBusy(false);
                    setNotes('');
                    onClose();
                },
            },
        );
    };

    return (
        <Modal show={open} onHide={onClose} centered className="tf-modal">
            <Modal.Header closeButton>
                <Modal.Title>Report perspective</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <p className="text-muted small">
                    Reports are reviewed by moderators. False reports may affect your account.
                </p>
                <Form.Group>
                    <Form.Label>Why are you reporting this?</Form.Label>
                    <Form.Select value={reason} onChange={(e) => setReason(e.target.value)}>
                        {REASONS.map((r) => (
                            <option key={r.value} value={r.value}>{r.label}</option>
                        ))}
                    </Form.Select>
                </Form.Group>
                <Form.Group className="mt-3">
                    <Form.Label>Notes (optional)</Form.Label>
                    <Form.Control
                        as="textarea"
                        rows={3}
                        value={notes}
                        onChange={(e) => setNotes(e.target.value)}
                        maxLength={500}
                    />
                </Form.Group>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="outline-secondary" onClick={onClose}>Cancel</Button>
                <Button variant="danger" onClick={submit} disabled={busy}>
                    {busy ? 'Sending…' : 'Submit report'}
                </Button>
            </Modal.Footer>
        </Modal>
    );
}
