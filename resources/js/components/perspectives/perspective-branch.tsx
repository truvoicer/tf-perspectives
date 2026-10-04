// resources/js/Components/PerspectiveBranch.tsx
import type { CSSProperties } from 'react';
import Composer from './composer';
import type { PerspectiveNode } from '../../types/perspectives';

interface PerspectiveBranchProps {
    node: PerspectiveNode;
    branchingId: number | null;
    onOpenBranch: (id: number) => void;
    onCloseBranch: () => void;
    onEmpathize: (id: number) => void;
    canInteract: boolean;
}

function timeAgo(iso: string | null): string {
    if (!iso) return '';
    const seconds = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    const steps: Array<[number, string]> = [
        [31536000, 'y'],
        [2592000, 'mo'],
        [604800, 'w'],
        [86400, 'd'],
        [3600, 'h'],
        [60, 'm'],
    ];
    for (const [size, label] of steps) {
        if (seconds >= size) return `${Math.floor(seconds / size)}${label} ago`;
    }
    return 'just now';
}

export default function PerspectiveBranch({
    node,
    branchingId,
    onOpenBranch,
    onCloseBranch,
    onEmpathize,
    canInteract,
}: PerspectiveBranchProps) {
    const isBranching = branchingId === node.id;
    const hasChildren = node.children.length > 0;

    const label = node.voice ?? node.author?.name ?? '?';
    const initial = label.trim().charAt(0).toUpperCase();
    const depthStyle = { '--depth': node.depth } as CSSProperties;

    return (
        <div className="branch" style={depthStyle}>
            <article className="card">
                <header className="card-head">
                    <span className="avatar" aria-hidden="true">{initial}</span>
                    <div className="who">
                        <strong>{node.voice ?? node.author?.name}</strong>
                        <span className="meta">
                            {node.voice ? `${node.author?.name} · ` : ''}
                            {timeAgo(node.created_at)}
                            {node.depth > 0 &&
                                ` · ${node.depth} step${node.depth > 1 ? 's' : ''} in`}
                        </span>
                    </div>
                </header>

                <p className="card-body">{node.body}</p>

                <footer className="card-foot">
                    <button
                        type="button"
                        className={`pill ${node.has_empathized ? 'pill-on' : ''}`}
                        disabled={!canInteract}
                        onClick={() => onEmpathize(node.id)}
                        title={
                            canInteract
                                ? 'I have stood in these shoes'
                                : 'Sign in to stand in these shoes'
                        }
                    >
                        👟 {node.empathy_count}
                        <span className="sr-only">people have stood in these shoes</span>
                    </button>

                    {node.can_branch ? (
                        <button
                            type="button"
                            className={`pill ${isBranching ? 'pill-active' : ''}`}
                            disabled={!canInteract}
                            onClick={() =>
                                isBranching ? onCloseBranch() : onOpenBranch(node.id)
                            }
                            title={canInteract ? 'Put yourself in their shoes' : 'Sign in to respond'}
                        >
                            {isBranching ? 'Never mind' : 'Step into their shoes'}
                        </button>
                    ) : (
                        <span
                            className="pill pill-muted"
                            title="This branch has reached the deepest step"
                        >
                            deepest step
                        </span>
                    )}

                    {hasChildren && (
                        <span className="count">
                            {node.children.length} branched from here
                        </span>
                    )}
                </footer>
            </article>

            {(hasChildren || isBranching) && (
                <div className="children">
                    {isBranching && (
                        <Composer
                            parentId={node.id}
                            parentLabel={node.voice ?? node.author?.name ?? null}
                            onCancel={onCloseBranch}
                            onSuccess={onCloseBranch}
                        />
                    )}

                    {node.children.map((child) => (
                        <PerspectiveBranch
                            key={child.id}
                            node={child}
                            branchingId={branchingId}
                            onOpenBranch={onOpenBranch}
                            onCloseBranch={onCloseBranch}
                            onEmpathize={onEmpathize}
                            canInteract={canInteract}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
