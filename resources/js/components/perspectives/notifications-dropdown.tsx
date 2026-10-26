// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/notifications-dropdown.tsx
import { Link, usePage } from '@inertiajs/react';
import { Dropdown } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBellSlash } from '@fortawesome/free-solid-svg-icons';
import { timeAgo } from '../../lib/format';

export function NotificationsDropdown() {
    const { props } = usePage();
    // Cast to any or define a proper SharedProps interface
    const notifications = (props.latest_notifications as any[]) ?? [];

    return (
        <Dropdown.Menu className="tf-dropdown tf-notifications">
            <Dropdown.Header className="d-flex justify-content-between align-items-center">
                <span>Notifications</span>
                {notifications.length > 0 && (
                    <Link href="/perspectives/notifications" className="small text-decoration-none">
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
                notifications.map((n) => (
                    <Dropdown.Item
                        key={n.id}
                        as={Link}
                        href={n.perspective_id ? `/perspectives/${n.perspective_id}` : '#'}
                    >
                        <div className="tf-notif">
                            <span className="tf-avatar tf-avatar-xs">
                                {n.actor?.name?.charAt(0).toUpperCase() ?? '?'}
                            </span>
                            <div>
                                <div className="small">{n.message}</div>
                                <small className="text-muted">{timeAgo(n.created_at)}</small>
                            </div>
                        </div>
                    </Dropdown.Item>
                ))
            )}
        </Dropdown.Menu>
    );
}
