// packages/truvoicer/tf-perspectives/resources/js/pages/bookmarks.tsx
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBookmark } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/perspectives/layout';
import { PerspectiveCard } from '../components/perspectives/perspective-card';
import Pagination from '../components/perspectives/pagination';
import EmptyState from '../components/perspectives/empty-state';
import type { Paginator, Perspective } from '../types/perspectives';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';

interface BookmarksProps {
    perspectives: Paginator<Perspective>;
}

export default function Bookmarks({ perspectives }: BookmarksProps) {
    const { user } = useAuth();

    return (
        <Layout>
            <h1 className="tf-page-title mb-4">
                <FontAwesomeIcon icon={faBookmark} className="me-2" />
                Bookmarks
            </h1>

            <div className="tf-panel">
                {perspectives.data.length === 0 ? (
                    <EmptyState
                        icon={faBookmark}
                        title="No bookmarks yet"
                        body="Save perspectives to revisit their branches later."
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
        </Layout>
    );
}
