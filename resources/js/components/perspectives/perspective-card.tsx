// packages/truvoicer/tf-perspectives/resources/js/components/PerspectiveCard.tsx
import { Link, router } from '@inertiajs/react';
import { Badge, Dropdown } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faBookmark as faBookmarkSolid, faFlag, faEllipsisVertical,
    faLayerGroup, faEyeSlash, faArrowRight, faComment,
} from '@fortawesome/free-solid-svg-icons';
import { faBookmark as faBookmarkRegular } from '@fortawesome/free-regular-svg-icons';
import { ReactionBar } from './ReactionBar';
import { ShareMenu } from './ShareMenu';
import { categoryIcon } from '../lib/icons';
import { initials, timeAgo, excerpt } from '../lib/format';
import type { Perspective } from '../types';

interface PerspectiveCardProps {
    perspective: Perspective;
    canInteract: boolean;
    onBranch?: (id: number) => void;
    onReport?: (id: number) => void;
    /** Show the "step into their shoes" button (thread view only). */
    showBranchAction?: boolean;
    /** Truncate body (used in list views). */
    truncate?: boolean;
}

export function PerspectiveCard({
    perspective,
    canInteract,
    onBranch,
    onReport,
    showBranchAction = false,
    truncate = false,
}: PerspectiveCardProps) {
    const label = perspective.voice ?? perspective.author?.name ?? 'Anonymous';
    const url = typeof window !== 'undefined'
        ? `${window.location.origin}/perspectives/${perspective.id}`
        : `/perspectives/${perspective.id}`;

    const toggleBookmark = () => {
        router.post(
            `/perspectives/${perspective.id}/bookmark`,
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <article className="tf-card">
            <header className="tf-card-head">
                <span className="tf-avatar">
                    {perspective.is_anonymous && !perspective.author
                        ? '?'
                        : initials(perspective.author?.name ?? label)}
                </span>
                <div className="tf-card-who">
                    <div className="tf-card-line">
                        <strong>{label}</strong>
                        {perspective.is_anonymous && (
                            <span className="tf-anon-tag" title="Posted anonymously">
                                <FontAwesomeIcon icon={faEyeSlash} />
                            </span>
                        )}
                        {perspective.voice && perspective.author && (
                            <span className="tf-muted">· {perspective.author.name}</span>
                        )}
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
                            href={`/categories/${perspective.category.slug}`}
                            className="tf-cat-pill"
                            style={{ ['--cat' as any]: perspective.category.color }}
                        >
                            <FontAwesomeIcon
                                icon={categoryIcon(perspective.category.icon)}
                                className="me-1"
                            />
                            {perspective.category.name}
                        </Link>
                    )}

                    <ShareMenu url={url} title={excerpt(perspective.body, 80)} />

                    <Dropdown align="end">
                        <Dropdown.Toggle as="button" className="tf-icon-btn-sm">
                            <FontAwesomeIcon icon={faEllipsisVertical} />
                        </Dropdown.Toggle>
                        <Dropdown.Menu className="tf-dropdown">
                            <Dropdown.Item onClick={toggleBookmark}>
                                <FontAwesomeIcon
                                    icon={
                                        perspective.is_bookmarked
                                            ? faBookmarkSolid
                                            : faBookmarkRegular
                                    }
                                    className="me-2"
                                />
                                {perspective.is_bookmarked ? 'Remove bookmark' : 'Bookmark'}
                            </Dropdown.Item>
                            <Dropdown.Item onClick={() => onReport?.(perspective.id)}>
                                <FontAwesomeIcon icon={faFlag} className="me-2" />
                                Report
                            </Dropdown.Item>
                        </Dropdown.Menu>
                    </Dropdown>
                </div>
            </header>

            <div className="tf-card-body">
                {truncate ? excerpt(perspective.body, 260) : perspective.body}
            </div>

            {perspective.tags.length > 0 && (
                <div className="tf-card-tags">
                    {perspective.tags.map((tag) => (
                        <Link
                            key={tag}
                            href={`/search?tag=${encodeURIComponent(tag)}`}
                            className="tf-tag-link"
                        >
                            #{tag}
                        </Link>
                    ))}
                </div>
            )}

            <footer className="tf-card-foot">
                <ReactionBar
                    perspectiveId={perspective.id}
                    summary={perspective.reactions}
                    disabled={!canInteract}
                />

                <div className="tf-card-foot-right">
                    {perspective.children_count > 0 && (
                        <Link
                            href={`/perspectives/${perspective.id}`}
                            className="tf-count"
                        >
                            <FontAwesomeIcon icon={faComment} className="me-1" />
                            {perspective.children_count}
                        </Link>
                    )}
                    {showBranchAction && perspective.can_branch && (
                        <button
                            type="button"
                            className="tf-pill-action"
                            disabled={!canInteract}
                            onClick={() => onBranch?.(perspective.id)}
                        >
                            <FontAwesomeIcon icon={faLayerGroup} className="me-1" />
                            Step into their shoes
                        </button>
                    )}
                    {!showBranchAction && (
                        <Link
                            href={`/perspectives/${perspective.id}`}
                            className="tf-read-more"
                        >
                            Step in <FontAwesomeIcon icon={faArrowRight} className="ms-1" />
                        </Link>
                    )}
                </div>
            </footer>
        </article>
    );
}
