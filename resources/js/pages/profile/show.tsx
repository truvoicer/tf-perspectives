// packages/truvoicer/tf-perspectives/resources/js/pages/profile/show.tsx
import { router } from '@inertiajs/react';
import { Row, Col, Button, Tab, Nav, Badge } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faUserPlus, faUserCheck, faShoePrints, faLayerGroup,
    faBookmark, faCalendarDays, faLocationDot,
} from '@fortawesome/free-solid-svg-icons';
import Layout from '../../components/Layout';
import { PerspectiveCard } from '../../components/PerspectiveCard';
import Pagination from '../../components/Pagination';
import EmptyState from '../../components/EmptyState';
import { useAuth } from '../../hooks/useAuth';
import { initials } from '../../lib/format';
import type { Paginator, Perspective, User } from '../../types';

interface ProfileShowProps {
    profile: User;
    perspectives: Paginator<Perspective>;
    bookmarks?: Paginator<Perspective>;
    tab: 'perspectives' | 'bookmarks' | 'branches';
    stats: {
        perspectives: number;
        branches: number;
        empathy_received: number;
        followers: number;
        following: number;
    };
}

export default function ProfileShow({
    profile,
    perspectives,
    bookmarks,
    tab,
    stats,
}: ProfileShowProps) {
    const { user } = useAuth();
    const isSelf = user?.id === profile.id;

    const switchTab = (next: string) => {
        router.get(`/u/${profile.handle}`, { tab: next }, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const toggleFollow = () => {
        router.post(`/u/${profile.handle}/follow`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <Layout>
            <div className="tf-profile">
                <div className="tf-profile-head">
                    <div className="tf-profile-avatar">
                        {initials(profile.name)}
                    </div>
                    <div className="tf-profile-info">
                        <h1>{profile.name}</h1>
                        <div className="tf-profile-meta">
                            <span>@{profile.handle}</span>
                            <span className="tf-dot">·</span>
                            <span>
                                <FontAwesomeIcon icon={faCalendarDays} className="me-1" />
                                Joined recently
                            </span>
                        </div>
                        {profile.bio && <p className="tf-profile-bio">{profile.bio}</p>}

                        <div className="tf-profile-stats">
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faShoePrints} />
                                <strong>{stats.perspectives}</strong>
                                <span>perspectives</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faLayerGroup} />
                                <strong>{stats.branches}</strong>
                                <span>branches</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faUserCheck} />
                                <strong>{stats.followers}</strong>
                                <span>followers</span>
                            </div>
                            <div className="tf-stat">
                                <FontAwesomeIcon icon={faUserPlus} />
                                <strong>{stats.following}</strong>
                                <span>following</span>
                            </div>
                        </div>
                    </div>

                    <div className="tf-profile-actions">
                        {!isSelf && (
                            <Button
                                variant={profile.is_following ? 'outline-secondary' : 'primary'}
                                onClick={toggleFollow}
                            >
                                <FontAwesomeIcon
                                    icon={profile.is_following ? faUserCheck : faUserPlus}
                                    className="me-2"
                                />
                                {profile.is_following ? 'Following' : 'Follow'}
                            </Button>
                        )}
                    </div>
                </div>

                <div className="tf-panel">
                    <Tab.Container activeKey={tab} onSelect={(k) => k && switchTab(k)}>
                        <Nav variant="pills" className="tf-tabs mb-3">
                            <Nav.Item>
                                <Nav.Link eventKey="perspectives">
                                    <FontAwesomeIcon icon={faShoePrints} className="me-2" />
                                    Perspectives
                                </Nav.Link>
                            </Nav.Item>
                            <Nav.Item>
                                <Nav.Link eventKey="branches">
                                    <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                                    Branches
                                </Nav.Link>
                            </Nav.Item>
                            {isSelf && (
                                <Nav.Item>
                                    <Nav.Link eventKey="bookmarks">
                                        <FontAwesomeIcon icon={faBookmark} className="me-2" />
                                        Bookmarks
                                    </Nav.Link>
                                </Nav.Item>
                            )}
                        </Nav>
                    </Tab.Container>

                    {perspectives.data.length === 0 ? (
                        <EmptyState
                            icon={faShoePrints}
                            title={
                                tab === 'bookmarks'
                                    ? 'No bookmarks yet'
                                    : 'No perspectives yet'
                            }
                            body={
                                tab === 'bookmarks'
                                    ? 'Save perspectives to revisit them from here.'
                                    : undefined
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
                                />
                            ))}
                        </div>
                    )}

                    <Pagination paginator={perspectives} />
                </div>
            </div>
        </Layout>
    );
}
