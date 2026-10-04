// packages/truvoicer/tf-perspectives/resources/js/pages/feed.tsx
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Row, Col, Tab, Nav, Badge } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faFire, faClock, faUsers, faStar, faLayerGroup,
    faShoePrints, faChartLine,
} from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import { PerspectiveCard } from '../components/PerspectiveCard';
import Pagination from '../components/Pagination';
import EmptyState from '../components/EmptyState';
import { ReportModal } from '../components/ReportModal';
import { useAuth } from '../hooks/useAuth';
import { categoryIcon } from '../lib/icons';
import type { Category, Paginator, Perspective } from '../types';

interface FeedProps {
    perspectives: Paginator<Perspective>;
    featured?: Perspective[];
    categories?: Category[];
    filters: { q: string; tab: string };
    stats?: {
        total_perspectives: number;
        total_branches: number;
        total_voices: number;
        today: number;
    };
}

export default function Feed({
    perspectives,
    featured = [],
    categories = [],
    filters,
    stats,
}: FeedProps) {
    const { user } = useAuth();
    const [reportId, setReportId] = useState<number | null>(null);

    const tab = filters.tab || 'latest';

    const switchTab = (next: string) => {
        router.get('/', { ...filters, tab: next }, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <Layout
            hero={
                <div className="tf-hero-inner">
                    <h1>What does the world look like from where you stand?</h1>
                    <p>
                        Share a perspective. Then step into someone else's shoes and
                        branch their view from where they stand.
                    </p>
                    {stats && (
                        <div className="tf-stats">
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faShoePrints} />
                                <strong>{stats.total_perspectives}</strong>
                                <span>perspectives</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faLayerGroup} />
                                <strong>{stats.total_branches}</strong>
                                <span>branches</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faUsers} />
                                <strong>{stats.total_voices}</strong>
                                <span>voices</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faChartLine} />
                                <strong>{stats.today}</strong>
                                <span>today</span>
                            </div>
                        </div>
                    )}
                </div>
            }
        >
            <Row className="g-4">
                <Col lg={8}>
                    <div className="tf-panel">
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

                        {perspectives.data.length === 0 ? (
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
                                {perspectives.data.map((p) => (
                                    <PerspectiveCard
                                        key={p.id}
                                        perspective={p}
                                        canInteract={Boolean(user)}
                                        truncate
                                        onReport={setReportId}
                                    />
                                ))}
                            </div>
                        )}

                        <Pagination paginator={perspectives} />
                    </div>
                </Col>

                <Col lg={4}>
                    {featured.length > 0 && (
                        <div className="tf-panel tf-panel-side">
                            <h3 className="tf-side-title">
                                <FontAwesomeIcon icon={faStar} className="me-2" />
                                Featured this week
                            </h3>
                            <div className="tf-side-list">
                                {featured.map((p) => (
                                    <Link
                                        key={p.id}
                                        href={`/perspectives/${p.id}`}
                                        className="tf-side-item"
                                    >
                                        <span className="tf-avatar tf-avatar-sm">
                                            {(p.author?.name ?? '?').charAt(0).toUpperCase()}
                                        </span>
                                        <div>
                                            <strong>{p.voice ?? p.author?.name}</strong>
                                            <p>{p.body.slice(0, 90)}…</p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}

                    {categories.length > 0 && (
                        <div className="tf-panel tf-panel-side">
                            <h3 className="tf-side-title">
                                <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                                Browse categories
                            </h3>
                            <div className="tf-cat-grid">
                                {categories.map((c) => (
                                    <Link
                                        key={c.id}
                                        href={`/categories/${c.slug}`}
                                        className="tf-cat-tile"
                                        style={{ ['--cat' as any]: c.color }}
                                    >
                                        <FontAwesomeIcon icon={categoryIcon(c.icon)} />
                                        <span>{c.name}</span>
                                        {c.perspectives_count != null && (
                                            <Badge bg="light" text="dark">
                                                {c.perspectives_count}
                                            </Badge>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </Col>
            </Row>

            <ReportModal
                open={reportId !== null}
                perspectiveId={reportId}
                onClose={() => setReportId(null)}
            />
        </Layout>
    );
}
