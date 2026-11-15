// packages/truvoicer/tf-perspectives/resources/js/components/PerspectiveCard.tsx
import { faBookmark as faBookmarkRegular } from '@fortawesome/free-regular-svg-icons';
import {
    faArrowRight,
    faBookmark as faBookmarkSolid,
    faComment,
    faEllipsisVertical,
    faEyeSlash,
    faFlag,
    faLayerGroup,
    faPenToSquare,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Dropdown, Offcanvas } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { excerpt, initials, timeAgo } from '../../lib/format';
import { categoryIcon } from '../../lib/icons';
import type { Perspective } from '../../types/perspectives';
import { PerspectiveShoes } from './perspective-shoes';
import { ReactionBar } from './reaction-bar';
import { ShareMenu } from './share-menu';
import { UrlBuilder } from '../../lib/url-builder';

interface PerspectiveCardProps {
    perspective: Perspective;
    canInteract: boolean;
    onBranch?: (id: number) => void;
    onReport?: (id: number) => void;
    /** Show the "step into their shoes" button (thread view only). */
    showBranchAction?: boolean;
    /** Truncate body (used in list views). */
    truncate?: boolean;
    onEdit?: (perspective: Perspective) => void;
    threadPagePermalink?: string;
    feedPagePermalink?: string;
    categoryThreadPagePermalink?: string;
    categoriesPagePermalink?: string;
    searchPagePermalink?: string;
    searchResultsPagePermalink?: string;
}

export function PerspectiveCard({
    perspective,
    canInteract,
    onBranch,
    onReport,
    showBranchAction = false,
    truncate = false,
    onEdit,
    threadPagePermalink,
    feedPagePermalink,
    categoryThreadPagePermalink,
    categoriesPagePermalink,
    searchPagePermalink,
    searchResultsPagePermalink,
}: PerspectiveCardProps) {
    const label = perspective.voice ?? perspective.author?.full_name ?? 'Anonymous';
    const url = route('perspectives.show', { perspective: perspective.id });

    const [shoesOpen, setShoesOpen] = useState(false);
    const toggleBookmark = () => {
        router.post(route('perspectives.bookmark.toggle', { perspective: perspective.id }), {}, { preserveScroll: true, preserveState: true });
    };
    console.log({threadPagePermalink,
    feedPagePermalink,
    categoryThreadPagePermalink,
    categoriesPagePermalink,
    searchPagePermalink,
    searchResultsPagePermalink,})
    return (
        <>
            <article className="tf-card">
                <header className="tf-card-head">
                    <span className="tf-avatar">
                        {perspective.is_anonymous && !perspective.author ? '?' : initials(perspective.author?.full_name ?? label)}
                    </span>
                    <div className="tf-card-who">
                        <div className="tf-card-line">
                            <strong>{label}</strong>
                            {perspective.is_anonymous && (
                                <span className="tf-anon-tag" title="Posted anonymously">
                                    <FontAwesomeIcon icon={faEyeSlash} />
                                </span>
                            )}
                            {perspective.voice && perspective.author && <span className="tf-muted">· {perspective.author.full_name}</span>}
                        </div>
                        <div className="tf-card-meta">
                            <span>{timeAgo(perspective.created_at)}</span>
                            {perspective.depth > 0 && (
                                <>
                                    <span className="tf-dot">·</span>
                                    <span>
                                        {perspective.depth} step
                                        {perspective.depth > 1 ? 's' : ''} in
                                    </span>
                                </>
                            )}
                        </div>
                    </div>

                    <div className="tf-card-tools">
                        {perspective.category && (
                            <Link
                                href={UrlBuilder.buildCategoryThreadPageUrl(categoryThreadPagePermalink, perspective.category.slug)}
                                className="tf-cat-pill"
                                style={{ ['--cat' as any]: perspective.category.color }}
                            >
                                <FontAwesomeIcon icon={categoryIcon(perspective.category.icon)} className="me-1" />
                                {perspective.category.name}
                            </Link>
                        )}

                        <ShareMenu url={url} title={excerpt(perspective.body, 80)} />

                        <Dropdown align="end">
                            <Dropdown.Toggle as="button" className="tf-icon-btn-sm">
                                <FontAwesomeIcon icon={faEllipsisVertical} />
                            </Dropdown.Toggle>
                            <Dropdown.Menu className="tf-dropdown">
                                {perspective.is_owner && onEdit && (
                                    <Dropdown.Item onClick={() => onEdit(perspective)}>
                                        <FontAwesomeIcon icon={faPenToSquare} className="me-2" />
                                        Edit
                                    </Dropdown.Item>
                                )}
                                <Dropdown.Item onClick={toggleBookmark}>
                                    <FontAwesomeIcon icon={perspective.is_bookmarked ? faBookmarkSolid : faBookmarkRegular} className="me-2" />
                                    {perspective.is_bookmarked ? 'Remove bookmark' : 'Bookmark'}
                                </Dropdown.Item>
                                {!perspective.is_owner && (
                                    <Dropdown.Item onClick={() => onReport?.(perspective.id)}>
                                        <FontAwesomeIcon icon={faFlag} className="me-2" />
                                        Report
                                    </Dropdown.Item>
                                )}
                            </Dropdown.Menu>
                        </Dropdown>
                    </div>
                </header>

                <div className="tf-card-body">{truncate ? excerpt(perspective.body, 260) : perspective.body}</div>

                {perspective.tags.length > 0 && (
                    <div className="tf-card-tags">
                        {perspective.tags.map((tag) => (
                            <Link key={tag} href={`${UrlBuilder.buildSearchPageUrl(searchPagePermalink)}?tag=${encodeURIComponent(tag)}`} className="tf-tag-link">
                                #{tag}
                            </Link>
                        ))}
                    </div>
                )}

                <footer className="tf-card-foot">
                    <ReactionBar perspectiveId={perspective.id} summary={perspective.reactions} disabled={!canInteract} />

                    <div className="tf-card-foot-right">
                        {perspective.children_count > 0 && (
                            <Link href={UrlBuilder.buildThreadPageUrl(threadPagePermalink, perspective.id)} className="tf-count">
                                <FontAwesomeIcon icon={faComment} className="me-1" />
                                {perspective.children_count}
                            </Link>
                        )}
                        {showBranchAction && perspective.can_branch && (
                            <button type="button" className="tf-pill-action" disabled={!canInteract} onClick={() => setShoesOpen(true)}>
                                <FontAwesomeIcon icon={faLayerGroup} className="me-1" />
                                Step into their shoes
                            </button>
                        )}
                        {!showBranchAction && (
                            <Link href={UrlBuilder.buildThreadPageUrl(threadPagePermalink, perspective.id)} className="tf-read-more">
                                Step in <FontAwesomeIcon icon={faArrowRight} className="ms-1" />
                            </Link>
                        )}
                    </div>
                </footer>
            </article>

            {/* The Drawer */}
            <Offcanvas show={shoesOpen} onHide={() => setShoesOpen(false)} placement="end" className="tf-offcanvas">
                <Offcanvas.Body className="p-0">
                    <PerspectiveShoes
                        perspective={perspective}
                        shoes={perspective.shoes} // Assuming these are eager loaded or passed down
                        onClose={() => setShoesOpen(false)}
                    />
                </Offcanvas.Body>
            </Offcanvas>
        </>
    );
}
