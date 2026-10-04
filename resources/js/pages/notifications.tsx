// packages/truvoicer/tf-perspectives/resources/js/pages/notifications.tsx
import { Link, router } from '@inertiajs/react';
import { Button } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBell, faCheckDouble } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import EmptyState from '../components/EmptyState';
import { timeAgo, initials } from '../lib/format';
import type { NotificationItem } from '../types';

interface NotificationsProps {
    notifications: {
        data: NotificationItem[];
        next_page_url: string | null;
        last_page: number;
        current_page: number;
    };
}

export default function Notifications({ notifications }: NotificationsProps) {
    const markAll = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true });
    };

    const markOne = (id: string) => {
        router.post(`/notifications/${id}/read`, {}, { preserveScroll: true });
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
                                onClick={() => markOne(n.id)}
                            >
                                <span className="tf-avatar tf-avatar-sm">
                                    {initials(n.actor?.name)}
                                </span>
                                <div className="tf-notif-body">
                                    <Link href={`/perspectives/${n.perspective_id}`}>
                                        {n.message}
                                    </Link>
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
            </div>
        </Layout>
    );
}
