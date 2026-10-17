// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/shoe-composer.tsx
import { useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { Button, Form } from 'react-bootstrap';
import { route } from 'ziggy-js';
import type { Perspective } from '../../types/perspectives';

interface ShoeComposerProps {
    perspective: Perspective;
    onSuccess?: () => void;
    onCancel?: () => void;
}

export function ShoeComposer({ perspective, onSuccess, onCancel }: ShoeComposerProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        voice: '',
        body: '',
        mood: '',
        is_anonymous: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        console.log(data);
        post(route('perspectives.shoes.store', { perspective: perspective.id }), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onSuccess?.();
            },
        });
    };

    return (
        <Form className="tf-shoe-composer" onSubmit={submit}>
            <h4 className="tf-side-title mb-3">Step into their shoes</h4>
            <p className="text-muted small mb-4">
                Describe a position you occupy relative to this perspective. Don't argue against
                the original post — just share what the world looks like from where you stand.
            </p>

            <Form.Group className="mb-3">
                <Form.Label className="tf-label">Whose shoes are you in?</Form.Label>
                <Form.Control
                    value={data.voice}
                    onChange={(e) => setData('voice', e.target.value)}
                    placeholder="As a neighbour with a disability..."
                    required
                    maxLength={120}
                    isInvalid={!!errors.voice}
                />
                <Form.Control.Feedback type="invalid">{errors.voice}</Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-3">
                <Form.Label className="tf-label">What does it look like from here?</Form.Label>
                <Form.Control
                    as="textarea"
                    rows={4}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    required
                    minLength={10}
                    maxLength={2000}
                    isInvalid={!!errors.body}
                />
                <Form.Control.Feedback type="invalid">{errors.body}</Form.Control.Feedback>
            </Form.Group>

            <div className="d-flex justify-content-end gap-2 mt-4">
                {onCancel && (
                    <Button variant="outline-secondary" type="button" onClick={onCancel}>
                        Cancel
                    </Button>
                )}
                <Button variant="primary" type="submit" disabled={processing}>
                    {processing ? 'Saving…' : 'Add these shoes'}
                </Button>
            </div>
        </Form>
    );
}
