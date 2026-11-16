// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/blocks/category-thread-block.tsx
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, usePage } from '@inertiajs/react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { useState } from 'react';
import { route } from 'ziggy-js';
import EmptyState from '../../../components/perspectives/empty-state';
import Pagination from '../../../components/perspectives/pagination';
import { PerspectiveCard } from '../../../components/perspectives/perspective-card';
import { categoryIcon } from '../../../lib/icons';
import PerspectivesProvider from '../../../provider/perspectives-provider';
import type { Category, Paginator, Perspective } from '../../../types/perspectives';
import { ComposerModal } from '../composer-modal';
import { ReportModal } from '../report-modal';
import { UrlBuilder } from '../../../lib/url-builder';

export default function CategoryThreadBlock() {
    const { user } = useAuth();
    const {
        category,
        perspectives,
        searchResultsPagePermalink,
        searchPagePermalink,
        threadPagePermalink,
        feedPagePermalink,
        categoryThreadPagePermalink,
        categoriesPagePermalink,
    } = usePage<{
        category: Category;
        perspectives: Paginator<Perspective>;
        searchResultsPagePermalink: string;
        searchPagePermalink: string;
        threadPagePermalink: string;
        feedPagePermalink: string;
        categoryThreadPagePermalink: string;
        categoriesPagePermalink: string;
    }>().props;
    const [reportId, setReportId] = useState<number | null>(null);

    const [editing, setEditing] = useState<Perspective | null>(null);
    const [composerOpen, setComposerOpen] = useState(false);
    return (
        <PerspectivesProvider
            searchResultsPagePermalink={searchResultsPagePermalink}
            searchPagePermalink={searchPagePermalink}
            threadPagePermalink={threadPagePermalink}
            feedPagePermalink={feedPagePermalink}
            categoryThreadPagePermalink={categoryThreadPagePermalink}
            categoriesPagePermalink={categoriesPagePermalink}
            perspectives={perspectives}
        >
            <Link href={UrlBuilder.buildCategoriesPageUrl(categoriesPagePermalink)} className="tf-back">
                <FontAwesomeIcon icon={faArrowLeft} className="me-2" />
                All categories
            </Link>

            <div className="tf-panel">
                {perspectives.data.length === 0 ? (
                    <EmptyState
                        icon={categoryIcon(category.icon)}
                        title="Nothing here yet"
                        body="Be the first to offer a perspective in this category."
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
                                onEdit={(persp) => {
                                    setComposerOpen(true);
                                    setEditing(persp);
                                }}
                            />
                        ))}
                    </div>
                )}
                <Pagination paginator={perspectives} />
            </div>
            <ComposerModal open={composerOpen} onClose={() => setComposerOpen(false)} editing={editing} />
            <ReportModal open={reportId !== null} perspectiveId={reportId} onClose={() => setReportId(null)} />
        </PerspectivesProvider>
    );
}
