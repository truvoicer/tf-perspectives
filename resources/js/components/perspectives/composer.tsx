// packages/truvoicer/tf-perspectives/resources/js/components/Composer.tsx
import { useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { Button, Form, Row, Col, Badge, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faEyeSlash, faEye, faTag, faLayerGroup, faFaceSmile,
    faCircleInfo,
} from '@fortawesome/free-solid-svg-icons';
import { useAuth } from '../hooks/useAuth';
import { categoryIcon } from '../lib/icons';
import type { Category, ComposerPayload } from '../types';

const MOODS = ['hopeful', 'weary', 'angry', 'curious', 'grateful', 'afraid', 'determined'] as const;

interface ComposerProps {
    parentId?: number | null;
    parentLabel?: string | null;
    categories?: Category[];
    onCancel?: () => void;
    onSuccess?: () => void;
    submitLabel?: string;
    compact?: boolean;
}

interface ComposerForm {
    voice: string;
    body: string;
    parent_id: number | null;
    category_id: number | null;
    mood: string;
    tags: string;
    is_anonymous: boolean;
    [key: string]: string | number | boolean | null;
}

export function Composer({
    parentId = null,
    parentLabel = null,
    categories = [],
    onCancel,
    onSuccess,
    submitLabel,
    compact = false,
}: ComposerProps) {
    const { user } = useAuth();
    const [tagInput, setTagInput] = useState('');

    const { data, setData, post, processing, errors, reset } = useForm<ComposerForm>({
        voice: '',
        body: '',
        parent_id: parentId,
        category_id: null,
        mood: '',
        tags: '',
        is_anonymous: false,
    });

    if (!user) {
        return (
            <div className="tf-composer-locked">
                <FontAwesomeIcon icon={faEye} className="me-2" />
                <a href="/login">Sign in</a> to add your perspective.
            </div>
        );
    }

    const isBranch = parentId !== null;
    const bodyLeft = 2000 - data.body.length;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/perspectives', {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setTagInput('');
                onSuccess?.();
            },
        });
    };

    const addTag = () => {
        const t = tagInput.trim().toLowerCase().replace(/[^a-z0-9-]/g, '');
        if (!t) return;
        const current = data.tags ? data.tags.split(',') : [];
        if (!current.includes(t) && current.length < 5) {
            setData('tags', [...current, t].join(','));
        }
        setTagInput('');
    };

    const removeTag = (tag: string) => {
        const next = data.tags
            .split(',')
            .filter((t) => t && t !== tag)
            .join(',');
        setData('tags', next);
    };

    const currentTags = data.tags ? data.tags.split(',').filter(Boolean) : [];

    return (
        <Form className="tf-composer" onSubmit={submit}>
            {isBranch && parentLabel && (
                <div className="tf-composer-context">
                    <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                    Stepping into the shoes of <strong>{parentLabel}</strong>
                </div>
            )}

            <Row className="g-3">
                <Col md={6}>
                    <Form.Group>
                        <Form.Label className="tf-label">
                            <FontAwesomeIcon icon={faCircleInfo} className="me-2" />
                            Whose shoes?
                        </Form.Label>
                        <Form.Control
                            type="text"
                            value={data.voice}
                            onChange={(e) => setData('voice', e.target.value)}
                            maxLength={120}
                            placeholder={
                                isBranch
                                    ? 'As a person who…'
                                    : 'As a night-shift nurse / As a landlord'
                            }
                        />
                        {errors.voice && (
                            <Form.Text className="text-danger">{errors.voice}</Form.Text>
                        )}
                    </Form.Group>
                </Col>

                {!isBranch && (
                    <Col md={6}>
                        <Form.Group>
                            <Form.Label className="tf-label">
                                <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                                Category
                            </Form.Label>
                            <Form.Select
                                value={data.category_id ?? ''}
                                onChange={(e) =>
                                    setData(
                                        'category_id',
                                        e.target.value ? Number(e.target.value) : null,
                                    )
                                }
                            >
                                <option value="">— Choose a category —</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </Form.Select>
                        </Form.Group>
                    </Col>
                )}
            </Row>

            <Form.Group className="mt-3">
                <Form.Label className="tf-label">
                    What does the world look like from here?
                </Form.Label>
                <Form.Control
                    as="textarea"
                    rows={compact ? 4 : 6}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    minLength={10}
                    maxLength={2000}
                    required
                    placeholder="Describe what you see, what you fear, what you want — without arguing for it."
                />
                <div className="d-flex justify-content-between mt-1">
                    {errors.body ? (
                        <Form.Text className="text-danger">{errors.body}</Form.Text>
                    ) : (
                        <span />
                    )}
                    <small
                        className={
                            bodyLeft < 100 ? 'text-danger' : 'text-muted'
                        }
                    >
                        {bodyLeft} left
                    </small>
                </div>
            </Form.Group>

            {!isBranch && (
                <>
                    <Form.Group className="mt-3">
                        <Form.Label className="tf-label">
                            <FontAwesomeIcon icon={faTag} className="me-2" />
                            Tags <small className="text-muted">(up to 5)</small>
                        </Form.Label>
                        <div className="tf-tag-input">
                            <div className="tf-tag-list">
                                {currentTags.map((tag) => (
                                    <Badge
                                        key={tag}
                                        bg="light"
                                        text="dark"
                                        className="tf-tag"
                                        onClick={() => removeTag(tag)}
                                    >
                                        #{tag} <span className="tf-tag-x">×</span>
                                    </Badge>
                                ))}
                            </div>
                            <div className="d-flex gap-2">
                                <Form.Control
                                    size="sm"
                                    placeholder="add a tag and press Enter"
                                    value={tagInput}
                                    onChange={(e) => setTagInput(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter' || e.key === ',') {
                                            e.preventDefault();
                                            addTag();
                                        }
                                    }}
                                    disabled={currentTags.length >= 5}
                                />
                                <Button
                                    size="sm"
                                    variant="outline-secondary"
                                    type="button"
                                    onClick={addTag}
                                    disabled={!tagInput.trim() || currentTags.length >= 5}
                                >
                                    Add
                                </Button>
                            </div>
                        </div>
                    </Form.Group>

                    <Form.Group className="mt-3">
                        <Form.Label className="tf-label">
                            <FontAwesomeIcon icon={faFaceSmile} className="me-2" />
                            Tone
                        </Form.Label>
                        <div className="tf-moods">
                            {MOODS.map((m) => (
                                <button
                                    key={m}
                                    type="button"
                                    className={`tf-mood ${data.mood === m ? 'is-on' : ''}`}
                                    onClick={() =>
                                        setData('mood', data.mood === m ? '' : m)
                                    }
                                >
                                    {m}
                                </button>
                            ))}
                        </div>
                    </Form.Group>
                </>
            )}

            <div className="tf-composer-options">
                <Form.Check
                    type="switch"
                    id="anon"
                    label="Post anonymously"
                    checked={data.is_anonymous}
                    onChange={(e) => setData('is_anonymous', e.target.checked)}
                />
                <OverlayTrigger
                    placement="top"
                    overlay={
                        <Tooltip>
                            Anonymous posts still count toward your reputation and dashboard.
                        </Tooltip>
                    }
                >
                    <span className="tf-help"><FontAwesomeIcon icon={faEyeSlash} /></span>
                </OverlayTrigger>
            </div>

            <div className="tf-composer-actions">
                <Button
                    variant="primary"
                    type="submit"
                    disabled={processing || data.body.trim().length < 10}
                >
                    {processing
                        ? 'Saving…'
                        : submitLabel ?? (isBranch ? 'Add this perspective' : 'Share perspective')}
                </Button>
                {onCancel && (
                    <Button variant="outline-secondary" type="button" onClick={onCancel}>
                        Cancel
                    </Button>
                )}
            </div>
        </Form>
    );
}
