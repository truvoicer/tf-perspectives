// packages/truvoicer/tf-perspectives/resources/js/pages/notifications.tsx
import { Link, router } from '@inertiajs/react';
import { Button } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBell, faCheckDouble } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/perspectives/layout';
import EmptyState from '../components/perspectives/empty-state';
import { timeAgo, initials } from '../lib/format';
import { route } from 'ziggy-js';

interface NotificationItem {
    id: string;
    type: string;
    actor: { name: string } | null;
    message: string;
    perspective_id: number;
    created_at: string;
    read_at: string | null;
}

interface NotificationsProps {
    notifications: {
        data: NotificationItem[];
        links: { url: string | null; label: string; active: boolean }[];
        last_page: number;
        current_page: number;
    };
}

export default function Notifications({ notifications }: NotificationsProps) {
    const markAll = () => {
        router.post(route('perspectives.notifications.read-all'), {}, { preserveScroll: true });
    };

    const markOne = (id: string) => {
        router.post(route('perspectives.notifications.read', { id }), {}, { preserveScroll: true });
    };

    return (
        <Layout>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1 className="tf-page-title mb-0">
                    <FontAwesomeIcon icon={faBell} className="me-2" />
                    Notifications
                </h1>
                {notifications.data.some((n) => !n.read_at) && (
                    <Button variant="outline-secondary" onClick={markAll}>
                        <FontAwesomeIcon icon={faCheckDouble} className="me-2" />
                        Mark all as read
                    </Button>
                )}
            </div>

            <div className="tf-panel">
                {notifications.data.length === 0 ? (
                    <EmptyState
                        icon={faBell}
                        title="All caught up"
                        body="You'll see reactions, branches and follows here."
                    />
                ) : (
                    <ul className="tf-notifications-list">
                        {notifications.data.map((n) => (
                            <li
                                key={n.id}
                                className={`tf-notif-row ${!n.read_at ? 'is-unread' : ''}`}
                                onClick={() => !n.read_at && markOne(n.id)}
                                style={{ cursor: n.read_at ? 'default' : 'pointer' }}
                            >
                                <span className="tf-avatar tf-avatar-sm">
                                    {initials(n.actor?.name)}
                                </span>
                                <div className="tf-notif-body">
                                    {n.perspective_id ? (
                                        <Link href={route('perspectives.show', { perspective: n.perspective_id })}>
                                            {n.message}
                                        </Link>
                                    ) : (
                                        <span>{n.message}</span>
                                    )}
                                    <small className="text-muted">
                                        {timeAgo(n.created_at)}
                                    </small>
                                </div>
                                {!n.read_at && (
                                    <span className="tf-unread-dot" aria-hidden="true" />
                                )}
                            </li>
                        ))}
                    </ul>
                )}

                {/* Simple Pagination */}
                {notifications.last_page > 1 && (
                    <div className="tf-pagination">
                        {notifications.links.map((link, i) => (
                            link.url ? (
                                <Link key={i} href={link.url} className={`tf-page-link ${link.active ? 'is-active' : ''}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                            ) : (
                                <span key={i} className="tf-page-link is-disabled" dangerouslySetInnerHTML={{ __html: link.label }} />
                            )
                        ))}
                    </div>
                )}
            </div>
        </Layout>
    );
}
