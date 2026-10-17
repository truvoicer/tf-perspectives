// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/shoe-discussion-thread.tsx
import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { Button, Form } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faReply, faTrash } from '@fortawesome/free-solid-svg-icons';
import { route } from 'ziggy-js';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { initials, timeAgo } from '../../lib/format';
import type { PerspectiveShoe, ShoeDiscussion } from '../../types/perspectives';

interface Props {
    shoe: PerspectiveShoe;
    discussion: ShoeDiscussion;
}

function DiscussionNode({ shoe, discussion }: Props) {
    const { user } = useAuth();
    const [isReplying, setIsReplying] = useState(false);

    const { data, setData, post, processing, reset } = useForm({ body: '' });

    const submitReply = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('perspectives.shoes.discussions.store', { perspective: shoe.perspective_id, perspectiveShoe: shoe.id }), {
            preserveScroll: true,
            data: { body: data.body, parent_id: discussion.id },
            onSuccess: () => {
                reset();
                setIsReplying(false);
            },
        });
    };

    const deleteDiscussion = () => {
        if (!confirm('Delete this discussion?')) return;
        // Inertia router delete...
        import('@inertiajs/react').then(({ router }) => {
            router.delete(route('shoes.discussions.destroy', { discussion: discussion.id }), {
                preserveScroll: true,
            });
        });
    };

    const authorName = discussion.is_anonymous && !discussion.author
        ? 'Anonymous'
        : discussion.author?.display_name ?? 'Unknown';

    return (
        <div className="tf-discussion-node mb-3" style={{ marginLeft: discussion.depth > 0 ? '1.5rem' : 0 }}>
            <div className="d-flex gap-2">
                <span className="tf-avatar tf-avatar-xs mt-1">
                    {initials(authorName)}
                </span>
                <div className="flex-grow-1">
                    <div className="d-flex align-items-center gap-2 mb-1">
                        <strong className="small">{authorName}</strong>
                        <span className="text-muted small">· {timeAgo(discussion.created_at)}</span>
                    </div>
                    <p className="mb-1 text-sm">{discussion.body}</p>

                    <div className="d-flex gap-3 mt-1">
                        {user && (
                            <button
                                className="btn btn-link p-0 text-muted text-decoration-none small"
                                onClick={() => setIsReplying(!isReplying)}
                            >
                                <FontAwesomeIcon icon={faReply} className="me-1" /> Reply
                            </button>
                        )}
                        {discussion.is_owner && (
                            <button
                                className="btn btn-link p-0 text-danger text-decoration-none small"
                                onClick={deleteDiscussion}
                            >
                                <FontAwesomeIcon icon={faTrash} className="me-1" /> Delete
                            </button>
                        )}
                    </div>

                    {isReplying && (
                        <Form onSubmit={submitReply} className="mt-2">
                            <Form.Control
                                as="textarea"
                                rows={2}
                                size="sm"
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                placeholder="Reply to this..."
                                required
                                className="mb-2"
                            />
                            <div className="d-flex gap-2 justify-content-end">
                                <Button size="sm" variant="outline-secondary" onClick={() => setIsReplying(false)}>
                                    Cancel
                                </Button>
                                <Button size="sm" variant="primary" type="submit" disabled={processing}>
                                    Reply
                                </Button>
                            </div>
                        </Form>
                    )}

                    {/* Recursive children */}
                    {discussion.children?.map((child) => (
                        <DiscussionNode key={child.id} shoe={shoe} discussion={child} />
                    ))}
                </div>
            </div>
        </div>
    );
}

export function ShoeDiscussionThread({ shoe, discussions = [] }: { shoe: PerspectiveShoe, discussions?: ShoeDiscussion[] }) {
    if (!discussions.length) {
        return <p className="text-muted small italic">No discussions yet. Be the first to reply.</p>;
    }
    return (
        <div className="tf-discussion-thread mt-3 pt-3 border-top">
            {discussions.map((d) => (
                <DiscussionNode key={d.id} shoe={shoe} discussion={d} />
            ))}
        </div>
    );
}
