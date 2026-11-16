import { router, usePage } from '@inertiajs/react';
import { createContext, ReactNode, useContext, useEffect, useState } from 'react';
import { Category, Paginator, Perspective } from '../types/perspectives';

export type PerspectivesProviderContextType = {
    perspectives?: Paginator<Perspective>;
    popularTags?: string[];
    filters?: { q: string; category: string; tag: string; mood: string; sort: string };
    searchResultsPagePermalink?: string;
    searchPagePermalink?: string;
    categories?: Category[];
    threadPagePermalink?: string;
    feedPagePermalink?: string;
    categoryThreadPagePermalink?: string;
    categoriesPagePermalink?: string;
    update: (data: Partial<Omit<PerspectivesProviderContextType, 'update'>>) => void;
};

const perspectivesBlockProviderContextData: PerspectivesProviderContextType = {
    update: () => {},
    perspectives: undefined,
    popularTags: undefined,
    filters: undefined,
    searchResultsPagePermalink: undefined,
    searchPagePermalink: undefined,
    categories: undefined,
    threadPagePermalink: undefined,
    feedPagePermalink: undefined,
    categoryThreadPagePermalink: undefined,
    categoriesPagePermalink: undefined,
};
const PerspectivesBlockContext = createContext<PerspectivesProviderContextType>(perspectivesBlockProviderContextData);

interface PerspectivesProviderProps extends Omit<PerspectivesProviderContextType, 'update'> {
    children?: ReactNode;
};

function PerspectivesProvider({
    children,
    perspectives,
    popularTags,
    filters,
    searchResultsPagePermalink,
    searchPagePermalink,
    categories,
    threadPagePermalink,
    feedPagePermalink,
    categoryThreadPagePermalink,
    categoriesPagePermalink
}: PerspectivesProviderProps) {
    const updateStateData = (data: Partial<PerspectivesProviderContextType>) => {
        setStateData((prevState) => {
            const newState = { ...prevState };
            (Object.keys(data) as (keyof PerspectivesProviderContextType)[]).forEach((key) => {
                if (Object.prototype.hasOwnProperty.call(perspectivesBlockProviderContextData, key)) {
                    (newState[key] as unknown) = data[key]!;
                }
            });
            return newState;
        });
    };

    const [stateData, setStateData] = useState<PerspectivesProviderContextType>({
        ...perspectivesBlockProviderContextData,
        perspectives,
        popularTags,
        filters,
        searchResultsPagePermalink,
        searchPagePermalink,
        categories,
        threadPagePermalink,
        feedPagePermalink,
        categoryThreadPagePermalink,
        categoriesPagePermalink,
        update: updateStateData,
    });

    return <PerspectivesBlockContext.Provider value={stateData}>{children}</PerspectivesBlockContext.Provider>;
}

export const usePerspectivesContext = () => {
    const context = useContext(PerspectivesBlockContext);

    // This is the key part: we check if the context is `undefined`.
    // If it is, it means we are trying to use the context outside of its provider.
    if (context === undefined) {
        throw new Error('usePerspectivesContext must be used within a PerspectivesProvider');
    }

    // If the context is not undefined, we can safely return it.
    // TypeScript now knows that the return value is `ThemeContextType`.
    return context;
};

export default PerspectivesProvider;
