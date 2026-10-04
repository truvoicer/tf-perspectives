// packages/truvoicer/tf-perspectives/resources/js/components/NotificationsDropdown.tsx
import { Dropdown } from 'react-bootstrap';
import { Link } from '@inertiajs/react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBellSlash } from '@fortawesome/free-solid-svg-icons';
import { useAuth } from '../hooks/useAuth';
import { timeAgo } from '../lib/format';

export function NotificationsDropdown() {
    const { user } = useAuth();
    const notifications = (user as any)?.notifications ?? []; // populated by controller

    return (
        <Dropdown.Menu className="tf-dropdown tf-notifications">
            <Dropdown.Header className="d-flex justify-content-between align-items-center">
                <span>Notifications</span>
                {notifications.length > 0 && (
                    <Link href="/notifications" className="small">
                        See all
                    </Link>
                )}
            </Dropdown.Header>
            <Dropdown.Divider />
            {notifications.length === 0 ? (
                <div className="tf-empty-sm">
                    <FontAwesomeIcon icon={faBellSlash} className="mb-2" />
                    <div>Nothing new yet.</div>
                </div>
            ) : (
                notifications.slice(0, 6).map((n: any) => (
                    <Dropdown.Item
                        key={n.id}
                        as={Link}
                        href={`/perspectives/${n.perspective_id}`}
                    >
                        <div className="tf-notif">
                            <span className="tf-avatar tf-avatar-xs">
                                {n.actor?.name?.charAt(0).toUpperCase() ?? '?'}
                            </span>
                            <div>
                                <div>{n.message}</div>
                                <small className="text-muted">{timeAgo(n.created_at)}</small>
                            </div>
                        </div>
                    </Dropdown.Item>
                ))
            )}
        </Dropdown.Menu>
    );
}
