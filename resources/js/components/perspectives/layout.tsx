// packages/truvoicer/tf-perspectives/resources/js/components/Layout.tsx
import { useState, type ReactNode } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    Container, Navbar, Nav, Dropdown, Badge, Button,
} from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faBell, faBookmark, faCompass, faHouse, faLayerGroup,
    faMagnifyingGlass, faPlus, faRightFromBracket, faUser,
    faGaugeHigh,
} from '@fortawesome/free-solid-svg-icons';
import { useAuth } from '../hooks/useAuth';
import type { SharedProps } from '../types';
import { ComposerModal } from './ComposerModal';

interface LayoutProps {
    children: ReactNode;
    /** Optional slot rendered in the hero region, under the navbar. */
    hero?: ReactNode;
}

export default function Layout({ children, hero }: LayoutProps) {
    const { user } = useAuth();
    const page = usePage<SharedProps>();
    const { url, props } = page;
    const flash = props.flash;
    const unread = props.unread_notifications ?? 0;
    const [composerOpen, setComposerOpen] = useState(false);

    const isActive = (path: string) =>
        url === path || url.startsWith(`${path}/`);

    return (
        <div className="tf-shell">
            <Navbar expand="lg" className="tf-navbar" sticky="top">
                <Container>
                    <Navbar.Brand as={Link} href="/" className="tf-brand">
                        <span className="tf-brand-mark">
                            <FontAwesomeIcon icon={faShoePrintsStub} />
                        </span>
                        <span className="tf-brand-text">
                            <strong>Other Shoes</strong>
                            <em>see it from where they stand</em>
                        </span>
                    </Navbar.Brand>

                    <Navbar.Toggle aria-controls="tf-nav" />
                    <Navbar.Collapse id="tf-nav">
                        <Nav className="me-auto">
                            <Nav.Link as={Link} href="/"
                                className={url === '/' ? 'active' : ''}>
                                <FontAwesomeIcon icon={faHouse} className="me-2" />
                                Feed
                            </Nav.Link>
                            <Nav.Link as={Link} href="/categories"
                                className={isActive('/categories') ? 'active' : ''}>
                                <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                                Categories
                            </Nav.Link>
                            <Nav.Link as={Link} href="/search"
                                className={isActive('/search') ? 'active' : ''}>
                                <FontAwesomeIcon icon={faMagnifyingGlass} className="me-2" />
                                Search
                            </Nav.Link>
                        </Nav>

                        <div className="d-flex align-items-center gap-2">
                            {user ? (
                                <>
                                    <Button
                                        variant="primary"
                                        size="sm"
                                        className="tf-btn-compose d-none d-lg-inline-flex"
                                        onClick={() => setComposerOpen(true)}
                                    >
                                        <FontAwesomeIcon icon={faPlus} className="me-2" />
                                        New perspective
                                    </Button>

                                    <Dropdown align="end">
                                        <Dropdown.Toggle
                                            as="button"
                                            className="tf-icon-btn position-relative"
                                        >
                                            <FontAwesomeIcon icon={faBell} />
                                            {unread > 0 && (
                                                <Badge
                                                    bg="danger"
                                                    pill
                                                    className="tf-badge-dot"
                                                >
                                                    {unread > 9 ? '9+' : unread}
                                                </Badge>
                                            )}
                                        </Dropdown.Toggle>
                                        <NotificationsDropdown />
                                    </Dropdown>

                                    <Dropdown align="end">
                                        <Dropdown.Toggle
                                            as="button"
                                            className="tf-avatar-btn"
                                            id="user-menu"
                                        >
                                            <span className="tf-avatar tf-avatar-sm">
                                                {user.name.charAt(0).toUpperCase()}
                                            </span>
                                        </Dropdown.Toggle>
                                        <Dropdown.Menu className="tf-dropdown">
                                            <Dropdown.Header>
                                                Signed in as <strong>{user.name}</strong>
                                            </Dropdown.Header>
                                            <Dropdown.Divider />
                                            <Dropdown.Item as={Link} href={`/u/${user.handle}`}>
                                                <FontAwesomeIcon icon={faUser} className="me-2" />
                                                My profile
                                            </Dropdown.Item>
                                            <Dropdown.Item as={Link} href="/dashboard">
                                                <FontAwesomeIcon icon={faGaugeHigh} className="me-2" />
                                                Dashboard
                                            </Dropdown.Item>
                                            <Dropdown.Item as={Link} href="/bookmarks">
                                                <FontAwesomeIcon icon={faBookmark} className="me-2" />
                                                Bookmarks
                                            </Dropdown.Item>
                                            <Dropdown.Divider />
                                            <Dropdown.Item as={Link} href="/logout" method="post">
                                                <FontAwesomeIcon
                                                    icon={faRightFromBracket}
                                                    className="me-2"
                                                />
                                                Sign out
                                            </Dropdown.Item>
                                        </Dropdown.Menu>
                                    </Dropdown>
                                </>
                            ) : (
                                <>
                                    <Nav.Link as={Link} href="/login">Sign in</Nav.Link>
                                    <Button
                                        as={Link as any}
                                        href="/register"
                                        variant="primary"
                                        size="sm"
                                        className="tf-btn-compose"
                                    >
                                        Join
                                    </Button>
                                </>
                            )}
                        </div>
                    </Navbar.Collapse>
                </Container>
            </Navbar>

            {hero && <div className="tf-hero">{hero}</div>}

            <Container className="tf-main">
                {flash?.success && (
                    <div className="alert alert-success tf-flash">{flash.success}</div>
                )}
                {flash?.error && (
                    <div className="alert alert-danger tf-flash">{flash.error}</div>
                )}
                {children}
            </Container>

            <footer className="tf-foot">
                <Container>
                    <FontAwesomeIcon icon={faCompass} className="me-2" />
                    Every perspective here is a door, not a verdict.
                </Container>
            </footer>

            <ComposerModal
                open={composerOpen}
                onClose={() => setComposerOpen(false)}
            />

            {user && (
                <Button
                    variant="primary"
                    className="tf-fab d-lg-none"
                    onClick={() => setComposerOpen(true)}
                    aria-label="New perspective"
                >
                    <FontAwesomeIcon icon={faPlus} />
                </Button>
            )}
        </div>
    );
}

// Small stub so we don't need to import faShoePrints twice
import { faShoePrints } from '@fortawesome/free-solid-svg-icons';
const faShoePrintsStub = faShoePrints;
