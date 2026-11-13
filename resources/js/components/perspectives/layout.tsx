// packages/truvoicer/tf-perspectives/resources/js/components/Layout.tsx
import {
    faBell,
    faBookmark,
    faCompass,
    faGaugeHigh,
    faHouse,
    faLayerGroup,
    faMagnifyingGlass,
    faPlus,
    faRightFromBracket,
    faShoePrints,
    faUser,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, usePage } from '@inertiajs/react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { useState, type ReactNode } from 'react';
import { Badge, Button, Container, Dropdown, Nav, Navbar } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { SharedProps } from '../../types/perspectives';
import { ComposerModal } from './composer-modal';
import { NotificationsDropdown } from './notifications-dropdown';

interface LayoutProps {
    children: ReactNode;
    /** Optional slot rendered in the hero region, under the navbar. */
    hero?: ReactNode;
}

/** Floating-ui strategy that positions dropdowns against the viewport. */
const FIXED_POPPER = { strategy: 'fixed' as const };

export default function Layout({ children, hero }: LayoutProps) {
    const { user } = useAuth();
    const page = usePage<SharedProps>();
    const { url, props } = page;

    const flash = props.flash;
    const unread = props.unread_notifications ?? 0;

    const [composerOpen, setComposerOpen] = useState(false);

    const isActive = (path: string) => url === path || url.startsWith(`${path}/`);

    // `user` comes back wrapped in a data envelope from the shared props.
    // Keep a couple of locals so the JSX below stays readable.
    const me = user?.data ?? null;
    const initial = me?.full_name?.charAt(0).toUpperCase() ?? '';

    return (
        <div className="tf-shell">
            <Navbar expand="lg" className="tf-navbar" sticky="top">
                <Container>
                    <Navbar.Brand as={Link} href={route('perspectives.index')} className="tf-brand order-1">
                        <span className="tf-brand-mark">
                            <FontAwesomeIcon icon={faShoePrints} />
                        </span>
                        <span className="tf-brand-text">
                            <strong>Other Shoes</strong>
                            <em>see it from where they stand</em>
                        </span>
                    </Navbar.Brand>

                    <div className="d-flex align-items-center gap-2 order-2 order-lg-4">
                        {me ? (
                            <Button
                                variant="primary"
                                size="sm"
                                className="tf-btn-compose d-none d-lg-inline-flex"
                                onClick={() => setComposerOpen(true)}
                            >
                                <FontAwesomeIcon icon={faPlus} className="me-2" />
                                New perspective
                            </Button>
                        ) : (
                            <>
                                <Nav.Link as={Link} href={route('perspectives.login')}>
                                    Sign in
                                </Nav.Link>
                                <Link href={route('perspectives.register')} className="btn btn-primary btn-sm tf-btn-compose">
                                    Join
                                </Link>
                            </>
                        )}
                    </div>
                    {/*
                     * Bell + avatar live OUTSIDE the collapse so they're
                     * always reachable on mobile and never inherit the
                     * animated collapse's containing block.
                     */}
                    {me && (
                        <div className="tf-nav-actions order-3 order-lg-3 d-flex align-items-center gap-2">
                            <Dropdown align="end" popperConfig={FIXED_POPPER}>
                                <Dropdown.Toggle as="button" className="tf-icon-btn position-relative" aria-label="Notifications">
                                    <FontAwesomeIcon icon={faBell} />
                                    {unread > 0 && (
                                        <Badge bg="danger" pill className="tf-badge-dot">
                                            {unread > 9 ? '9+' : unread}
                                        </Badge>
                                    )}
                                </Dropdown.Toggle>
                                <NotificationsDropdown />
                            </Dropdown>

                            <Dropdown align="end" popperConfig={FIXED_POPPER}>
                                <Dropdown.Toggle as="button" className="tf-avatar-btn" id="user-menu" aria-label="Account menu">
                                    <span className="tf-avatar tf-avatar-sm">{initial}</span>
                                </Dropdown.Toggle>
                                <Dropdown.Menu className="tf-dropdown">
                                    <Dropdown.Header>
                                        Signed in as <strong>{me.full_name}</strong>
                                    </Dropdown.Header>
                                    <Dropdown.Divider />
                                    <Dropdown.Item as={Link} href={route('perspectives.profile', { user: me.id })}>
                                        <FontAwesomeIcon icon={faUser} className="me-2" />
                                        My profile
                                    </Dropdown.Item>
                                    <Dropdown.Item as={Link} href={route('perspectives.dashboard')}>
                                        <FontAwesomeIcon icon={faGaugeHigh} className="me-2" />
                                        Dashboard
                                    </Dropdown.Item>
                                    <Dropdown.Item as={Link} href={route('perspectives.bookmark.index')}>
                                        <FontAwesomeIcon icon={faBookmark} className="me-2" />
                                        Bookmarks
                                    </Dropdown.Item>
                                    <Dropdown.Divider />
                                    <Dropdown.Item as={Link} href={route('logout')} method="post" data={{ auth_context: 'perspectives' }}>
                                        <FontAwesomeIcon icon={faRightFromBracket} className="me-2" />
                                        Sign out
                                    </Dropdown.Item>
                                </Dropdown.Menu>
                            </Dropdown>
                        </div>
                    )}

                    <Navbar.Toggle aria-controls="tf-nav" className="order-3" />

                    <Navbar.Collapse id="tf-nav" className="order-3 order-lg-2">
                        <div className="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
                            <Nav className="me-auto">
                                <Nav.Link
                                    as={Link}
                                    href={route('perspectives.index')}
                                    className={isActive(route('perspectives.index')) ? 'active' : ''}
                                >
                                    <FontAwesomeIcon icon={faHouse} className="me-2" />
                                    Feed
                                </Nav.Link>
                                <Nav.Link
                                    as={Link}
                                    href={route('perspectives.categories')}
                                    className={isActive(route('perspectives.categories')) ? 'active' : ''}
                                >
                                    <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                                    Categories
                                </Nav.Link>
                                <Nav.Link
                                    as={Link}
                                    href={route('perspectives.search')}
                                    className={isActive(route('perspectives.search')) ? 'active' : ''}
                                >
                                    <FontAwesomeIcon icon={faMagnifyingGlass} className="me-2" />
                                    Search
                                </Nav.Link>
                            </Nav>

                        </div>
                    </Navbar.Collapse>
                </Container>
            </Navbar>

            {hero && <div className="tf-hero">{hero}</div>}

            <Container className="tf-main">
                {flash?.success && <div className="alert alert-success tf-flash">{flash.success}</div>}
                {flash?.error && <div className="alert alert-danger tf-flash">{flash.error}</div>}
                {children}
            </Container>

            <footer className="tf-foot">
                <Container>
                    <FontAwesomeIcon icon={faCompass} className="me-2" />
                    Every perspective here is a door, not a verdict.
                </Container>
            </footer>

            <ComposerModal open={composerOpen} onClose={() => setComposerOpen(false)} />


            {me && (
                <Button variant="primary" className="tf-fab d-lg-none" onClick={() => setComposerOpen(true)} aria-label="New perspective">
                    <FontAwesomeIcon icon={faPlus} />
                </Button>
            )}
        </div>
    );
}
