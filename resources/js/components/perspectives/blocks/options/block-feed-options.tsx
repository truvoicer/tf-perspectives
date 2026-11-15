import { usePage } from '@inertiajs/react';
import {
    BlockOptionsSharedProps,
    Button,
    LoadingSpinner,
    ManagePages,
    ManagePagesProps,
    NiceSelectDropdown,
    StylingOptions,
    StylingOptionsProps,
    TextInput,
    useAuthContext,
    useToastContext,
} from '@truvoicer/tru-fetcher-core-ui';
import { Page, Pagination } from '@truvoicer/tru-fetcher-core-ui/types';
import { useEffect, useState } from 'react';

import { AppMiddleware, Middleware } from '@truvoicer/tru-fetcher-core-ui/lib';
import { FetcherListPageBlock } from '@truvoicer/tru-fetcher-core-ui/types';
import { Modal } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { FeedOptionsBlock } from '../../../../types/page-block';

export interface BlockFeedOptionsOptionsProps extends BlockOptionsSharedProps {}
function BlockFeedOptions({ block, columnBlockDeleteFileRequest, columnBlockFileStoreRequest }: BlockFeedOptionsOptionsProps) {
    const [showPageSelectModal, setShowPageSelectModal] = useState(false);
    const [selectedPage, setSelectedPage] = useState<Page | null>(null);
    const [pageToSave, setPageToSave] = useState<Page | null>(null);
    const [pages, setPages] = useState<Pagination<Page> | null>(null);

    const data = block?.data as FeedOptionsBlock;
    const schema = block?.schema;
    const errors = block?.errors;

    const auth = useAuthContext();
    const page = usePage();

    const currentRows: Page[] = pages?.data ?? []; // change this to your paginated data

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
        if (!selectedPage) {
            return item;
        }
        const cloneItem = { ...item };
        cloneItem['selected'] = cloneItem['id'] === selectedPage?.id;
        return cloneItem;
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

                setSelectedPage(selected[0]);
            },
        };
        return defaultProps;
    }

    useEffect(() => {
        const threadPage = data?.thread_page || null;
        setPageToSave(threadPage);
        setSelectedPage(threadPage);
    }, [data]);

    return (
        <div className="row align-items-center">
            <div className="col-12">
                <div className="card">
                    <div className="card-header">Fetch Options</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Search Query"
                                    type="text"
                                    id="query"
                                    name="query"
                                    className="single-input mt-2"
                                    placeholder="Search Query"
                                    defaultValue={(data?.query as string | null) || (schema?.query as string | null) || ''}
                                    error={errors?.query as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Page Number"
                                    type="number"
                                    id="page_number"
                                    name="page_number"
                                    className="single-input mt-2"
                                    placeholder="Page Number"
                                    defaultValue={
                                        (data?.page_number as string | number | readonly string[] | undefined) ||
                                        (schema?.page_number as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.page_number as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Page Size"
                                    type="number"
                                    id="page_size"
                                    name="page_size"
                                    className="single-input mt-2"
                                    placeholder="Page Size"
                                    defaultValue={
                                        (data?.page_size as string | number | readonly string[] | undefined) ||
                                        (schema?.page_size as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.page_size as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Sort By"
                                    type="text"
                                    id="sort_by"
                                    name="sort_by"
                                    className="single-input mt-2"
                                    placeholder="Sort By"
                                    defaultValue={
                                        (data?.sort_by as string | number | readonly string[] | undefined) ||
                                        (schema?.sort_by as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.sort_by as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <label className="form-label mt-2">Sort Order</label>
                                <NiceSelectDropdown
                                    loadMoreOnScroll={false}
                                    placeholder="Sort Order (direction)"
                                    name="sort_order"
                                    options={[
                                        { value: 'asc', label: 'Ascending' },
                                        { value: 'desc', label: 'Descending' },
                                    ]}
                                    initialValue={(data?.sort_order as string) || (schema?.sort_order as string) || ''}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Search Fields"
                                    type="text"
                                    id="search_fields"
                                    name="search_fields"
                                    className="single-input mt-2"
                                    placeholder="Search Fields"
                                    defaultValue={
                                        Array.isArray(data?.search_fields) ? (data.search_fields as string[]).join(', ') : (data?.search_fields ?? '')
                                    }
                                    error={errors?.search_fields as string | undefined}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="card-header">Page</div>
                    <div className="card-body">
                        <div className="col-12">
                            <div className="mb-3">
                                <p>Selected Page: {pageToSave ? pageToSave?.title : 'None'}</p>
                            </div>
                            <Button
                                type="button"
                                onClick={() => {
                                    setShowPageSelectModal(true);
                                    fetchData({ query: {} });
                                }}
                            >
                                Select Page
                            </Button>
                            {pageToSave && (
                                <input type="hidden" name="thread_page" value={pageToSave.id} />
                            )}
                        </div>
                    </div>

                    <div className="card-header">Styling</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <StylingOptions
                                defaultValues={{
                                    background_color: '#ffffff',
                                    border_color: '#dee2e6',
                                    border_width: 1,
                                    border_style: 'solid',
                                    border_radius: '0px',
                                    box_shadow: 'none',
                                    padding: '15px',
                                    margin: '0px',
                                }}
                                enableThemeFields={true}
                                schema={schema}
                                data={data as StylingOptionsProps['data']}
                                errors={errors}
                                collapsible={true}
                                defaultExpanded={true}
                                title="Design & Styling"
                                showBackground={true}
                                showBorder={true}
                                showShadow={true}
                                showSpacing={true}
                            />
                        </div>
                    </div>
                </div>
            </div>
            <Modal show={showPageSelectModal} onHide={() => setShowPageSelectModal(false)} size="lg" className="modal-full-form">
                <Modal.Header closeButton>
                    <Modal.Title>Select Page</Modal.Title>
                </Modal.Header>
                <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                <Modal.Footer>
                    <Button type="button" onClick={() => {
                        setSelectedPage(null);
                        setShowPageSelectModal(false)
                    }}>
                        Close
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            if (selectedPage) {
                                setPageToSave(selectedPage);
                            }
                            setShowPageSelectModal(false);
                        }}
                    >
                        Save
                    </Button>
                </Modal.Footer>
            </Modal>
        </div>
    );
}

export default BlockFeedOptions;
