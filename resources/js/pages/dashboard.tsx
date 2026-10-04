// packages/truvoicer/tf-perspectives/resources/js/pages/dashboard.tsx
import { Link } from '@inertiajs/react';
import { Row, Col, Card, Badge } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faShoePrints, faLayerGroup, faHeart, faBookmark,
    faChartLine, faArrowTrendUp, faPenFancy,
} from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import EmptyState from '../components/EmptyState';
import { timeAgo } from '../lib/format';
import type { Perspective } from '../types';

interface DashboardProps {
    stats: {
        perspectives: number;
        branches: number;
        reactions_received: number;
        bookmarks: number;
        views_week: number;
        top_perspective?: Perspective | null;
    };
    recent_activity: Array<{
        id: number;
        type: 'branch' | 'reaction' | 'follow';
        message: string;
        created_at: string;
        perspective_id: number;
    }>;
}

export default function Dashboard({ stats, recent_activity }: DashboardProps) {
    return (
        <Layout>
            <h1 className="tf-page-title mb-4">Your dashboard</h1>

            <Row className="g-3 mb-4">
                <Col md={6} lg={3}>
                    <Card className="tf-stat-card">
                        <Card.Body>
                            <FontAwesomeIcon icon={faShoePrints} className="tf-stat-icon" />
                            <div className="tf-stat-value">{stats.perspectives}</div>
                            <div className="tf-stat-label">Perspectives offered</div>
                        </Card.Body>
                    </Card>
                </Col>
                <Col md={6} lg={3}>
                    <Card className="tf-stat-card">
                        <Card.Body>
                            <FontAwesomeIcon icon={faLayerGroup} className="tf-stat-icon" />
                            <div className="tf-stat-value">{stats.branches}</div>
                            <div className="tf-stat-label">Branches from yours</div>
                        </Card.Body>
                    </Card>
                </Col>
                <Col md={6} lg={3}>
                    <Card className="tf-stat-card">
                        <Card.Body>
                            <FontAwesomeIcon icon={faHeart} className="tf-stat-icon" />
                            <div className="tf-stat-value">{stats.reactions_received}</div>
                            <div className="tf-stat-label">Reactions received</div>
                        </Card.Body>
                    </Card>
                </Col>
                <Col md={6} lg={3}>
                    <Card className="tf-stat-card">
                        <Card.Body>
                            <FontAwesomeIcon icon={faChartLine} className="tf-stat-icon" />
                            <div className="tf-stat-value">{stats.views_week}</div>
                            <div className="tf-stat-label">Views this week</div>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>

            <Row className="g-4">
                <Col lg={7}>
                    <div className="tf-panel">
                        <h2 className="tf-side-title">
                            <FontAwesomeIcon icon={faArrowTrendUp} className="me-2" />
                            Recent activity
                        </h2>

                        {recent_activity.length === 0 ? (
                            <EmptyState
                                icon={faPenFancy}
                                title="Nothing yet"
                                body="Start posting perspectives to see activity here."
                            />
                        ) : (
                            <ul className="tf-activity">
                                {recent_activity.map((a) => (
                                    <li key={a.id} className="tf-activity-item">
                                        <Link href={`/perspectives/${a.perspective_id}`}>
                                            {a.message}
                                        </Link>
                                        <small className="text-muted">
                                            {timeAgo(a.created_at)}
                                        </small>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </Col>

                <Col lg={5}>
                    {stats.top_perspective ? (
                        <div className="tf-panel">
                            <h2 className="tf-side-title">
                                <FontAwesomeIcon icon={faChartLine} className="me-2" />
                                Your most-reacted perspective
                            </h2>
                            <Link
                                href={`/perspectives/${stats.top_perspective.id}`}
                                className="tf-highlight"
                            >
                                <strong>{stats.top_perspective.voice}</strong>
                                <p>{stats.top_perspective.body.slice(0, 180)}…</p>
                                <Badge bg="primary">
                                    {stats.top_perspective.reactions.counts.empathy} 👟
                                </Badge>
                            </Link>
                        </div>
                    ) : (
                        <div className="tf-panel">
                            <h2 className="tf-side-title">Start here</h2>
                            <p className="text-muted">
                                Offer your first perspective to see how others respond.
                            </p>
                        </div>
                    )}
                </Col>
            </Row>
        </Layout>
    );
}
