// packages/truvoicer/tf-perspectives/resources/js/pages/thread.tsx
import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft, faShoePrints } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import { PerspectiveCard } from '../components/PerspectiveCard';
import { Composer } from '../components/Composer';
import { ReportModal } from '../components/ReportModal';
import { useAuth } from '../hooks/useAuth';
import type { Category, Perspective, PerspectiveNode } from '../types';

interface ThreadProps {
    rootId: number;
    maxDepth: number;
    perspectives: Perspective[];
    categories?: Category[];
}

function buildTree(nodes: Perspective[]): PerspectiveNode | null {
    const map = new Map<number, PerspectiveNode>(
        nodes.map((n) => [n.id, { ...n, children: [] }]),
    );
    let root: PerspectiveNode | null = null;
    map.forEach((node) => {
        if (node.parent_id && map.has(node.parent_id)) {
            map.get(node.parent_id)!.children.push(node);
        } else if (!node.parent_id) {
            root = node;
        }
    });
    return root;
}

function Branch({
    node,
    branchingId,
    setBranchingId,
    canInteract,
    onReport,
    categories,
}: {
    node: PerspectiveNode;
    branchingId: number | null;
    setBranchingId: (id: number | null) => void;
    canInteract: boolean;
    onReport: (id: number) => void;
    categories: Category[];
}) {
    const isBranching = branchingId === node.id;

    return (
        <div className="tf-branch" style={{ ['--depth' as any]: node.depth }}>
            <PerspectiveCard
                perspective={node}
                canInteract={canInteract}
                onBranch={setBranchingId}
                onReport={onReport}
                showBranchAction
            />

            {(node.children.length > 0 || isBranching) && (
                <div className="tf-children">
                    {isBranching && (
                        <div className="tf-branch-composer">
                            <Composer
                                parentId={node.id}
                                parentLabel={node.voice ?? node.author?.name ?? null}
                                categories={categories}
                                onCancel={() => setBranchingId(null)}
                                onSuccess={() => setBranchingId(null)}
                                compact
                            />
                        </div>
                    )}
                    {node.children.map((child) => (
                        <Branch
                            key={child.id}
                            node={child}
                            branchingId={branchingId}
                            setBranchingId={setBranchingId}
                            canInteract={canInteract}
                            onReport={onReport}
                            categories={categories}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

export default function Thread({ perspectives, categories = [] }: ThreadProps) {
    const { user } = useAuth();
    const [branchingId, setBranchingId] = useState<number | null>(null);
    const [reportId, setReportId] = useState<number | null>(null);

    const tree = useMemo(() => buildTree(perspectives), [perspectives]);

    if (!tree) {
        return (
            <Layout>
                <div className="tf-panel">
                    <Link href="/" className="tf-back">
                        <FontAwesomeIcon icon={faArrowLeft} className="me-2" />
                        All perspectives
                    </Link>
                    <p className="text-muted">This perspective has drifted away.</p>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <Link href="/" className="tf-back">
                <FontAwesomeIcon icon={faArrowLeft} className="me-2" />
                All perspectives
            </Link>

            <div className="tf-thread">
                <div className="tf-thread-intro">
                    <FontAwesomeIcon icon={faShoePrints} className="tf-thread-icon" />
                    <span>A perspective and every pair of shoes it has led to.</span>
                </div>

                <Branch
                    node={tree}
                    branchingId={branchingId}
                    setBranchingId={setBranchingId}
                    canInteract={Boolean(user)}
                    onReport={setReportId}
                    categories={categories}
                />
            </div>

            <ReportModal
                open={reportId !== null}
                perspectiveId={reportId}
                onClose={() => setReportId(null)}
            />
        </Layout>
    );
}
