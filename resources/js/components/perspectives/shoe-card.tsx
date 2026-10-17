// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/shoe-card.tsx
import { useState } from 'react';
import { Form, Button } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faComment, faChevronDown, faChevronUp } from '@fortawesome/free-solid-svg-icons';
import { useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { initials, timeAgo } from '../../lib/format';
import type { PerspectiveShoe } from '../../types/perspectives';
import { ShoeDiscussionThread } from './shoe-discussion-thread';

export function ShoeCard({ shoe }: { shoe: PerspectiveShoe }) {
    const { user } = useAuth();
    const [showDiscussions, setShowDiscussions] = useState(false);

    const { data, setData, post, processing, reset } = useForm({ body: '' });

    const submitDiscussion = (e: React.FormEvent) => {
        e.preventDefault();

        post(route('perspectives.shoes.discussions.store', { perspective: shoe.perspective_id, perspectiveShoe: shoe.id }), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setShowDiscussions(true); // open thread so they see their post
            },
        });
    };

    const authorName = shoe.is_anonymous && !shoe.author
        ? 'Anonymous'
        : shoe.author?.display_name ?? 'Unknown';

    return (
        <div className={`tf-shoe-card ${shoe.changed_my_view ? 'is-impactful' : ''}`}>
            {shoe.changed_my_view && (
                <div className="tf-impact-badge">
                    Changed the author's view
                </div>
            )}

            <header className="tf-shoe-head">
                <span className="tf-avatar tf-avatar-sm">{initials(authorName)}</span>
                <div>
                    <strong>{shoe.voice}</strong>
                    <div className="text-muted small">
                        {authorName} · {timeAgo(shoe.created_at)}
                    </div>
                </div>
            </header>

            <p className="tf-shoe-body">{shoe.body}</p>

            <footer className="tf-shoe-foot">
                <button
                    className="btn btn-link p-0 text-decoration-none text-muted"
                    onClick={() => setShowDiscussions(!showDiscussions)}
                >
                    <FontAwesomeIcon icon={faComment} className="me-1" />
                    {shoe.discussions_count} discussions
                    <FontAwesomeIcon icon={showDiscussions ? faChevronUp : faChevronDown} className="ms-2" />
                </button>
            </footer>

            {showDiscussions && (
                <div className="tf-shoe-discussions">
                    <ShoeDiscussionThread shoe={shoe} discussions={shoe.discussions} />

                    {user && (
                        <Form onSubmit={submitDiscussion} className="mt-3">
                            <Form.Control
                                as="textarea"
                                rows={2}
                                size="sm"
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                placeholder="Start a discussion about this position..."
                                required
                            />
                            <div className="text-end mt-2">
                                <Button size="sm" variant="outline-primary" type="submit" disabled={processing}>
                                    Post discussion
                                </Button>
                            </div>
                        </Form>
                    )}
                </div>
            )}
        </div>
    );
}
