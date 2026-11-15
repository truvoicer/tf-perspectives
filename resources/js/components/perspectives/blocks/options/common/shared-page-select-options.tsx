import { usePage } from '@inertiajs/react';
import {
    BlockOptionsSharedProps,
    Button,
    LoadingSpinner,
    ManagePages,
    ManagePagesProps,
    useAuthContext,
    useToastContext,
} from '@truvoicer/tru-fetcher-core-ui';
import { Page, Pagination } from '@truvoicer/tru-fetcher-core-ui/types';
import { useEffect, useState } from 'react';

import { AppMiddleware, Middleware } from '@truvoicer/tru-fetcher-core-ui/lib';
import { Modal } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { SharedPageSelectOptionsBlock, ThreadOptionsBlock } from '../../../../../types/page-block';

export interface BlockThreadOptionsProps extends BlockOptionsSharedProps {}
function SharedPageSelectOptions({ block, columnBlockDeleteFileRequest, columnBlockFileStoreRequest }: BlockThreadOptionsProps) {
    const [showFeedPageSelectModal, setShowFeedPageSelectModal] = useState(false);
    const [selectedFeedPage, setSelectedFeedPage] = useState<Page | null>(null);
    const [feedPageToSave, setFeedPageToSave] = useState<Page | null>(null);

    const [showCategoriesPageSelectModal, setShowCategoriesPageSelectModal] = useState(false);
    const [selectedCategoriesPage, setSelectedCategoriesPage] = useState<Page | null>(null);
    const [categoriesPageToSave, setCategoriesPageToSave] = useState<Page | null>(null);

    const [showCategoryThreadPageSelectModal, setShowCategoryThreadPageSelectModal] = useState(false);
    const [selectedCategoryThreadPage, setSelectedCategoryThreadPage] = useState<Page | null>(null);
    const [categoryThreadPageToSave, setCategoryThreadPageToSave] = useState<Page | null>(null);

    const [showThreadPageSelectModal, setShowThreadPageSelectModal] = useState(false);
    const [selectedThreadPage, setSelectedThreadPage] = useState<Page | null>(null);
    const [threadPageToSave, setThreadPageToSave] = useState<Page | null>(null);

    const [showSearchPageSelectModal, setShowSearchPageSelectModal] = useState(false);
    const [selectedSearchPage, setSelectedSearchPage] = useState<Page | null>(null);
    const [searchPageToSave, setSearchPageToSave] = useState<Page | null>(null);

    const [showSearchResultsPageSelectModal, setShowSearchResultsPageSelectModal] = useState(false);
    const [selectedSearchResultsPage, setSelectedSearchResultsPage] = useState<Page | null>(null);
    const [searchResultsPageToSave, setSearchResultsPageToSave] = useState<Page | null>(null);

    const [selectedPageType, setSelectedPageType] = useState<'feed' | 'categories' | 'category_thread' | 'thread' | 'search' | 'search_results' | null>(null);

    const [pages, setPages] = useState<Pagination<Page> | null>(null);

    const data = block?.data as SharedPageSelectOptionsBlock;
    const schema = block?.schema;
    const errors = block?.errors;

    const auth = useAuthContext();
    const page = usePage();

    const toastProvider = useToastContext();
    const middleware = new AppMiddleware(page);
    middleware.setToastProvider(toastProvider).setAuthProvider(auth);

    async function fetchData({ query }: { query?: { page?: number } }) {
        try {
            const response = await middleware.makeRequest({
                method: Middleware.REQUEST.GET,
                url: route('admin.page.data.index'),
                protectedReq: true,
                query: typeof query === 'object' && query !== null ? query : {},
            });
            if (response) {
                setPages(response);
            } else {
                console.warn(`No data found for menu items.`);
            }
        } catch (error) {
            console.error('Error fetching menu items:', error);
            throw error;
        }
    }
    function customizeIdsRowData(item: Page) {
        switch (selectedPageType) {
            case 'feed':
                if (!selectedFeedPage) {
                    return item;
                }
                const cloneItem = { ...item };
                cloneItem['selected'] = cloneItem['id'] === selectedFeedPage?.id;
                return cloneItem;
            case 'categories':
                if (!selectedCategoriesPage) {
                    return item;
                }
                const cloneItemCategories = { ...item };
                cloneItemCategories['selected'] = cloneItemCategories['id'] === selectedCategoriesPage?.id;
                return cloneItemCategories;
            case 'category_thread':
                if (!selectedCategoryThreadPage) {
                    return item;
                }
                const cloneItemCategoryThread = { ...item };
                cloneItemCategoryThread['selected'] = cloneItemCategoryThread['id'] === selectedCategoryThreadPage?.id;
                return cloneItemCategoryThread;
            case 'thread':
                if (!selectedThreadPage) {
                    return item;
                }
                const cloneItemThread = { ...item };
                cloneItemThread['selected'] = cloneItemThread['id'] === selectedThreadPage?.id;
                return cloneItemThread;
            case 'search':
                if (!selectedSearchPage) {
                    return item;
                }
                const cloneItemSearch = { ...item };
                cloneItemSearch['selected'] = cloneItemSearch['id'] === selectedSearchPage?.id;
                return cloneItemSearch;
            case 'search_results':
                if (!selectedSearchResultsPage) {
                    return item;
                }
                const cloneItemSearchResults = { ...item };
                cloneItemSearchResults['selected'] = cloneItemSearchResults['id'] === selectedSearchResultsPage?.id;
                return cloneItemSearchResults;
            default:
                return item;
        }
    }
    function buildManagePagesProps(pageData: Pagination<Page>) {
        const defaultProps: ManagePagesProps = {
            pages: pageData,
            paginationProps: {
                pagination_type: 'state',
                onPageChange: (page: number) => {
                    fetchData({
                        query: { page },
                    });
                },
            },
        };
        defaultProps['refreshCallback'] = () => {
            fetchData({});
        };
        defaultProps['rowDataCallback'] = customizeIdsRowData;
        defaultProps['selection'] = {
            multiple: false,
            onSelected: (selected: Page[]) => {
                if (!Array.isArray(selected)) {
                    return;
                }
                if (selected.length === 0) {
                    return;
                }

                switch (selectedPageType) {
                    case 'feed':
                        setSelectedFeedPage(selected[0]);
                        break;
                    case 'categories':
                        setSelectedCategoriesPage(selected[0]);
                        break;
                    case 'category_thread':
                        setSelectedCategoryThreadPage(selected[0]);
                        break;
                    case 'thread':
                        setSelectedThreadPage(selected[0]);
                        break;
                    case 'search':
                        setSelectedSearchPage(selected[0]);
                        break;
                    case 'search_results':
                        setSelectedSearchResultsPage(selected[0]);
                        break;
                }
            },
        };
        return defaultProps;
    }

    useEffect(() => {
        const feedPage = data?.feed_page || null;
        const categoriesPage = data?.categories_page || null;
        const categoryThreadPage = data?.category_thread_page || null;
        const threadPage = data?.thread_page || null;
        const searchPage = data?.search_page || null;
        const searchResultsPage = data?.search_results_page || null;
        setThreadPageToSave(threadPage);
        setSelectedThreadPage(threadPage);
        setFeedPageToSave(feedPage);
        setSelectedFeedPage(feedPage);
        setCategoriesPageToSave(categoriesPage);
        setSelectedCategoriesPage(categoriesPage);
        setCategoryThreadPageToSave(categoryThreadPage);
        setSelectedCategoryThreadPage(categoryThreadPage);
        setSearchPageToSave(searchPage);
        setSelectedSearchPage(searchPage);
        setSearchResultsPageToSave(searchResultsPage);
        setSelectedSearchResultsPage(searchResultsPage);
    }, [data]);

    return (
        <>
            <div className="row">
                <div className="col-12 col-md-6">
                    <div className="mb-3">
                        <p>Selected Feed Page: {feedPageToSave ? feedPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('feed');
                            setShowFeedPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Feed Page
                    </Button>
                    {feedPageToSave && <input type="hidden" name="feed_page" value={feedPageToSave.id} />}
                </div>
                <div className="col-12 col-md-6 mt-4 mt-md-0">
                    <div className="mb-3">
                        <p>Selected Categories Page: {categoriesPageToSave ? categoriesPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('categories');
                            setShowCategoriesPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Categories Page
                    </Button>
                    {categoriesPageToSave && <input type="hidden" name="categories_page" value={categoriesPageToSave.id} />}
                </div>
                <div className="col-12 col-md-6 mt-4">
                    <div className="mb-3">
                        <p>Selected Category Thread Page: {categoryThreadPageToSave ? categoryThreadPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('category_thread');
                            setShowCategoryThreadPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Category Thread Page
                    </Button>
                    {categoryThreadPageToSave && <input type="hidden" name="category_thread_page" value={categoryThreadPageToSave.id} />}
                </div>
                <div className="col-12 col-md-6 mt-4">
                    <div className="mb-3">
                        <p>Selected Thread Page: {threadPageToSave ? threadPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('thread');
                            setShowThreadPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Thread Page
                    </Button>
                    {threadPageToSave && <input type="hidden" name="thread_page" value={threadPageToSave.id} />}
                </div>
                <div className="col-12 col-md-6 mt-4">
                    <div className="mb-3">
                        <p>Selected Search Page: {searchPageToSave ? searchPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('search');
                            setShowSearchPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Search Page
                    </Button>
                    {searchPageToSave && <input type="hidden" name="search_page" value={searchPageToSave.id} />}
                </div>
                <div className="col-12 col-md-6 mt-4">
                    <div className="mb-3">
                        <p>Selected Search Results Page: {searchResultsPageToSave ? searchResultsPageToSave?.title : 'None'}</p>
                    </div>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedPageType('search_results');
                            setShowSearchResultsPageSelectModal(true);
                            fetchData({ query: {} });
                        }}
                    >
                        Select Search Results Page
                    </Button>
                    {searchResultsPageToSave && <input type="hidden" name="search_results_page" value={searchResultsPageToSave.id} />}
                </div>
            </div>

            <Modal show={showFeedPageSelectModal} onHide={() => setShowFeedPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Feed Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedFeedPage(null);
                            setShowFeedPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedFeedPage) {
                                setFeedPageToSave(selectedFeedPage);
                            }
                            setShowFeedPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>

            <Modal
                show={showCategoryThreadPageSelectModal}
                onHide={() => setShowCategoryThreadPageSelectModal(false)}
                size="lg"
                className="modal-full-form"
            >
                <Modal.Header closeButton>
                    <Modal.Title>Select Category Thread Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedCategoryThreadPage(null);
                            setShowCategoryThreadPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedCategoryThreadPage) {
                                setCategoryThreadPageToSave(selectedCategoryThreadPage);
                            }
                            setShowCategoryThreadPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>

            <Modal show={showCategoriesPageSelectModal} onHide={() => setShowCategoriesPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Categories Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedCategoriesPage(null);
                            setShowCategoriesPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedCategoriesPage) {
                                setCategoriesPageToSave(selectedCategoriesPage);
                            }
                            setShowCategoriesPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>
            <Modal show={showThreadPageSelectModal} onHide={() => setShowThreadPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Thread Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedThreadPage(null);
                            setShowThreadPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedThreadPage) {
                                setThreadPageToSave(selectedThreadPage);
                            }
                            setShowThreadPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>
            <Modal show={showSearchPageSelectModal} onHide={() => setShowSearchPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Search Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedSearchPage(null);
                            setShowSearchPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedSearchPage) {
                                setSearchPageToSave(selectedSearchPage);
                            }
                            setShowSearchPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>
            <Modal show={showSearchResultsPageSelectModal} onHide={() => setShowSearchResultsPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Search Results Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button
                        type="button"
                        onClick={() => {
                            setSelectedSearchResultsPage(null);
                            setShowSearchResultsPageSelectModal(false);
                        }}
                    >
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedSearchResultsPage) {
                                setSearchResultsPageToSave(selectedSearchResultsPage);
                            }
                            setShowSearchResultsPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>
        </>
    );
}

export default SharedPageSelectOptions;
