import { faClock, faFire, faPlus, faShoePrints, faStar, faUsers } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { router, usePage } from '@inertiajs/react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { useState } from 'react';
import { Badge, Button, Tab, Dropdown, Nav, Navbar } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { ComposerModal } from '../../../components/perspectives/composer-modal';
import EmptyState from '../../../components/perspectives/empty-state';
import Pagination from '../../../components/perspectives/pagination';
import { PerspectiveCard } from '../../../components/perspectives/perspective-card';
import { ReportModal } from '../../../components/perspectives/report-modal';
import type { Category, Paginator, Perspective } from '../../../types/perspectives';

interface FeedBlockProps {
    perspectives: Paginator<Perspective>;
    filters: { q: string; tab: string };
}

export default function FeedBlock(props: FeedBlockProps) {
    const { user } = useAuth();

    const { perspectives, filters } = usePage<{ perspectives: Paginator<Perspective>; filters: { q: string; tab: string } }>().props;
    const [reportId, setReportId] = useState<number | null>(null);

    const [editing, setEditing] = useState<Perspective | null>(null);
    const [composerOpen, setComposerOpen] = useState(false);

    const tab = filters?.tab || 'latest';

    const switchTab = (next: string) => {
        router.reload({
            data: { ...filters, tab: next },
        });
    };

    return (
        <div className="tf-panel">
            <div className="d-flex justify-content-between align-items-center">
            <Tab.Container activeKey={tab} onSelect={(k) => k && switchTab(k)}>
                <Nav variant="pills" className="tf-tabs mb-3">
                    <Nav.Item>
                        <Nav.Link eventKey="latest">
                            <FontAwesomeIcon icon={faClock} className="me-2" />
                            Latest
                        </Nav.Link>
                    </Nav.Item>
                    <Nav.Item>
                        <Nav.Link eventKey="trending">
                            <FontAwesomeIcon icon={faFire} className="me-2" />
                            Trending
                        </Nav.Link>
                    </Nav.Item>
                    <Nav.Item>
                        <Nav.Link eventKey="featured">
                            <FontAwesomeIcon icon={faStar} className="me-2" />
                            Featured
                        </Nav.Link>
                    </Nav.Item>
                    {user && (
                        <Nav.Item>
                            <Nav.Link eventKey="following">
                                <FontAwesomeIcon icon={faUsers} className="me-2" />
                                Following
                            </Nav.Link>
                        </Nav.Item>
                    )}
                </Nav>
            </Tab.Container>
            <Button variant="primary" className="" onClick={() => setComposerOpen(true)} aria-label="New perspective">
                    <FontAwesomeIcon icon={faPlus} />
                </Button>
            </div>

            {Array.isArray(perspectives?.data) && perspectives.data.length === 0 ? (
                <EmptyState
                    icon={faShoePrints}
                    title="No perspectives yet"
                    body={
                        tab === 'following'
                            ? 'Follow a few voices and their perspectives will appear here.'
                            : 'Be the first pair of shoes — start a new perspective.'
                    }
                />
            ) : (
                <div className="tf-feed">
                    {Array.isArray(perspectives?.data) && perspectives.data.map((p) => (
                        <PerspectiveCard
                            key={p.id}
                            perspective={p}
                            canInteract={Boolean(user)}
                            truncate
                            onReport={setReportId}
                            onEdit={(persp) => setEditing(persp)}
                        />
                    ))}
                </div>
            )}

            <Pagination paginator={perspectives} />

            <ComposerModal open={composerOpen} onClose={() => setComposerOpen(false)} editing={editing} />
            <ReportModal open={reportId !== null} perspectiveId={reportId} onClose={() => setReportId(null)} />
        </div>
    );
}
